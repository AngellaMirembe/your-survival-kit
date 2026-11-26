<?php
/** Admin layout header. Every admin page must set $adminActive and require login. */
require_once dirname(__DIR__) . '/../includes/auth.php';
require_login();
$admin       = current_admin();
$adminActive = $adminActive ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($pageTitle ?? 'Dashboard') ?> — Admin | Your Survival Kit</title>
  <link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body">

<button class="btn btn-dark d-lg-none m-2" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebar">
  <i class="bi bi-list"></i> Menu
</button>

<aside class="admin-sidebar collapse d-lg-block" id="adminSidebar">
  <div class="brand">
    <img src="<?= e(asset('img/logo.png')) ?>" alt="Logo">
    <div><strong>Your Survival Kit</strong><small style="color:var(--ys-green)">Admin panel</small></div>
  </div>
  <ul class="nav flex-column">
    <?php
    $links = [
      ['',                 'bi-grid-1x2',   'Dashboard'],
      ['news.php',         'bi-newspaper',  'News & Stories'],
      ['events.php',       'bi-calendar-event', 'Events'],
      ['gallery.php',      'bi-images',     'Gallery'],
      ['donations.php',    'bi-cash-stack', 'Donation requests'],
      ['volunteers.php',   'bi-people',     'Volunteers'],
      ['messages.php',     'bi-envelope',   'Messages'],
      ['subscribers.php',  'bi-send',       'Newsletter'],
      ['settings.php',     'bi-gear',       'Settings'],
    ];
    foreach ($links as $l):
        $href = $l[0] ?: 'index.php';
        $active = ($adminActive === $l[0]) ? ' active' : ''; ?>
      <li class="nav-item">
        <a class="nav-link<?= $active ?>" href="<?= e(url('admin/' . $href)) ?>"><i class="bi <?= $l[1] ?>"></i> <?= e($l[2]) ?></a>
      </li>
    <?php endforeach; ?>
    <li class="nav-item mt-4"><hr style="border-color:rgba(255,255,255,.15)"></li>
    <li class="nav-item">
      <a class="nav-link" href="<?= e(url()) ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View website</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= e(url('admin/logout.php')) ?>"><i class="bi bi-box-arrow-left"></i> Sign out</a>
    </li>
  </ul>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <div>
      <span class="text-secondary small" style="font-family:var(--ys-font-head)">
        <i class="bi bi-house-door me-1"></i> Admin / <?= e($pageTitle ?? 'Dashboard') ?>
      </span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span class="small"><i class="bi bi-person-circle me-1"></i> <?= e($admin['name']) ?></span>
      <span class="badge bg-secondary"><?= e(ucfirst($admin['role'])) ?></span>
    </div>
  </header>

  <div class="admin-content">
    <?php flash_messages(); ?>
