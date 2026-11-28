<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Gather What We Have. Give What Matters.';
$pageDescription = 'A small, youth-led group in Uganda gathering everyday essentials and giving them to children in underserved communities.';
$activePage      = 'home';
$ogImage         = asset('img/outreach/outreach-09.jpg');

$featured = $pdo->query(
    'SELECT n.*, a.name AS author_name FROM news n
     LEFT JOIN admin_users a ON a.id = n.author_id
     WHERE n.status = "published" ORDER BY n.published_at DESC LIMIT 3'
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="ys-container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-7">
        <img src="<?= e(asset('img/logo.png')) ?>" alt="Your Survival Kit emblem" class="hero-logo">
        <span class="badge-ys mb-3"><?= e(setting('home_hero_eyebrow', 'Youth-led group • Uganda')) ?></span>
        <h1><?= e(setting('home_hero_title', 'Gather what we have.')) ?><br><span style="color:#e5b24b"><?= e(setting('home_hero_title_accent', 'Give what matters.')) ?></span></h1>
        <p><?= e(setting('home_hero_text', 'We gather everyday essentials, pack them with care and hand them directly to children in communities we know.')) ?></p>
        <div class="mt-4">
          <a href="<?= e(url('donate')) ?>" class="btn btn-ys btn-lg"><i class="bi bi-heart-fill me-2"></i> Give a kit</a>
          <a href="<?= e(url('what-we-do')) ?>" class="btn btn-light-ys btn-lg">See our work</a>
        </div>
        <div class="d-flex align-items-center gap-3 mt-5 flex-wrap">
          <div class="d-flex">
            <?php foreach (array_slice(setting_json('home_hero_images', ['outreach-01.jpg','outreach-02.jpg','outreach-03.jpg']), 0, 3) as $photo): ?>
              <img src="<?= e(image_src($photo)) ?>" class="rounded-circle border border-2 border-light ms-2" width="42" height="42" alt="Your Survival Kit outreach">
            <?php endforeach; ?>
          </div>
          <small class="text-white-50"><?= e(setting('home_hero_credit', 'Young volunteers and neighbours making it happen')) ?></small>
        </div>
      </div>
      <div class="col-lg-5 hero-image d-none d-lg-block">
        <img src="<?= e(asset('img/outreach/outreach-09.jpg')) ?>" alt="Your Survival Kit outreach in Uganda" class="img-fluid">
      </div>
    </div>
  </div>
</section>

<!-- What we promise -->
<section class="section-sm section-alt">
  <div class="ys-container">
    <div class="row g-3">
      <?php
      $promises = [
        ['bi-people', 'Small by choice', 'A handful of volunteers, not a big institution.'],
        ['bi-hand-thumbs-up', 'Hands-on', 'We collect, pack and deliver the items ourselves.'],
        ['bi-journal-check', 'Honest records', 'Every gift is noted before it leaves our hands.'],
        ['bi-geo-alt', 'Local first', 'We work in communities we actually know.'],
      ];
      foreach ($promises as $p): ?>
        <div class="col-6 col-lg-3">
          <div class="stat-box">
            <span class="stat-label mb-2"><i class="bi <?= $p[0] ?> fs-4 d-block text-brown"></i><?= e($p[1]) ?></span>
            <small class="text-secondary d-block mt-1"><?= e($p[2]) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Mission -->
<section class="section">
  <div class="ys-container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6">
        <div class="feature-img">
          <img src="<?= e(asset('img/outreach/outreach-10.jpg')) ?>" alt="Young volunteers at a Your Survival Kit outreach in Uganda">
        </div>
      </div>
      <div class="col-lg-6 ps-lg-5">
        <span class="eyebrow"><i class="bi bi-stars me-1"></i> Why we exist</span>
        <h2 class="section-title">Small gifts, gathered together, become real change.</h2>
        <p class="lead-lg">Too many children start the day without the basics that make school and home feel safe. A notebook, a warm layer, a bar of soap — these are ordinary to us and life-changing to them.</p>
        <p class="text-secondary">Your Survival Kit removes the distance between wanting to help and actually helping. We gather what our community already has, sort it with care, and hand it over in person to children who need it.</p>
        <p class="text-secondary">We are deliberately small. That means we know the families we support, we can follow up, and we can tell you exactly where a gift went — without a warehouse, a big office or a long chain of approvals.</p>
        <div class="row mt-4 g-3">
          <div class="col-sm-6">
            <div class="d-flex gap-3 align-items-start">
              <div class="icon-box green flex-shrink-0"><i class="bi bi-lightning-charge"></i></div>
              <div><h6 class="mb-1">Fast & local</h6><p class="small text-secondary mb-0">We respond in communities we know.</p></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex gap-3 align-items-start">
              <div class="icon-box flex-shrink-0"><i class="bi bi-shield-check"></i></div>
              <div><h6 class="mb-1">Personal</h6><p class="small text-secondary mb-0">The people who collect a gift are the ones who deliver it.</p></div>
            </div>
          </div>
        </div>
        <a href="<?= e(url('about')) ?>" class="btn btn-outline-ys mt-4">Read our story <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- What we do preview -->
<section class="section section-alt">
  <div class="ys-container">
    <div class="text-center mb-5">
      <span class="eyebrow">What we do</span>
      <h2 class="section-title">Four ways we show up</h2>
      <p class="lead-lg mx-auto">Practical support designed around what children actually need, not what is convenient to give.</p>
    </div>
    <div class="row g-4">
      <?php
      $programs = [
        ['bi-bag-heart', 'School Survival Kits', 'Notebooks, pencils, bags and basic stationery so every child can learn with confidence.'],
        ['bi-person-bounding-box', 'Clothing & Care', 'Clothes, socks and simple hygiene items collected from families and passed on respectfully.'],
        ['bi-cup-hot', 'Food & Nutrition', 'Shelf-stable snacks and reusable bottles for children who arrive at school hungry.'],
        ['bi-people', 'Mentorship & Play', 'Young volunteers who listen, play and remind children that they are not forgotten.'],
      ];
      foreach ($programs as $i => $p): ?>
        <div class="col-md-6 col-lg-3">
          <div class="card-ys">
            <div class="icon-box <?= $i % 2 ? 'green' : '' ?>"><i class="bi <?= $p[0] ?>"></i></div>
            <h4><?= e($p[1]) ?></h4>
            <p><?= e($p[2]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-5">
      <a href="<?= e(url('what-we-do')) ?>" class="btn btn-ys">Explore our programmes</a>
    </div>
  </div>
</section>

<!-- Featured stories -->
<?php if ($featured): ?>
<section class="section">
  <div class="ys-container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3">
      <div>
        <span class="eyebrow">From the field</span>
        <h2 class="section-title mb-0">Latest stories</h2>
      </div>
      <a href="<?= e(url('news')) ?>" class="btn btn-outline-ys">All stories <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $n): ?>
        <div class="col-md-6 col-lg-4">
          <article class="story-card">
            <a href="<?= e(url('news/' . $n['slug'])) ?>">
              <img src="<?= e(image_src($n['image'])) ?>" alt="<?= e($n['title']) ?>" loading="lazy">
            </a>
            <div class="story-body">
              <div class="story-meta">
                <span><i class="bi bi-calendar3 me-1"></i><?= e(date('M j, Y', strtotime($n['published_at'] ?? $n['created_at']))) ?></span>
                <span><i class="bi bi-person me-1"></i><?= e($n['author_name'] ?? 'Our team') ?></span>
              </div>
              <span class="badge-ys mb-2"><?= e($n['category']) ?></span>
              <h4><a href="<?= e(url('news/' . $n['slug'])) ?>"><?= e($n['title']) ?></a></h4>
              <p class="text-secondary small mt-2 mb-3"><?= e(excerpt($n['excerpt'] ?: strip_tags($n['content']), 130)) ?></p>
              <a href="<?= e(url('news/' . $n['slug'])) ?>" class="fw-bold small text-brown">Read story <i class="bi bi-arrow-right"></i></a>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- How it works -->
<section class="section section-dark">
  <div class="ys-container">
    <div class="text-center mb-5">
      <span class="eyebrow">Simple by design</span>
      <h2 class="section-title"><?= e(setting('home_how_title', 'How it works')) ?></h2>
      <p class="lead-lg mx-auto text-white-50"><?= e(setting('home_how_text', 'Three simple steps. No warehouses, no middlemen — just a short chain we can account for.')) ?></p>
    </div>
    <div class="row g-4 text-center">
      <?php
      $stepIcons = ['bi-box-seam', 'bi-clipboard-check', 'bi-person-check'];
      $steps = setting_json('home_how_steps', [
        ['title' => 'Gather', 'text' => 'Families and young people give what they can.'],
        ['title' => 'Sort & pack', 'text' => 'Volunteers check every item and add a handwritten note.'],
        ['title' => 'Deliver', 'text' => 'We hand each kit to a school or family ourselves.'],
      ]);
      foreach (array_slice($steps, 0, 3) as $i => $s): ?>
        <div class="col-md-4">
          <div class="p-4">
            <div class="mx-auto mb-3 rounded-circle" style="width:78px;height:78px;background:rgba(150,186,172,.18);font-size:1.9rem;color:#96baac;display:grid;place-items:center">
              <i class="bi <?= e($stepIcons[$i] ?? 'bi-check2') ?>"></i>
            </div>
            <h5>Step <?= $i + 1 ?>: <?= e($s['title'] ?? '') ?></h5>
            <p class="text-white-50 small mb-0"><?= e($s['text'] ?? '') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="ys-container">
    <div class="cta-band d-flex flex-wrap align-items-center justify-content-between gap-4">
      <div>
        <h2 class="mb-2">A child is waiting for a kit.</h2>
        <p class="mb-0">Your gift — large or small — becomes something a child can hold, use and remember.</p>
      </div>
      <div>
        <a href="<?= e(url('donate')) ?>" class="btn btn-light-ys btn-lg me-2">Donate now</a>
        <a href="<?= e(url('volunteer') . '#apply') ?>" class="btn btn-outline-light btn-lg">Become a volunteer</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
