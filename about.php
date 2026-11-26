<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'About Us';
$pageDescription = 'Meet the small, youth-led team in Uganda behind Your Survival Kit and the outreaches they run.';
$activePage      = 'about';
$ogImage         = asset('img/outreach/leader-shanis.jpg');

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">About Us</li>
    </ol></nav>
    <span class="eyebrow">Who we are</span>
    <h1>A small team doing what it can.</h1>
    <p>Your Survival Kit is a youth-led group in Uganda. We gather everyday essentials, pack them with care and hand them to children in communities we know.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-5">
        <div class="feature-img"><img src="<?= e(asset('img/outreach/outreach-11.jpg')) ?>" alt="Your Survival Kit outreach in Uganda"></div>
      </div>
      <div class="col-lg-7 ps-lg-5">
        <span class="eyebrow">Our story</span>
        <h2 class="section-title">It started with what we already had.</h2>
        <p class="lead-lg">A few young people noticed children around them going without basic school and care items. Instead of waiting for a big institution to step in, they collected what they could, packed it themselves and delivered it.</p>
        <p class="text-secondary">That is still how we work today. Your Survival Kit is not a large charity with warehouses and a head office. It is a small group of volunteers who organise collection drives, sort donations at whatever space we can borrow, and make deliveries in person.</p>
        <p class="text-secondary">The group is led by <strong>Akatucungura Shanis Pretty</strong>, a young debater who brings the same clarity to organising that she brings to a debate floor. Her approach is simple: listen first, act with what you have, and be honest about what you did.</p>
        <a href="<?= e(url('what-we-do')) ?>" class="btn btn-ys mt-3">See what we do</a>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="ys-container">
    <div class="text-center mb-5">
      <span class="eyebrow">What guides us</span>
      <h2 class="section-title">Our values</h2>
    </div>
    <div class="row g-4">
      <?php
      $values = [
        ['bi-people', 'Youth leadership', 'Young people make the decisions, do the packing and learn from every delivery.'],
        ['bi-shield-check', 'Dignity first', 'We give respectfully and privately, never making a child feel like a case.'],
        ['bi-journal-check', 'Simple honesty', 'We write down what was collected and where it went. No glossy reports, just accurate ones.'],
        ['bi-arrow-repeat', 'Consistency', 'Showing up again matters more than one dramatic gesture.'],
      ];
      foreach ($values as $v): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card-ys text-center">
            <div class="icon-box mx-auto"><i class="bi <?= $v[0] ?>"></i></div>
            <h4><?= e($v[1]) ?></h4>
            <p><?= e($v[2]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="row gy-5 align-items-center">
      <div class="col-lg-6">
        <span class="eyebrow">Our leader</span>
        <h2 class="section-title">Akatucungura Shanis Pretty</h2>
        <p class="lead-lg">A young debater, organiser and the voice that started this group.</p>
        <p class="text-secondary">Shanis leads the team with a debater's habit of asking the right question before acting: what does this child actually need, who can we reach today, and how will we know we helped? That thinking shapes every outreach we run.</p>
        <p class="text-secondary">She works alongside a small core team and volunteers who help with collection, packing and delivery. Roles rotate depending on what needs doing — the title matters less than showing up.</p>
        <div class="alert alert-light border mt-4 mb-0 small">
          <i class="bi bi-info-circle text-brown me-1"></i>
          Want to interview the team or verify our work? Use the <a href="<?= e(url('contact')) ?>" class="text-brown fw-bold">contact page</a> and we will put you in touch.
        </div>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-12">
            <div class="card-ys d-flex align-items-center gap-3 p-3">
              <img src="<?= e(asset('img/outreach/leader-shanis.jpg')) ?>" alt="Akatucungura Shanis Pretty" class="rounded-circle" width="86" height="86" style="object-fit:cover;object-position:top">
              <div>
                <h5 class="mb-1">Akatucungura Shanis Pretty</h5>
                <small class="text-secondary d-block">Founder &amp; leader • young debater</small>
                <small class="text-secondary">Coordinates outreaches and speaks for the team.</small>
              </div>
            </div>
          </div>
          <?php
          $team = [
            ['Core volunteer', 'Helps collect and sort donated items', 'outreach-12.jpg'],
            ['Core volunteer', 'Packs kits and writes the notes inside', 'outreach-13.jpg'],
            ['Core volunteer', 'Liaises with families and schools', 'outreach-14.jpg'],
            ['Core volunteer', 'Documents outreaches and updates donors', 'outreach-15.jpg'],
          ];
          foreach ($team as $t): ?>
            <div class="col-sm-6">
              <div class="card-ys d-flex align-items-center gap-3 p-3">
                <img src="<?= e(asset('img/outreach/' . $t[2])) ?>" alt="Your Survival Kit volunteer at an outreach" class="rounded-circle" width="62" height="62" style="object-fit:cover">
                <div><h6 class="mb-0 small"><?= e($t[0]) ?></h6><small class="text-secondary"><?= e($t[1]) ?></small></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="small text-secondary mt-3 mb-0"><i class="bi bi-info-circle me-1"></i> Team photos are from our outreaches. Names will be added as members confirm them.</p>
      </div>
    </div>
  </div>
</section>

<section class="section-sm section-alt">
  <div class="ys-container">
    <div class="cta-band d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div><h3 class="mb-1">Want to meet the team?</h3><p class="mb-0">We would love to talk about your community and how we can work together.</p></div>
      <a href="<?= e(url('contact')) ?>" class="btn btn-light-ys">Get in touch</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
