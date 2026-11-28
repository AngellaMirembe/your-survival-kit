<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'What We Do';
$pageDescription = 'Explore how this small youth-led group in Uganda gathers, packs and delivers school supplies, care items, food and mentorship.';
$activePage      = 'what-we-do';
include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">What We Do</li>
    </ol></nav>
    <span class="eyebrow">Our programmes</span>
    <h1>Practical help, delivered with dignity.</h1>
    <p>We do a few things well. Each programme is built around something a child can actually use, wear, eat or learn from.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="text-center mb-5">
      <span class="eyebrow">The model</span>
      <h2 class="section-title">Gather. Pack. Deliver.</h2>
      <p class="lead-lg mx-auto"><?= e(setting('what_intro_text', 'Every kit is built around what a child actually needs for school and care. We keep the list short, useful and honest.')) ?></p>
    </div>
    <div class="row g-4">
      <?php
      $steps = [
        ['bi-box-seam', '1. Gather', 'Families and young people contribute items they no longer need; others give money and we buy carefully. We share a short wish list before each drive so giving is specific.'],
        ['bi-clipboard-check', '2. Pack', 'Volunteers check quality, group items by age and need, and add a handwritten note. Each kit is written down before it leaves the table.'],
        ['bi-person-check', '3. Deliver', 'We hand kits to schools and families ourselves, confirm receipt and tell donors where their gift went. No anonymous drop-offs, no assumptions.'],
      ];
      foreach ($steps as $i => $s): ?>
        <div class="col-md-4">
          <div class="card-ys h-100">
            <div class="icon-box <?= $i === 1 ? 'green' : '' ?>"><i class="bi <?= $s[0] ?>"></i></div>
            <h4><?= e($s[1]) ?></h4>
            <p><?= e($s[2]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="ys-container">
    <?php
    $programs = [
      ['bi-bag-heart', 'School Survival Kits', 'outreach/outreach-16.jpg',
       'A sturdy bag with notebooks, pencils, an exercise book, a ruler and a reusable bottle. Built for a child starting or continuing school with confidence.',
       ['Backpack or sturdy bag', 'Notebooks & pencils', 'Eraser, ruler, sharpener', 'Reusable water bottle']],
      ['bi-person-bounding-box', 'Clothing & Care', 'outreach/outreach-17.jpg',
       'Clothing and basic hygiene items passed on in good condition. We check sizes and usefulness before anything is packed.',
       ['Clean clothes in good condition', 'Socks and underwear', 'Soap and a towel', 'A blanket or shawl where needed']],
      ['bi-cup-hot', 'Food & Nutrition', 'outreach/outreach-18.jpg',
       'Simple, shelf-stable nourishment for children who arrive at school hungry or whose family has had a difficult month.',
       ['Non-perishable snacks', 'Reusable bottle', 'Rice or maize portions', 'Fortified drink sachets']],
      ['bi-people', 'Mentorship & Play', 'outreach/outreach-19.jpg',
       'Our volunteers visit schools, read with children, play games and remind them that someone is paying attention.',
       ['Reading together', 'Games and sports', 'Homework support', 'Handwritten encouragement notes']],
    ];
    foreach ($programs as $i => $p): ?>
      <div class="row align-items-center gy-4 mb-5 <?= $i % 2 ? 'flex-row-reverse' : '' ?>">
        <div class="col-lg-5">
          <div class="feature-img"><img src="<?= e(asset('img/' . $p[2])) ?>" alt="<?= e($p[1]) ?>"></div>
        </div>
        <div class="col-lg-7 <?= $i % 2 ? 'pe-lg-5' : 'ps-lg-5' ?>">
          <div class="icon-box"><i class="bi <?= $p[0] ?>"></i></div>
          <h3><?= e($p[1]) ?></h3>
          <p class="text-secondary"><?= e($p[3]) ?></p>
          <ul class="list-unstyled mt-3 mb-4">
            <?php foreach ($p[4] as $item): ?>
              <li class="mb-2"><i class="bi bi-check-circle-fill text-green me-2"></i><?= e($item) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="<?= e(url('donate')) ?>" class="btn btn-outline-ys btn-sm">Support this programme</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="row gy-4">
      <div class="col-lg-7">
        <span class="eyebrow">Fair and careful</span>
        <h2 class="section-title">How children are selected</h2>
        <p class="text-secondary">We work with teachers and community leaders who already know the families. Requests are reviewed by at least two team members, and we prioritise children facing the greatest daily barriers to learning and safety.</p>
        <p class="text-secondary mb-0">We never publish names, photographs or identifying details without written permission from a guardian. Dignity is not an extra step — it is the starting point.</p>
      </div>
      <div class="col-lg-5">
        <div class="card-ys">
          <h4 class="mb-3"><i class="bi bi-journal-check me-2 text-brown"></i> What we do not do</h4>
          <ul class="list-unstyled mb-0">
            <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> No photo opportunities without consent</li>
            <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> No leaking personal family information</li>
            <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> No unverified items or expired consumables</li>
            <li class="mb-0"><i class="bi bi-x-circle text-danger me-2"></i> No promises we cannot keep</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-sm section-alt">
  <div class="ys-container">
    <div class="cta-band d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div><h3 class="mb-1">Ready to make a kit?</h3><p class="mb-0">Give a gift or bring your time. Both are equally welcome.</p></div>
      <div>
        <a href="<?= e(url('donate')) ?>" class="btn btn-light-ys">Donate</a>
        <a href="<?= e(url('volunteer') . '#apply') ?>" class="btn btn-outline-light">Volunteer</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
