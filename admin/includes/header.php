<?php
/** Admin layout header. Every admin page must set $adminActive and require login. */
require_once dirname(__DIR__) . '/../includes/auth.php';
require_login();
$admin       = current_admin();
$adminActive = $adminActive ?? '';

// Small pending counts in the menu, so staff can see what needs attention.
$badges = [];
$counts = [
  'donations.php'   => "SELECT COUNT(*) FROM donations WHERE status = 'pending'",
  'volunteers.php'  => "SELECT COUNT(*) FROM volunteers WHERE status = 'new'",
  'messages.php'    => "SELECT COUNT(*) FROM messages WHERE is_read = 0",
  'subscribers.php' => "SELECT COUNT(*) FROM subscribers WHERE verified_at IS NULL AND unsubscribed_at IS NULL",
];
foreach ($counts as $key => $sql) {
    try {
        $badges[$key] = (int) $pdo->query($sql)->fetchColumn();
    } catch (Throwable $e) {
        $badges[$key] = 0;
    }
}

$menu = [
  ['label' => 'Overview', 'items' => [
    ['key' => '',            'icon' => 'bi-grid-1x2',      'label' => 'Dashboard',         'title' => 'Dashboard overview'],
  ]],
  ['label' => 'Content', 'items' => [
    ['key' => 'news.php',    'icon' => 'bi-newspaper',     'label' => 'Stories',           'title' => 'Add, edit and delete news stories'],
    ['key' => 'events.php',  'icon' => 'bi-calendar-event','label' => 'Events',            'title' => 'Add, edit, delete and announce events'],
    ['key' => 'gallery.php', 'icon' => 'bi-images',        'label' => 'Gallery',           'title' => 'Add, edit and delete photos'],
  ]],
  ['label' => 'Community', 'items' => [
    ['key' => 'donations.php',   'icon' => 'bi-cash-stack', 'label' => 'Donation pledges', 'title' => 'Review, confirm and delete donation pledges'],
    ['key' => 'volunteers.php',  'icon' => 'bi-people',     'label' => 'Volunteers',       'title' => 'Review and update volunteer applications'],
    ['key' => 'messages.php',    'icon' => 'bi-envelope',   'label' => 'Messages',         'title' => 'Read, reply to and delete messages'],
    ['key' => 'subscribers.php', 'icon' => 'bi-send',       'label' => 'Newsletter',       'title' => 'Add, verify and remove subscribers'],
  ]],
  ['label' => 'System', 'items' => [
    ['key' => 'settings.php', 'icon' => 'bi-gear', 'label' => 'Settings', 'title' => 'Contact details, email delivery, stats and password'],
  ]],
];
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
  <link href="<?= e(asset('css/style.css') . '?v=20261126b') ?>" rel="stylesheet">
</head>
<body class="admin-body">

<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Admin menu">
  <div class="brand">
    <img src="<?= e(asset('img/logo.png')) ?>" alt="Logo">
    <div class="flex-grow-1">
      <strong>Your Survival Kit</strong>
      <small style="color:var(--ys-green)">Admin panel</small>
    </div>
    <button id="adminSidebarClose" class="btn btn-sm btn-outline-light d-lg-none" type="button" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
  </div>

  <nav class="admin-nav">
    <ul class="nav flex-column">
      <?php foreach ($menu as $group): ?>
        <li class="admin-nav-group"><?= e($group['label']) ?></li>
        <?php foreach ($group['items'] as $item): ?>
          <?php
          $href   = $item['key'] ?: 'index.php';
          $active = ($adminActive === $item['key']) ? ' active' : '';
          ?>
          <li class="nav-item">
            <a class="nav-link<?= $active ?>" href="<?= e(url('admin/' . $href)) ?>" title="<?= e($item['title']) ?>">
              <i class="bi <?= e($item['icon']) ?>"></i>
              <span><?= e($item['label']) ?></span>
              <?php if (!empty($badges[$item['key']])): ?>
                <span class="admin-nav-count" title="Needs attention"><?= (int) $badges[$item['key']] ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="admin-sidebar-foot">
    <a class="nav-link" href="<?= e(url()) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> View website</a>
    <a class="nav-link" href="<?= e(url('admin/logout.php')) ?>"><i class="bi bi-box-arrow-left"></i> Sign out</a>
  </div>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <div class="d-flex align-items-center min-w-0">
      <button id="adminMenuToggle" class="btn btn-dark btn-sm d-lg-none me-2" type="button"
              aria-controls="adminSidebar" aria-expanded="false" aria-label="Open admin menu">
        <i class="bi bi-list"></i>
      </button>
      <span class="text-secondary small admin-breadcrumb">
        <i class="bi bi-house-door me-1"></i> Admin / <span class="text-dark"><?= e($pageTitle ?? 'Dashboard') ?></span>
      </span>
    </div>

    <div class="d-flex align-items-center gap-2 gap-md-3">
      <div class="dropdown">
        <button class="btn btn-ys btn-sm dropdown-toggle" type="button" id="adminQuickAdd"
                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Quick add">
          <i class="bi bi-plus-lg me-md-1"></i><span class="d-none d-md-inline">Quick add</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminQuickAdd">
          <li><h6 class="dropdown-header">Create new</h6></li>
          <li><a class="dropdown-item" href="<?= e(url('admin/news.php?action=new')) ?>"><i class="bi bi-newspaper me-2"></i> New story</a></li>
          <li><a class="dropdown-item" href="<?= e(url('admin/events.php?action=new')) ?>"><i class="bi bi-calendar-event me-2"></i> New event</a></li>
          <li><a class="dropdown-item" href="<?= e(url('admin/gallery.php?action=new')) ?>"><i class="bi bi-image me-2"></i> Add photo</a></li>
          <li><a class="dropdown-item" href="<?= e(url('admin/subscribers.php')) ?>"><i class="bi bi-person-plus me-2"></i> Add subscriber</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= e(url('admin/settings.php#stats')) ?>"><i class="bi bi-bar-chart me-2"></i> Impact numbers</a></li>
        </ul>
      </div>

      <a class="admin-top-icon d-none d-sm-inline" href="<?= e(url()) ?>" target="_blank" rel="noopener" title="View website" aria-label="View website"><i class="bi bi-box-arrow-up-right"></i></a>
      <span class="small admin-user-name"><i class="bi bi-person-circle me-1"></i> <?= e($admin['name']) ?></span>
      <span class="badge bg-secondary admin-role"><?= e(ucfirst($admin['role'])) ?></span>
    </div>
  </header>

  <div class="admin-content">
    <?php flash_messages(); ?>
