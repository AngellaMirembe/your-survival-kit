<?php
require_once __DIR__ . '/config.php';

/** Escape output — use for every dynamic value printed into HTML. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirect to a site-relative path or a full URL. */
function redirect(string $path): void
{
    $target = preg_match('#^https?://#i', $path) ? $path : url($path);
    header('Location: ' . $target);
    exit;
}

/** Return the current request path relative to the app (for active nav states). */
function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = BASE_PATH;
    if ($base !== '' && strpos($path, $base) === 0) {
        $path = substr($path, strlen($base));
    }
    return trim(str_replace('.php', '', $path), '/');
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/* ---------------- CSRF ---------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Security check failed. Please reload the page and try again.');
    }
}

/* ---------------- Flash messages ---------------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_messages(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $messages = $_SESSION['flash'];
    unset($_SESSION['flash']);
    foreach ($messages as $m) {
        $color = match ($m['type']) {
            'success' => 'success',
            'error'   => 'danger',
            'warning' => 'warning',
            default   => 'info',
        };
        echo '<div class="alert alert-' . $color . ' alert-dismissible fade show" role="alert">'
            . e($m['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

/** Re-populate a form field after a validation error. */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function set_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

/* ---------------- Uploads ---------------- */
/** Validate and move an uploaded image. Returns stored filename or false. */
function upload_image(array $file, string $prefix = 'img'): string|false
{
    if (!isset($file['error']) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if (!is_uploaded_file($file['tmp_name']) || $file['size'] > MAX_UPLOAD) {
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $map   = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($map[$mime])) {
        return false;
    }

    // Verify it is really an image
    if (@getimagesize($file['tmp_name']) === false) {
        return false;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(5)) . '.' . $map[$mime];
    return move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename) ? $filename : false;
}

function delete_image(?string $filename): void
{
    if (!$filename) {
        return;
    }
    $path = UPLOAD_DIR . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

function image_src(?string $filename, string $fallback = 'outreach/outreach-09.jpg'): string
{
    if ($filename) {
        $safe = basename($filename);
        // Preferred: an image in uploads/.
        if (is_file(UPLOAD_DIR . $safe)) {
            return UPLOAD_URL . rawurlencode($safe);
        }
        // Hosted servers sometimes miss uploads/; the outreach copy is also deployed.
        if (is_file(dirname(__DIR__) . '/assets/img/outreach/' . $safe)) {
            return asset('img/outreach/' . rawurlencode($safe));
        }
    }
    return asset('img/' . $fallback);
}

/** Where an image is actually stored: upload, outreach fallback, missing or blank. */
function image_location_status(?string $filename): string
{
    if (!$filename) return 'blank';
    $safe = basename($filename);
    if (is_file(UPLOAD_DIR . $safe)) return 'upload';
    if (is_file(dirname(__DIR__) . '/assets/img/outreach/' . $safe)) return 'outreach';
    return 'missing';
}

/** Decode a JSON setting with a safe default. */
function setting_json(string $key, array $default): array
{
    $raw = setting($key, '');
    if ($raw === '') return $default;
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $default;
}

/* ---------------- Uganda phone / WhatsApp helpers ---------------- */
/** Normalise a Ugandan number to +256XXXXXXXXX, or return false. */
function ug_phone($phone): string|false
{
    $p = preg_replace('/[^0-9+]/', '', trim((string) $phone));
    if (preg_match('/^\+256\d{9}$/', $p)) return $p;
    if (preg_match('/^256\d{9}$/', $p)) return '+' . $p;
    if (preg_match('/^0\d{9}$/', $p)) return '+256' . substr($p, 1);
    return false;
}

/** Build a wa.me link. Uses the site WhatsApp setting unless a phone is passed. */
function whatsapp_link(?string $phone = null, string $message = ''): string
{
    $number = $phone ? ug_phone($phone) : ug_phone(setting('whatsapp_number'));
    if (!$number) return '#';
    $url = 'https://wa.me/' . ltrim($number, '+');
    if ($message !== '') $url .= '?text=' . rawurlencode($message);
    return $url;
}

/* ---------------- Text helpers ---------------- */
function excerpt(string $text, int $length = 160): string
{
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}

function time_ago(string $datetime): string
{
    $seconds = max(0, time() - strtotime($datetime));
    return match (true) {
        $seconds < 60      => 'just now',
        $seconds < 3600    => floor($seconds / 60) . ' min ago',
        $seconds < 86400   => floor($seconds / 3600) . ' hr ago',
        $seconds < 604800  => floor($seconds / 86400) . ' days ago',
        default            => date('M j, Y', strtotime($datetime)),
    };
}

/** Very small, dependency-free pagination markup. */
function pagination(int $total, int $perPage, int $current, string $baseUrl): void
{
    $pages = ceil($total / $perPage);
    if ($pages <= 1) {
        return;
    }
    echo '<nav aria-label="Pagination"><ul class="pagination justify-content-center mt-4">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i === $current ? ' active' : '';
        echo '<li class="page-item' . $active . '">'
            . '<a class="page-link" href="' . e($baseUrl) . '?page=' . $i . '">' . $i . '</a></li>';
    }
    echo '</ul></nav>';
}
