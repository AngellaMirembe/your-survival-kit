<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mail.php';

/* ---------------- Verify email ---------------- */
if (isset($_GET['verify'])) {
    $token = (string) $_GET['verify'];
    $stmt = $pdo->prepare(
        'UPDATE subscribers SET verified_at = NOW(), verify_token = NULL
         WHERE verify_token = ? AND verified_at IS NULL'
    );
    $stmt->execute([$token]);
    if ($stmt->rowCount()) {
        flash('success', 'Email confirmed. You will now get updates about upcoming events.');
    } else {
        flash('info', 'That link has already been used or has expired.');
    }
    redirect('');
}

/* ---------------- Unsubscribe ---------------- */
if (isset($_GET['unsubscribe'])) {
    $token = (string) $_GET['unsubscribe'];
    $stmt = $pdo->prepare(
        'UPDATE subscribers SET unsubscribed_at = NOW() WHERE unsubscribe_token = ? AND unsubscribed_at IS NULL'
    );
    $stmt->execute([$token]);
    flash('info', 'You have been removed from the updates list.');
    redirect('');
}

/* ---------------- Subscribe ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'That email address does not look right.');
        redirect('');
    }

    $verifyToken = bin2hex(random_bytes(32));
    $unsubToken  = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare('SELECT id, verified_at FROM subscribers WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing && $existing['verified_at']) {
        flash('info', 'You are already subscribed — thank you!');
        redirect('');
    }

    try {
        if ($existing) {
            $pdo->prepare(
                'UPDATE subscribers SET verify_token = ?, unsubscribe_token = ?, unsubscribed_at = NULL WHERE id = ?'
            )->execute([$verifyToken, $unsubToken, $existing['id']]);
        } else {
            $pdo->prepare(
                'INSERT INTO subscribers (email, verify_token, unsubscribe_token) VALUES (?, ?, ?)'
            )->execute([$email, $verifyToken, $unsubToken]);
        }

        $link = url('newsletter.php?verify=' . rawurlencode($verifyToken));
        $html = '<h2 style="color:#a96538">Confirm your email</h2>'
              . '<p>Thanks for following ' . e(setting('site_name', SITE_NAME)) . '. '
              . 'Confirm your address so we can send you upcoming event updates.</p>'
              . '<p><a href="' . e($link) . '" style="background:#a96538;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block">Confirm my email</a></p>'
              . '<p style="font-size:.82rem;color:#888">If you did not ask for this, you can ignore this message.</p>';

        if (send_site_email($email, 'Confirm your email — ' . setting('site_name', SITE_NAME), $html)) {
            flash('success', mail_smtp_configured()
                ? 'Almost there! Check your inbox for a confirmation link.'
                : 'Thanks — your request was saved. You can confirm your email from the link in Admin → Newsletter.');
        } else {
            flash('error', 'Your details were saved, but the confirmation email could not be sent. '
                . (mail_smtp_configured()
                    ? 'Please check the SMTP settings in Admin → Settings.'
                    : 'Add a Gmail App Password in Admin → Settings → Email delivery (SMTP).'));
        }
    } catch (PDOException $e) {
        flash('error', 'We could not subscribe you right now. Please try again.');
    }
}

redirect('');
