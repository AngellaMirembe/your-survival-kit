<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Upcoming Events';
$pageDescription = 'See upcoming Your Survival Kit outreaches, collection drives and community events in Uganda.';
$activePage      = 'events';
$ogImage         = asset('img/outreach/outreach-22.jpg');

$today = date('Y-m-d');
$upcoming = $pdo->prepare(
    "SELECT * FROM events WHERE status = 'published' AND event_date >= ? ORDER BY event_date ASC, event_time ASC LIMIT 12"
);
$upcoming->execute([$today]);
$upcoming = $upcoming->fetchAll();

$past = $pdo->prepare(
    "SELECT * FROM events WHERE status = 'published' AND event_date < ? ORDER BY event_date DESC LIMIT 3"
);
$past->execute([$today]);
$past = $past->fetchAll();

$waNumber = setting('whatsapp_number', '+256 765 976851');

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Events</li>
    </ol></nav>
    <span class="eyebrow">Come and join us</span>
    <h1>Upcoming events</h1>
    <p>Collection drives, packing days and outreaches. Confirm your place on WhatsApp and we will share the exact meeting point.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">

    <?php if ($upcoming): ?>
      <div class="row g-4">
        <?php foreach ($upcoming as $ev): ?>
          <?php
          $where = trim((string) $ev['location']) ?: 'Location shared with confirmed volunteers';
          $text  = 'Hello Your Survival Kit. I would like to join "' . $ev['title'] . '" on '
                 . date('D, M j, Y', strtotime($ev['event_date'])) . '.';
          ?>
          <div class="col-md-6 col-lg-4">
            <article class="card-ys event-card h-100">
              <div class="event-date">
                <strong><?= e(date('d', strtotime($ev['event_date']))) ?></strong>
                <span><?= e(date('M', strtotime($ev['event_date']))) ?></span>
              </div>
              <?php if ($ev['image']): ?>
                <img src="<?= e(image_src($ev['image'])) ?>" alt="<?= e($ev['title']) ?>" class="event-img">
              <?php else: ?>
                <img src="<?= e(asset('img/outreach/outreach-22.jpg')) ?>" alt="Your Survival Kit outreach" class="event-img">
              <?php endif; ?>
              <div class="p-3">
                <h4 class="h5 mb-2"><?= e($ev['title']) ?></h4>
                <p class="small text-secondary mb-3"><?= e($ev['description'] ? excerpt($ev['description'], 220) : 'More details will be shared on WhatsApp.') ?></p>
                <ul class="list-unstyled small mb-3">
                  <li class="mb-1"><i class="bi bi-calendar3 text-brown me-2"></i><?= e(date('l, M j, Y', strtotime($ev['event_date']))) ?></li>
                  <?php if ($ev['event_time']): ?><li class="mb-1"><i class="bi bi-clock text-brown me-2"></i><?= e($ev['event_time']) ?></li><?php endif; ?>
                  <li><i class="bi bi-geo-alt text-brown me-2"></i><?= e($where) ?></li>
                </ul>
                <a class="btn btn-success btn-sm w-100" href="<?= e(whatsapp_link($waNumber, $text)) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i> Confirm on WhatsApp</a>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card-ys text-center py-5">
        <div class="icon-box mx-auto mb-3"><i class="bi bi-calendar-event"></i></div>
        <h3>No events listed yet</h3>
        <p class="text-secondary mx-auto" style="max-width:520px">We are planning the next outreach. Get in touch on WhatsApp and we will let you know as soon as a date is confirmed.</p>
        <a class="btn btn-ys mt-2" href="<?= e(whatsapp_link($waNumber, 'Hello! Are there any upcoming events I can join?')) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i> Ask on WhatsApp</a>
      </div>
    <?php endif; ?>

    <?php if ($past): ?>
      <div class="mt-5 pt-4 border-top">
        <h3 class="h4 mb-4">Recent events</h3>
        <div class="row g-3">
          <?php foreach ($past as $ev): ?>
            <div class="col-md-4">
              <div class="d-flex gap-3 align-items-start">
                <div class="event-date small"><strong><?= e(date('d', strtotime($ev['event_date']))) ?></strong><span><?= e(date('M', strtotime($ev['event_date']))) ?></span></div>
                <div><h6 class="mb-1 small"><?= e($ev['title']) ?></h6><small class="text-secondary"><?= e(excerpt($ev['description'] ?? '', 70)) ?></small></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section-sm section-alt">
  <div class="ys-container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-7">
        <span class="eyebrow">Stay in the loop</span>
        <h2 class="section-title h3">Get event updates by email</h2>
        <p class="text-secondary mb-0">Join the list and we will email you when a new event is added. You confirm your address first — no spam, no surprises.</p>
      </div>
      <div class="col-lg-5">
        <form method="post" action="<?= e(url('newsletter.php')) ?>" class="d-flex flex-wrap gap-2">
          <?php csrf_field(); ?>
          <input type="email" name="email" class="form-control flex-grow-1" style="min-width:180px" placeholder="Your email address" required aria-label="Email address">
          <button class="btn btn-ys" type="submit">Join</button>
        </form>
        <small class="text-secondary d-block mt-2">Channel: <?= e(setting('whatsapp_channel', 'YOUR SURVIVAL KIT')) ?> · <a href="<?= e(whatsapp_link($waNumber, 'Hello! Please add me to the Your Survival Kit WhatsApp channel.')) ?>" target="_blank" rel="noopener" class="text-brown">WhatsApp us</a></small>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
