<?php
require_once dirname(__DIR__) . '/includes/auth.php';

// The bottom-left staff icon requests a fresh sign-in even when a session exists.
$reauth = isset($_GET['reauth']);
if (is_logged_in() && !$reauth) {
    redirect('admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (login_locked()) {
        $error = 'Too many failed attempts. Please wait 15 minutes and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $stmt  = $pdo->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            reset_login_attempts();
            flash('success', 'Welcome back, ' . $user['name'] . '.');
            redirect('admin/index.php');
        }
        failed_login();
        $error = 'Incorrect email or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Staff Sign In — Your Survival Kit</title>
  <link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= e(asset('css/style.css') . '?v=20261126b') ?>" rel="stylesheet">
</head>
<body class="admin-body">
<div class="login-wrap">
  <div class="login-card">
    <img src="<?= e(asset('img/logo.png')) ?>" alt="Your Survival Kit logo">
    <h3 class="text-center mb-1">Staff Sign In</h3>
    <p class="text-center text-secondary small mb-4">Manage stories, photos and donations.</p>

    <?php flash_messages(); ?>

    <?php if ($error): ?>
      <div class="alert alert-danger small"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($reauth && is_logged_in()): ?>
      <div class="alert alert-info small">
        You are signed in as <strong><?= e(current_admin()['name']) ?></strong>. Enter your password to continue.
        <a class="d-block mt-1" href="<?= e(url('admin/logout.php')) ?>">Not you? Sign out</a>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('admin/login.php') . ($reauth ? '?reauth=1' : '')) ?>">
      <?php csrf_field(); ?>
      <div class="mb-3">
        <label class="form-label" for="email">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-ys w-100" type="submit"><i class="bi bi-box-arrow-in-right me-2"></i> Sign in</button>
    </form>

    <div class="text-center mt-4">
      <a href="<?= e(url()) ?>" class="small"><i class="bi bi-arrow-left me-1"></i> Back to website</a>
    </div>
    <p class="text-center text-secondary small mt-4 mb-0" style="font-size:.72rem">
      Staff access only. If you cannot sign in, ask the team lead to reset your password.
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
