<?php
/**
 * Email helpers. Requires functions.php (and therefore config.php) first.
 *
 * Delivery order:
 *   1. SMTP (if Admin → Settings has SMTP host, user and password) — real delivery.
 *   2. Local log file (only when YS_MAIL_LOG=1) — offline testing.
 *   3. PHP mail() — hosting with a working mail server.
 */
if (!defined('MAIL_LOG_ONLY')) {
    define('MAIL_LOG_ONLY', getenv('YS_MAIL_LOG') === '1');
}

function mail_log_path(): string
{
    $dir = dirname(__DIR__) . '/storage';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir . '/mail.log';
}

function log_mail(string $to, string $subject, string $html): void
{
    $line = '[' . date('Y-m-d H:i:s') . "] TO: {$to} | SUBJECT: {$subject}\n"
          . strip_tags($html) . "\n" . str_repeat('-', 60) . "\n";
    @file_put_contents(mail_log_path(), $line, FILE_APPEND | LOCK_EX);
}

/** True when an SMTP account has been entered in Admin → Settings. */
function mail_smtp_configured(): bool
{
    return (string) setting('smtp_host', '') !== ''
        && (string) setting('smtp_user', '') !== ''
        && (string) setting('smtp_pass', '') !== '';
}

function mail_smtp_settings(): array
{
    $port = (int) setting('smtp_port', '587');
    if ($port < 1 || $port > 65535) $port = 587;
    $secure = strtolower(trim((string) setting('smtp_secure', 'tls')));
    if (!in_array($secure, ['tls', 'ssl', 'none'], true)) $secure = 'tls';

    return [
        'host'   => trim((string) setting('smtp_host', '')),
        'port'   => $port,
        'user'   => trim((string) setting('smtp_user', '')),
        'pass'   => (string) setting('smtp_pass', ''),
        'secure' => $secure,
    ];
}

/* ---------------- Minimal SMTP client (no external library) ---------------- */

function smtp_send_line($fp, string $line): void
{
    fwrite($fp, $line . "\r\n");
}

/** Read a full SMTP reply, including multiline 250-/334- responses. */
function smtp_read($fp): string
{
    $data = '';
    while (($line = fgets($fp, 8192)) !== false) {
        $data .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $data;
}

function smtp_code_is(string $response, string $codes): bool
{
    $code = substr(ltrim($response), 0, 3);
    return in_array($code, explode(',', $codes), true);
}

function mail_header_encode(string $text): string
{
    return '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], '', $text)) . '?=';
}

/** Send via SMTP with optional STARTTLS / SSL and LOGIN or PLAIN auth. */
function smtp_send_email(string $to, string $subject, string $html, string $from, string $fromName): bool
{
    $cfg = mail_smtp_settings();
    if (!$cfg['host'] || !$cfg['user'] || !$cfg['pass']) {
        return false;
    }

    $errno = 0;
    $errstr = '';
    $remote = ($cfg['secure'] === 'ssl' ? 'ssl://' : 'tcp://') . $cfg['host'] . ':' . $cfg['port'];
    $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$fp) {
        log_mail($to, $subject, $html . '<p>SMTP connection failed: ' . e($errstr) . '</p>');
        return false;
    }
    stream_set_timeout($fp, 20);

    $fail = function (string $why) use ($fp, $to, $subject, $html): bool {
        @fclose($fp);
        log_mail($to, $subject, $html . '<p>SMTP error: ' . e($why) . '</p>');
        return false;
    };

    if (!smtp_code_is(smtp_read($fp), '220')) return $fail('Server did not send 220 greeting.');

    $hello = function () use ($fp, $cfg): string {
        smtp_send_line($fp, 'EHLO ' . (gethostname() ?: 'localhost'));
        return smtp_read($fp);
    };

    $ehlo = $hello();
    if (!smtp_code_is($ehlo, '250')) return $fail('EHLO was rejected.');

    if ($cfg['secure'] === 'tls') {
        smtp_send_line($fp, 'STARTTLS');
        if (!smtp_code_is(smtp_read($fp), '220')) return $fail('STARTTLS is unavailable.');
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return $fail('Could not start TLS encryption.');
        }
        $ehlo = $hello();
        if (!smtp_code_is($ehlo, '250')) return $fail('EHLO after TLS was rejected.');
    }

    // Authenticate (LOGIN where supported, PLAIN as a fallback)
    smtp_send_line($fp, 'AUTH LOGIN');
    $auth = smtp_read($fp);
    if (smtp_code_is($auth, '235')) {
        // Already authenticated — nothing further to send.
    } elseif (smtp_code_is($auth, '334')) {
        smtp_send_line($fp, base64_encode($cfg['user']));
        if (!smtp_code_is(smtp_read($fp), '334')) return $fail('SMTP username rejected.');
        smtp_send_line($fp, base64_encode($cfg['pass']));
        if (!smtp_code_is(smtp_read($fp), '235')) return $fail('SMTP password rejected.');
    } else {
        smtp_send_line($fp, 'AUTH PLAIN ' . base64_encode("\0" . $cfg['user'] . "\0" . $cfg['pass']));
        if (!smtp_code_is(smtp_read($fp), '235')) return $fail('SMTP authentication failed.');
    }

    smtp_send_line($fp, 'MAIL FROM:<' . $from . '>');
    if (!smtp_code_is(smtp_read($fp), '250')) return $fail('Sender was rejected.');

    smtp_send_line($fp, 'RCPT TO:<' . $to . '>');
    if (!smtp_code_is(smtp_read($fp), '250,251')) return $fail('Recipient was rejected.');

    smtp_send_line($fp, 'DATA');
    if (!smtp_code_is(smtp_read($fp), '354')) return $fail('Server refused the message data.');

    $domain = preg_replace('/[^a-z0-9.-]/i', '', $cfg['host']) ?: 'localhost';
    $headers = [
        'From: ' . mail_header_encode($fromName) . ' <' . $from . '>',
        'To: <' . $to . '>',
        'Subject: ' . mail_header_encode($subject),
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(8)) . '@' . $domain . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n"
             . chunk_split(base64_encode($html), 76, "\r\n") . "\r\n.";
    smtp_send_line($fp, $message);
    $sent = smtp_code_is(smtp_read($fp), '250');
    smtp_send_line($fp, 'QUIT');
    @fclose($fp);

    if (!$sent) {
        log_mail($to, $subject, $html . '<p>SMTP server did not accept the message.</p>');
    }
    return $sent;
}

