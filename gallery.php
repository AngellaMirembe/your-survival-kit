<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Gallery';
$pageDescription = 'Photos from Your Survival Kit outreaches in Uganda — collection drives, packing days and deliveries.';
$activePage      = 'gallery';
$ogImage         = asset('img/outreach/outreach-01.jpg');

$images = $pdo->query('SELECT * FROM gallery WHERE consent_confirmed = 1 ORDER BY sort_order, id DESC')->fetchAll();
$categories = [];
foreach ($images as $img) {
    $categories[$img['category']] = true;
}
include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Gallery</li>
    </ol></nav>
    <span class="eyebrow">Moments that matter</span>
    <h1>From the outreaches</h1>
    <p>Real moments from our collection drives, packing tables and deliveries in Uganda. Captions are being written by the team — if you can identify a photo, tell us.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">

    <p class="small text-secondary mb-4"><i class="bi bi-shield-check me-1"></i> We ask permission before sharing photos of children. If an image here should be removed, please <a href="<?= e(url('contact')) ?>" class="text-brown fw-bold">let us know</a>.</p>

    <?php if ($images): ?>
      <div class="filter-tabs mb-4">
        <button class="btn btn-sm btn-outline-ys active" data-filter="all">All photos</button>
        <?php foreach (array_keys($categories) as $cat): ?>
          <button class="btn btn-sm btn-outline-ys" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
        <?php endforeach; ?>
      </div>

      <div class="row">
        <?php foreach ($images as $img): ?>
          <div class="col-6 col-lg-4">
            <div class="gallery-item" data-title="<?= e($img['title']) ?>" data-category="<?= e($img['category']) ?>" data-full="<?= e(image_src($img['image'])) ?>">
              <img src="<?= e(image_src($img['image'])) ?>" alt="<?= e($img['title']) ?>" loading="lazy">
              <div class="gallery-caption">
                <h6><?= e($img['title']) ?></h6>
                <small><?= e($img['category']) ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <!-- Fallback shown only if the gallery table has no rows. -->
      <div class="row">
        <?php
        $fallback = [
          ['Outreach day', 'outreach-01.jpg'],
          ['Collection drive', 'outreach-02.jpg'],
          ['Packing together', 'outreach-03.jpg'],
          ['The team at work', 'outreach-04.jpg'],
          ['Delivery day', 'outreach-05.jpg'],
          ['Moments from the field', 'outreach-06.jpg'],
        ];
        foreach ($fallback as $p): ?>
          <div class="col-6 col-lg-4">
            <div class="gallery-item" data-title="<?= e($p[0]) ?>" data-category="Outreach" data-full="<?= e(asset('img/outreach/' . $p[1])) ?>">
              <img src="<?= e(asset('img/outreach/' . $p[1])) ?>" alt="<?= e($p[0]) ?> in Uganda" loading="lazy">
              <div class="gallery-caption"><h6><?= e($p[0]) ?></h6><small>Outreach</small></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
