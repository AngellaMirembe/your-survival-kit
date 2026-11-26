<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'News & Stories';
$pageDescription = 'Read the latest updates, impact reports and practical guides from Your Survival Kit.';
$activePage      = 'news';

$perPage = 6;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$total = (int) $pdo->query('SELECT COUNT(*) FROM news WHERE status = "published"')->fetchColumn();
$posts = $pdo->prepare(
    'SELECT n.*, a.name AS author_name FROM news n
     LEFT JOIN admin_users a ON a.id = n.author_id
     WHERE n.status = "published" ORDER BY n.published_at DESC LIMIT ? OFFSET ?'
);
$posts->bindValue(1, $perPage, PDO::PARAM_INT);
$posts->bindValue(2, $offset, PDO::PARAM_INT);
$posts->execute();
$all = $posts->fetchAll();

$featured = array_shift($all);
include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">News & Stories</li>
    </ol></nav>
    <span class="eyebrow">From the field</span>
    <h1>Stories worth sharing</h1>
    <p>Guides, impact notes and honest reflections from a youth-led charity doing the work in its own community.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">

    <?php if ($featured): ?>
      <article class="card-ys mb-5 p-0 overflow-hidden">
        <div class="row g-0">
          <div class="col-md-6">
            <img src="<?= e(image_src($featured['image'])) ?>" alt="<?= e($featured['title']) ?>" class="w-100 h-100" style="object-fit:cover;min-height:320px">
          </div>
          <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-center">
            <span class="badge-ys align-self-start mb-3">Featured • <?= e($featured['category']) ?></span>
            <h2 class="mb-3"><a href="<?= e(url('news/' . $featured['slug'])) ?>" class="text-decoration-none"><?= e($featured['title']) ?></a></h2>
            <p class="text-secondary"><?= e(excerpt($featured['excerpt'] ?: strip_tags($featured['content']), 260)) ?></p>
            <p class="story-meta mb-4">
              <span><i class="bi bi-calendar3 me-1"></i><?= e(date('M j, Y', strtotime($featured['published_at'] ?? $featured['created_at']))) ?></span>
              <span><i class="bi bi-person me-1"></i><?= e($featured['author_name'] ?? 'Our team') ?></span>
            </p>
            <a href="<?= e(url('news/' . $featured['slug'])) ?>" class="btn btn-ys align-self-start">Read the story</a>
          </div>
        </div>
      </article>
    <?php endif; ?>

    <?php if ($all): ?>
      <div class="row g-4">
        <?php foreach ($all as $n): ?>
          <div class="col-md-6 col-lg-4">
            <article class="story-card">
              <a href="<?= e(url('news/' . $n['slug'])) ?>"><img src="<?= e(image_src($n['image'])) ?>" alt="<?= e($n['title']) ?>" loading="lazy"></a>
              <div class="story-body">
                <span class="badge-ys mb-2"><?= e($n['category']) ?></span>
                <h4><a href="<?= e(url('news/' . $n['slug'])) ?>"><?= e($n['title']) ?></a></h4>
                <p class="story-meta mt-2">
                  <span><i class="bi bi-calendar3 me-1"></i><?= e(date('M j, Y', strtotime($n['published_at'] ?? $n['created_at']))) ?></span>
                  <span><i class="bi bi-eye me-1"></i><?= (int) $n['views'] ?></span>
                </p>
                <p class="text-secondary small"><?= e(excerpt($n['excerpt'] ?: strip_tags($n['content']), 135)) ?></p>
                <a href="<?= e(url('news/' . $n['slug'])) ?>" class="fw-bold small text-brown">Continue reading <i class="bi bi-arrow-right"></i></a>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php elseif (!$featured): ?>
      <div class="text-center py-5"><p class="text-secondary">No stories published yet. Check back soon.</p></div>
    <?php endif; ?>

    <?php pagination($total, $perPage, $page, url('news')); ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