/** Send an HTML email. Returns true only when delivery (or logging) succeeded. */
function send_site_email(string $to, string $subject, string $html): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $smtpUser = (string) setting('smtp_user', '');
    $from = filter_var(setting('site_email', ''), FILTER_VALIDATE_EMAIL)
        ? setting('site_email')
        : (filter_var($smtpUser, FILTER_VALIDATE_EMAIL) ? $smtpUser : 'no-reply@localhost');
    $name = str_replace(["\r", "\n"], '', setting('site_name', SITE_NAME));
    $subject = str_replace(["\r", "\n"], '', $subject);

    $body = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px">'
          . '<p style="font-size:1.2rem;color:#a96538;font-weight:bold">' . e($name) . '</p>'
          . $html
          . '<hr style="border:0;border-top:1px solid #eee;margin-top:28px">'
          . '<p style="font-size:.78rem;color:#888">You are receiving this because you subscribed to updates. '
          . 'You can stop at any time.</p></div>';

    if (mail_smtp_configured()) {
        if (smtp_send_email($to, $subject, $body, $from, $name)) {
            return true;
        }
        log_mail($to, $subject, $body);
        return false;
    }

    if (MAIL_LOG_ONLY) {
        log_mail($to, $subject, $body);
        return true;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $name . ' <' . $from . '>',
        'Reply-To: ' . $from,
    ];
    $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
    if (!$ok) {
        log_mail($to, $subject, $body);
        return false;
    }
    return true;
}

/** Send one event announcement to every verified, subscribed email. */
function notify_subscribers_about_event(array $event): int
{
    global $pdo;

    $subscribers = $pdo->query(
        "SELECT email, unsubscribe_token FROM subscribers
         WHERE verified_at IS NOT NULL AND unsubscribed_at IS NULL"
    )->fetchAll();

    if (!$subscribers) return 0;

    $title    = $event['title'];
    $date     = date('D, M j, Y', strtotime($event['event_date']));
    $time     = trim((string) ($event['event_time'] ?? ''));
    $where    = trim((string) ($event['location'] ?? '')) ?: 'Location shared with confirmed volunteers';
    $intro    = trim(strip_tags((string) ($event['description'] ?? '')));
    if (mb_strlen($intro) > 240) $intro = mb_substr($intro, 0, 240) . '…';

    $eventUrl = url('events');
    $wa       = whatsapp_link(null, 'Hi! I saw the event "' . $title . '" on ' . $date . '. I would like to join.');

    $html = '<h2 style="color:#a96538">Upcoming event: ' . e($title) . '</h2>'
          . '<p><strong>When:</strong> ' . e($date) . ($time ? ' at ' . e($time) : '') . '<br>'
          . '<strong>Where:</strong> ' . e($where) . '</p>'
          . ($intro ? '<p>' . e($intro) . '</p>' : '')
          . '<p><a href="' . e($wa) . '" style="background:#25D366;color:#fff;padding:11px 18px;border-radius:8px;text-decoration:none;display:inline-block">Confirm on WhatsApp</a></p>'
          . '<p><a href="' . e($eventUrl) . '" style="color:#a96538">See all upcoming events</a></p>';

    $sent = 0;
    foreach ($subscribers as $s) {
        $unsub = url('newsletter.php?unsubscribe=' . rawurlencode($s['unsubscribe_token']));
        $withUnsub = $html . '<p style="font-size:.78rem;color:#888">'
            . '<a href="' . e($unsub) . '">Stop receiving these emails</a></p>';
        if (send_site_email($s['email'], 'Upcoming event: ' . $title, $withUnsub)) {
            $sent++;
        }
    }
    return $sent;
}
