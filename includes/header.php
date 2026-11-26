<?php
/** Shared public header. Set $pageTitle, $pageDescription, $activePage before including. */
$pageTitle       = $pageTitle       ?? setting('site_name', SITE_NAME);
$pageDescription = $pageDescription ?? setting('about_short', SITE_TAGLINE);
$activePage      = $activePage      ?? '';
$ogImage         = $ogImage         ?? asset('img/hero.jpg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | <?= e(setting('site_name', SITE_NAME)) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="theme-color" content="#a96538">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e(setting('site_name', SITE_NAME)) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:url" content="<?= e((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
  <link rel="canonical" href="<?= e((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
  <link rel="icon" type="image/png" href="<?= e(asset('img/logo.png')) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<div class="topbar">
  <div class="ys-container d-flex justify-content-between align-items-center flex-wrap">
    <span><i class="bi bi-lightning-charge-fill me-1"></i> Youth-led in Uganda. Community-powered.</span>
    <span class="d-none d-sm-inline">
      <?php if ($email = setting('site_email')): ?>
        <a class="me-3" href="mailto:<?= e($email) ?>"><i class="bi bi-envelope me-1"></i> <?= e($email) ?></a>
      <?php endif; ?>
      <?php if (setting('whatsapp_number')): ?>
        <a href="<?= e(whatsapp_link(null, 'Hello Your Survival Kit, I have a question.')) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i> WhatsApp us</a>
      <?php endif; ?>
    </span>
  </div>
</div>

<nav class="navbar navbar-expand-lg ys-navbar" aria-label="Main navigation">
  <div class="ys-container">
    <a class="navbar-brand" href="<?= e(url()) ?>">
      <img src="<?= e(asset('img/logo.png')) ?>" alt="Your Survival Kit logo" class="brand-logo">
      <span class="brand-text">Your Survival Kit<small>GIVE WHAT MATTERS</small></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="<?= e(url()) ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>" href="<?= e(url('about')) ?>">About</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'what-we-do' ? 'active' : '' ?>" href="<?= e(url('what-we-do')) ?>">What We Do</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'gallery' ? 'active' : '' ?>" href="<?= e(url('gallery')) ?>">Gallery</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'news' ? 'active' : '' ?>" href="<?= e(url('news')) ?>">News</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'events' ? 'active' : '' ?>" href="<?= e(url('events')) ?>">Events</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'volunteer' ? 'active' : '' ?>" href="<?= e(url('volunteer')) ?>">Get Involved</a></li>
        <li class="nav-item"><a class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>" href="<?= e(url('contact')) ?>">Contact</a></li>
        <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
          <a class="btn navbar-donate" href="<?= e(url('donate')) ?>"><i class="bi bi-heart-fill me-1"></i> Donate</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="ys-container" style="max-width: 1180px;">
  <?php flash_messages(); ?>
</div>

<main id="main-content">
