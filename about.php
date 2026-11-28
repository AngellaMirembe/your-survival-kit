<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'About Us';
$pageDescription = 'Meet the small, youth-led team in Uganda behind Your Survival Kit and the outreaches they run.';
$activePage      = 'about';
$ogImage         = asset('img/outreach/outreach-11.jpg');

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

<!-- Team section — text and photos editable in Admin → Page content -->
<section class="section section-alt">
  <div class="ys-container">
    <div class="text-center mb-5">
      <span class="eyebrow"><?= e(setting('about_team_eyebrow', 'Meet the team')) ?></span>
      <h2 class="section-title"><?= e(setting('about_team_title', 'Young people, working together')) ?></h2>
      <p class="lead-lg mx-auto" style="max-width:720px"><?= e(setting('about_team_text', 'A small, youth-led group in Uganda.')) ?></p>
    </div>
    <div class="row g-4">
      <?php
      $teamPhotos = setting_json('about_team_images', ['outreach-12.jpg','outreach-13.jpg','outreach-14.jpg','outreach-15.jpg']);
      if (!$teamPhotos) $teamPhotos = ['outreach-12.jpg'];
      foreach (array_slice($teamPhotos, 0, 6) as $photo): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card-ys h-100 p-0 text-center overflow-hidden">
            <img src="<?= e(image_src($photo)) ?>" alt="Your Survival Kit outreach team" class="w-100" style="height:190px;object-fit:cover" loading="lazy">
            <div class="p-3">
              <h6 class="mb-1 small">Outreach team</h6>
              <small class="text-secondary">Collecting, packing and delivering kits</small>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="small text-secondary text-center mt-4 mb-0"><i class="bi bi-info-circle me-1"></i> Photos are from our outreaches. Names can be added here as members confirm them.</p>
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
        <p class="text-secondary"><?= e(setting('about_story_paragraph', 'The work is done by a small core team and rotating volunteers who organise collection drives, sort donations in borrowed spaces and make deliveries in person.')) ?></p>
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

<section class="section-sm section-alt">
  <div class="ys-container">
    <div class="cta-band d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div><h3 class="mb-1">Want to meet the team?</h3><p class="mb-0">We would love to talk about your community and how we can work together.</p></div>
      <a href="<?= e(url('contact')) ?>" class="btn btn-light-ys">Get in touch</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
