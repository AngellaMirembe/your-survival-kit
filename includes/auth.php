<?php
require_once __DIR__ . '/functions.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function current_admin(): array
{
    static $admin = null;
    if ($admin !== null) {
        return $admin;
    }
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, name, email, role FROM admin_users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id'] ?? 0]);
    $admin = $stmt->fetch() ?: ['id' => 0, 'name' => 'Administrator', 'email' => '', 'role' => 'admin'];
    return $admin;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please sign in to continue.');
        redirect('admin/login.php');
    }
}

/** Simple brute-force protection: 5 attempts = 15 minute lockout. */
function login_locked(): bool
{
    $until = $_SESSION['login_lock_until'] ?? 0;
    if ($until > time()) {
        return true;
    }
    if ($until && $until <= time()) {
        unset($_SESSION['login_lock_until'], $_SESSION['login_attempts']);
    }
    return false;
}

function failed_login(): void
{
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_lock_until'] = time() + 15 * 60;
    }
}

function reset_login_attempts(): void
{
    unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
}
