<?php
$adminActive = 'news.php';
$pageTitle   = 'News & Stories';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);

/* ---------------- Save (create / update) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    csrf_verify();
    $title   = trim($_POST['title'] ?? '');
    $slug    = trim($_POST['slug'] ?? '') ?: slugify($title);
    $category = trim($_POST['category'] ?? 'News');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $status  = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
    $publish = $status === 'published' ? ($_POST['published_at'] ?? date('Y-m-d\TH:i')) : null;

    if ($title === '' || $content === '') {
        flash('error', 'A title and content are required.');
    } else {
        // Make the slug unique
        $check = $pdo->prepare('SELECT id FROM news WHERE slug = ?' . ($id ? ' AND id != ?' : ''));
        $check->execute($id ? [$slug, $id] : [$slug]);
        if ($check->fetch()) {
            $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 5);
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = upload_image($_FILES['image'], 'story');
            if (!$image) flash('warning', 'The image could not be uploaded (allowed: JPG, PNG, WebP, GIF, max 5 MB).');
        }

        if ($id) {
            $sql = 'UPDATE news SET title=?, slug=?, category=?, excerpt=?, content=?, status=?, published_at=?'
                 . ($image ? ', image=?' : '') . ' WHERE id=?';
            $params = [$title, $slug, $category, $excerpt, $content, $status, $publish];
            if ($image) $params[] = $image;
            $params[] = $id;
            $pdo->prepare($sql)->execute($params);
            flash('success', 'Story updated.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO news (title, slug, category, excerpt, content, image, author_id, status, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$title, $slug, $category, $excerpt, $content, $image, $_SESSION['admin_id'], $status, $publish]);
            flash('success', 'Story created.');
        }
        redirect('admin/news.php');
    }
}

/* ---------------- Delete ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    csrf_verify();
    $del = (int) ($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT image FROM news WHERE id = ?');
    $row->execute([$del]);
    if ($img = $row->fetch()) {
        delete_image($img['image']);
        $pdo->prepare('DELETE FROM news WHERE id = ?')->execute([$del]);
        flash('success', 'Story deleted.');
    }
    redirect('admin/news.php');
}

/* ---------------- Form data ---------------- */
$story = ['id' => 0, 'title' => '', 'slug' => '', 'category' => 'News', 'excerpt' => '', 'content' => '', 'image' => null, 'status' => 'draft', 'published_at' => date('Y-m-d\TH:i')];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM news WHERE id = ?');
    $stmt->execute([$id]);
    $story = $stmt->fetch() ?: $story;
}

if ($action === 'new' || $action === 'edit'): ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $action === 'edit' ? 'Edit story' : 'New story' ?></h2>
    <a href="<?= e(url('admin/news.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All stories</a>
  </div>

  <form method="post" action="<?= e(url('admin/news.php')) ?>" enctype="multipart/form-data" class="admin-card">
    <?php csrf_field(); ?>
    <input type="hidden" name="do" value="save">
    <input type="hidden" name="id" value="<?= (int) $story['id'] ?>">
    <div class="row g-3">
      <div class="col-lg-8">
        <label class="form-label" for="title">Title *</label>
        <input class="form-control" id="title" name="title" value="<?= e($_POST['title'] ?? $story['title']) ?>" required>
      </div>
      <div class="col-lg-4">
        <label class="form-label" for="slug">URL slug</label>
        <input class="form-control" id="slug" name="slug" value="<?= e($_POST['slug'] ?? $story['slug']) ?>" placeholder="auto-from-title">
        <small class="text-secondary">Leave blank to generate from the title.</small>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="category">Category</label>
        <input class="form-control" id="category" name="category" value="<?= e($_POST['category'] ?? $story['category']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" id="status" name="status">
          <option value="draft" <?= ($_POST['status'] ?? $story['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="published" <?= ($_POST['status'] ?? $story['status']) === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="published_at">Publish date</label>
        <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="<?= e($_POST['published_at'] ?? ($story['published_at'] ? date('Y-m-d\TH:i', strtotime($story['published_at'])) : date('Y-m-d\TH:i'))) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="excerpt">Short summary</label>
        <textarea class="form-control" id="excerpt" name="excerpt" rows="2"><?= e($_POST['excerpt'] ?? $story['excerpt']) ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label" for="content">Article content * <small class="text-secondary">(HTML allowed)</small></label>
        <textarea class="form-control" id="content" name="content" rows="14" required data-counter="#contentCount"><?= e($_POST['content'] ?? $story['content']) ?></textarea>
        <small class="text-secondary d-block mt-1"><span id="contentCount">0</span> characters</small>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="image">Cover image</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" data-preview="#imagePreview">
        <img id="imagePreview" src="<?= e($story['image'] ? UPLOAD_URL . rawurlencode($story['image']) : asset('img/placeholder.jpg')) ?>" alt="Preview" class="mt-2 rounded" style="max-height:130px;<?= $story['image'] ? '' : 'display:none' ?>">
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-ys" type="submit"><i class="bi bi-check2 me-1"></i> Save story</button>
      <a href="<?= e(url('admin/news.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>

<?php else:
    $all = $pdo->query('SELECT n.*, a.name AS author_name FROM news n LEFT JOIN admin_users a ON a.id=n.author_id ORDER BY n.created_at DESC')->fetchAll();
?>
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div><h2 class="mb-1">News & Stories</h2><p class="text-secondary mb-0"><?= count($all) ?> stories in total.</p></div>
    <a href="<?= e(url('admin/news.php?action=new')) ?>" class="btn btn-ys"><i class="bi bi-plus-lg me-1"></i> New story</a>
  </div>

  <div class="admin-card">
    <?php if ($all): ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($all as $n): ?>
              <tr>
                <td><img class="table-img" src="<?= e(image_src($n['image'])) ?>" alt=""></td>
                <td><strong><?= e(excerpt($n['title'], 55)) ?></strong><br><small class="text-secondary"><?= e($n['slug']) ?></small></td>
                <td><?= e($n['category']) ?></td>
                <td><?= e($n['author_name'] ?? '—') ?></td>
                <td><span class="status-pill status-<?= e($n['status']) ?>"><?= e(ucfirst($n['status'])) ?></span></td>
                <td><?= (int) $n['views'] ?></td>
                <td class="text-nowrap">
                  <?php if ($n['status'] === 'published'): ?>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('news/' . $n['slug'])) ?>" target="_blank" title="View"><i class="bi bi-eye"></i></a>
                  <?php endif; ?>
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/news.php?action=edit&id=' . $n['id'])) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="post" action="<?= e(url('admin/news.php')) ?>" class="d-inline" data-confirm="Delete this story permanently?">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-secondary mb-0">No stories yet. Create your first one.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
