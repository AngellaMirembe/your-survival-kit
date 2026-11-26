<?php
$adminActive = 'gallery.php';
$pageTitle   = 'Gallery';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    csrf_verify();
    $title   = trim($_POST['title'] ?? '');
    $caption = trim($_POST['caption'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $order   = (int) ($_POST['sort_order'] ?? 0);

    if ($title === '') {
        flash('error', 'A title is required.');
    } elseif ($id) {
        $pdo->prepare('UPDATE gallery SET title=?, caption=?, category=?, sort_order=? WHERE id=?')
            ->execute([$title, $caption, $category, $order, $id]);
        flash('success', 'Photo details updated.');
        redirect('admin/gallery.php');
    } else {
        if (empty($_FILES['image']['name'])) {
            flash('error', 'Please choose an image.');
        } else {
            $image = upload_image($_FILES['image'], 'gallery');
            if (!$image) {
                flash('error', 'Upload failed. Use JPG, PNG, WebP or GIF under 5 MB.');
            } else {
                $pdo->prepare('INSERT INTO gallery (title, caption, category, image, sort_order) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$title, $caption, $category, $image, $order]);
                flash('success', 'Photo added to the gallery.');
                redirect('admin/gallery.php');
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    csrf_verify();
    $del = (int) ($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT image FROM gallery WHERE id = ?');
    $row->execute([$del]);
    if ($g = $row->fetch()) {
        delete_image($g['image']);
        $pdo->prepare('DELETE FROM gallery WHERE id = ?')->execute([$del]);
        flash('success', 'Photo deleted.');
    }
    redirect('admin/gallery.php');
}

$photo = ['id' => 0, 'title' => '', 'caption' => '', 'category' => 'General', 'image' => null, 'sort_order' => 0];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$id]);
    $photo = $stmt->fetch() ?: $photo;
}

if ($action === 'new' || $action === 'edit'): ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $action === 'edit' ? 'Edit photo' : 'Add photo' ?></h2>
    <a href="<?= e(url('admin/gallery.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Gallery</a>
  </div>

  <form method="post" action="<?= e(url('admin/gallery.php')) ?>" enctype="multipart/form-data" class="admin-card">
    <?php csrf_field(); ?>
    <input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= (int) $photo['id'] ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="title">Title *</label>
        <input class="form-control" id="title" name="title" value="<?= e($_POST['title'] ?? $photo['title']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="category">Category</label>
        <input class="form-control" id="category" name="category" value="<?= e($_POST['category'] ?? $photo['category']) ?>" placeholder="Packing, Community…">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="sort_order">Sort order</label>
        <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) ($_POST['sort_order'] ?? $photo['sort_order']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="caption">Caption</label>
        <textarea class="form-control" id="caption" name="caption" rows="2"><?= e($_POST['caption'] ?? $photo['caption']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="image">Image <?= $action === 'new' ? '*' : '(leave blank to keep current)' ?></label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" data-preview="#imagePreview" <?= $action === 'new' ? 'required' : '' ?>>
        <img id="imagePreview" src="<?= e($photo['image'] ? UPLOAD_URL . rawurlencode($photo['image']) : asset('img/placeholder.jpg')) ?>" alt="Preview" class="mt-2 rounded" style="max-height:180px;<?= $photo['image'] ? '' : 'display:none' ?>">
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-ys" type="submit"><i class="bi bi-check2 me-1"></i> Save photo</button>
      <a href="<?= e(url('admin/gallery.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>

<?php else:
    $all = $pdo->query('SELECT * FROM gallery ORDER BY sort_order, id DESC')->fetchAll();
?>
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div><h2 class="mb-1">Gallery</h2><p class="text-secondary mb-0"><?= count($all) ?> photos. Uploaded images appear on the public gallery page. Confirm consent before publishing photos of children.</p></div>
    <a href="<?= e(url('admin/gallery.php?action=new')) ?>" class="btn btn-ys"><i class="bi bi-plus-lg me-1"></i> Add photo</a>
  </div>

  <div class="admin-card">
    <?php if ($all): ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Order</th><th>Added</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($all as $g): ?>
              <tr>
                <td><img class="table-img" src="<?= e(image_src($g['image'])) ?>" alt=""></td>
                <td><strong><?= e($g['title']) ?></strong><br><small class="text-secondary"><?= e(excerpt($g['caption'], 55)) ?></small></td>
                <td><?= e($g['category']) ?></td>
                <td><?= (int) $g['sort_order'] ?></td>
                <td><small><?= e(date('M j, Y', strtotime($g['created_at']))) ?></small></td>
                <td class="text-nowrap">
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/gallery.php?action=edit&id=' . $g['id'])) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                  <form method="post" action="<?= e(url('admin/gallery.php')) ?>" class="d-inline" data-confirm="Delete this photo?">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-secondary mb-0">No gallery entries yet. The outreach photos are already on the server in <code>uploads/</code> — run <code>bash apply-updates.sh</code> to add them to the gallery.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
