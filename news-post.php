<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$stmt = $pdo->prepare(
    'SELECT n.*, a.name AS author_name FROM news n
     LEFT JOIN admin_users a ON a.id = n.author_id
     WHERE n.slug = ? AND n.status = "published" LIMIT 1'
);
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle       = 'Story not found';
    $pageDescription = 'The story you are looking for could not be found.';
    $activePage      = 'news';
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="section text-center">
      <div class="ys-container">
        <i class="bi bi-file-earmark-break" style="font-size:5rem;color:var(--ys-green)"></i>
        <h1 class="mt-3">This story went missing</h1>
        <p class="text-secondary">It may have been moved or unpublished. Try browsing all stories instead.</p>
        <a href="<?= e(url('news')) ?>" class="btn btn-ys mt-3">Back to News</a>
      </div>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Count a view (silent — never blocks the article)
$pdo->prepare('UPDATE news SET views = views + 1 WHERE id = ?')->execute([$post['id']]);

$related = $pdo->prepare(
    'SELECT id, title, slug, image, category FROM news
     WHERE status = "published" AND id != ? ORDER BY published_at DESC LIMIT 3'
);
$related->execute([$post['id']]);
$related = $related->fetchAll();

$pageTitle       = $post['title'];
$pageDescription = $post['excerpt'] ?: excerpt(strip_tags($post['content']), 160);
$ogImage         = image_src($post['image']);
$activePage      = 'news';
include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= e(url('news')) ?>">News & Stories</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e(excerpt($post['title'], 45)) ?></li>
    </ol></nav>
    <span class="badge-ys mb-3"><?= e($post['category']) ?></span>
    <h1 style="font-size:clamp(1.8rem,4vw,2.8rem)"><?= e($post['title']) ?></h1>
    <p class="story-meta mt-3 mb-0">
      <span><i class="bi bi-person me-1"></i> By <?= e($post['author_name'] ?? 'Our team') ?></span>
      <span><i class="bi bi-calendar3 me-1"></i><?= e(date('F j, Y', strtotime($post['published_at'] ?? $post['created_at']))) ?></span>
      <span><i class="bi bi-eye me-1"></i><?= ((int) $post['views'] + 1) ?> views</span>
    </p>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <img src="<?= e(image_src($post['image'])) ?>" alt="<?= e($post['title']) ?>" class="img-fluid w-100 mb-4 rounded-4" style="max-height:480px;object-fit:cover">
        <?php if ($post['excerpt']): ?>
          <p class="lead text-secondary"><?= e($post['excerpt']) ?></p>
        <?php endif; ?>
        <div class="article-content">
          <?= $post['content'] // Authored by trusted admin users through the admin panel ?>
        </div>

        <hr class="my-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
          <a href="<?= e(url('news')) ?>" class="btn btn-outline-ys"><i class="bi bi-arrow-left me-1"></i> All stories</a>
          <div>
            <span class="text-secondary small me-2">Share:</span>
            <a class="btn btn-sm btn-outline-secondary rounded-circle" target="_blank" rel="noopener"
               href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('news/' . $post['slug'])) ?>" aria-label="Share on Facebook"><i class="bi bi-facebook"></i></a>
            <a class="btn btn-sm btn-outline-secondary rounded-circle" target="_blank" rel="noopener"
               href="https://twitter.com/intent/tweet?url=<?= urlencode(url('news/' . $post['slug'])) ?>&text=<?= urlencode($post['title']) ?>" aria-label="Share on X"><i class="bi bi-twitter-x"></i></a>
            <a class="btn btn-sm btn-outline-secondary rounded-circle" target="_blank" rel="noopener"
               href="mailto:?subject=<?= urlencode($post['title']) ?>&body=<?= urlencode(url('news/' . $post['slug'])) ?>" aria-label="Share by email"><i class="bi bi-envelope"></i></a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($related): ?>
      <div class="mt-5 pt-5 border-top">
        <h3 class="mb-4">Keep reading</h3>
        <div class="row g-4">
          <?php foreach ($related as $r): ?>
            <div class="col-md-4">
              <article class="story-card">
                <a href="<?= e(url('news/' . $r['slug'])) ?>"><img src="<?= e(image_src($r['image'])) ?>" alt="<?= e($r['title']) ?>" loading="lazy"></a>
                <div class="story-body">
                  <span class="badge-ys mb-2"><?= e($r['category']) ?></span>
                  <h5 class="h6"><a href="<?= e(url('news/' . $r['slug'])) ?>"><?= e($r['title']) ?></a></h5>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
