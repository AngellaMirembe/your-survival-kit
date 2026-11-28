<?php
$adminActive = 'gallery.php';
$pageTitle   = 'Gallery';
// Authenticate before any output so POST handlers can redirect cleanly.
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();

$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);

/** Normalise the awkward $_FILES shape from a multiple file input. */
function gallery_files(string $key): array
{
    $out = [];
    if (empty($_FILES[$key]) || !is_array($_FILES[$key]['name'] ?? null)) return $out;
    foreach ($_FILES[$key]['name'] as $i => $name) {
        if ($name === '' || ($_FILES[$key]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $out[] = [
            'name'     => $name,
            'type'     => $_FILES[$key]['type'][$i] ?? '',
            'tmp_name' => $_FILES[$key]['tmp_name'][$i] ?? '',
            'error'    => $_FILES[$key]['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $_FILES[$key]['size'][$i] ?? 0,
        ];
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    csrf_verify();
    $title    = trim($_POST['title'] ?? '');
    $caption  = trim($_POST['caption'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $order    = (int) ($_POST['sort_order'] ?? 0);
    $consent  = !empty($_POST['consent_confirmed']) ? 1 : 0;
    $saveId   = (int) ($_POST['id'] ?? $id);

    if ($title === '') {
        flash('error', 'A title is required.');
    } elseif ($saveId) {
        $pdo->prepare('UPDATE gallery SET title=?, caption=?, category=?, sort_order=?, consent_confirmed=? WHERE id=?')
            ->execute([$title, $caption, $category, $order, $consent, $saveId]);
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
                $pdo->prepare('INSERT INTO gallery (title, caption, category, image, sort_order, consent_confirmed) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$title, $caption, $category, $image, $order, $consent]);
                flash('success', 'Photo added to the gallery.');
                redirect('admin/gallery.php');
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'bulk') {
    csrf_verify();
    $files    = gallery_files('images');
    $category = trim($_POST['bulk_category'] ?? 'Outreach');
    $consent  = !empty($_POST['bulk_consent']) ? 1 : 0;
    $added = 0;
    $failed = 0;
    foreach ($files as $file) {
        $image = upload_image($file, 'gallery');
        if (!$image) { $failed++; continue; }
        $title = ucwords(str_replace(['-', '_'], ' ', pathinfo($file['name'], PATHINFO_FILENAME)));
        $pdo->prepare('INSERT INTO gallery (title, caption, category, image, sort_order, consent_confirmed) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$title, '', $category, $image, 999 + $added, $consent]);
        $added++;
    }
    if ($added) flash('success', "$added photo" . ($added === 1 ? '' : 's') . " uploaded." . ($failed ? " $failed failed." : ''));
    else flash('error', 'No photos were uploaded. Use JPG, PNG, WebP or GIF under 5 MB.');
    redirect('admin/gallery.php');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'reorder') {
    csrf_verify();
    $moveId = (int) ($_POST['id'] ?? 0);
    $dir    = $_POST['direction'] ?? '';
    $rows   = $pdo->query('SELECT id FROM gallery ORDER BY sort_order, id')->fetchAll(PDO::FETCH_COLUMN);
    $index  = array_search($moveId, array_map('intval', $rows), true);
    $swap   = $index + ($dir === 'up' ? -1 : 1);
    if ($index !== false && $swap >= 0 && $swap < count($rows)) {
        [$rows[$index], $rows[$swap]] = [$rows[$swap], $rows[$index]];
        $stmt = $pdo->prepare('UPDATE gallery SET sort_order=? WHERE id=?');
        foreach ($rows as $pos => $rowId) $stmt->execute([$pos, $rowId]);
        flash('success', 'Photo order updated.');
    }
    redirect('admin/gallery.php');
}

// Layout only after POST handling, so redirects send no half-rendered page.
require_once __DIR__ . '/includes/header.php';

$photo = ['id' => 0, 'title' => '', 'caption' => '', 'category' => 'General', 'image' => null, 'sort_order' => 0, 'consent_confirmed' => 0];
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
        <img id="imagePreview" src="<?= e(image_src($photo['image'])) ?>" alt="Preview" class="mt-2 rounded" style="max-height:180px;<?= $photo['image'] ? '' : 'display:none' ?>">
      </div>
      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="consent" name="consent_confirmed" value="1" <?= !empty($photo['consent_confirmed']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="consent">I confirm this photo may be shown publicly (required for photos of children)</label>
        </div>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-ys" type="submit"><i class="bi bi-check2 me-1"></i> Save photo</button>
      <a href="<?= e(url('admin/gallery.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>

<?php else:
    $where = [];
    $params = [];
    $categoryFilter = trim($_GET['category'] ?? '');
    $search = trim($_GET['search'] ?? '');
    if ($categoryFilter !== '') { $where[] = 'category = ?'; $params[] = $categoryFilter; }
    if ($search !== '') { $where[] = '(title LIKE ? OR caption LIKE ? OR image LIKE ?)'; $like = "%$search%"; $params = array_merge($params, [$like, $like, $like]); }
    $sql = 'SELECT * FROM gallery' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY sort_order, id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all = $stmt->fetchAll();
    $categories = $pdo->query('SELECT DISTINCT category FROM gallery ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
    $hiddenCount = (int) $pdo->query('SELECT COUNT(*) FROM gallery WHERE consent_confirmed = 0')->fetchColumn();
    $missingCount = 0;
    foreach ($pdo->query('SELECT image FROM gallery')->fetchAll(PDO::FETCH_COLUMN) as $img) {
        if (image_location_status($img) === 'missing') $missingCount++;
    }
?>
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div><h2 class="mb-1">Gallery</h2><p class="text-secondary mb-0"><?= count($all) ?> shown. Uploaded images appear on the public gallery page. Confirm consent before publishing photos of children.</p></div>
    <div class="d-flex gap-2">
      <a href="<?= e(url('admin/gallery.php?action=new')) ?>" class="btn btn-outline-ys"><i class="bi bi-image me-1"></i> Add one photo</a>
      <button class="btn btn-ys" type="button" data-bs-toggle="collapse" data-bs-target="#bulkUpload"><i class="bi bi-images me-1"></i> Upload many</button>
    </div>
  </div>

  <?php if ($hiddenCount || $missingCount): ?>
    <div class="alert alert-warning small">
      <i class="bi bi-exclamation-triangle me-1"></i>
      <?php if ($hiddenCount): ?><?= $hiddenCount ?> photo<?= $hiddenCount === 1 ? ' is' : 's are' ?> hidden from the public site until consent is confirmed. <?php endif; ?>
      <?php if ($missingCount): ?><?= $missingCount ?> photo<?= $missingCount === 1 ? ' has' : 's have' ?> a missing image file — check the image column below.<?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="collapse admin-card mb-4" id="bulkUpload">
    <h5 class="mb-1">Upload several photos at once</h5>
    <p class="text-secondary small">Hold Ctrl (Windows) or Command (Mac) to choose more than one file. Titles are created from the file names.</p>
    <form method="post" action="<?= e(url('admin/gallery.php')) ?>" enctype="multipart/form-data">
      <?php csrf_field(); ?>
      <input type="hidden" name="do" value="bulk">
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label" for="images">Photos from your device</label>
          <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple required>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="bulk_category">Category</label>
          <input class="form-control" id="bulk_category" name="bulk_category" value="Outreach" maxlength="80">
        </div>
        <div class="col-md-2">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="bulk_consent" name="bulk_consent" value="1" checked>
            <label class="form-check-label small" for="bulk_consent">Confirm consent</label>
          </div>
        </div>
        <div class="col-md-2"><button class="btn btn-ys w-100" type="submit"><i class="bi bi-upload me-1"></i> Upload</button></div>
      </div>
    </form>
  </div>

  <form class="admin-card mb-4 row g-2 align-items-end" method="get" action="<?= e(url('admin/gallery.php')) ?>">
    <div class="col-md-4">
      <label class="form-label small" for="search">Search</label>
      <input class="form-control form-control-sm" id="search" name="search" value="<?= e($search) ?>" placeholder="Title, caption or file name">
    </div>
    <div class="col-md-3">
      <label class="form-label small" for="category">Category</label>
      <select class="form-select form-select-sm" id="category" name="category">
        <option value="">All categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e($cat) ?>" <?= $cat === $categoryFilter ? 'selected' : '' ?>><?= e($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto"><button class="btn btn-outline-ys btn-sm" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button></div>
    <?php if ($search !== '' || $categoryFilter !== ''): ?><div class="col-auto"><a class="btn btn-link btn-sm" href="<?= e(url('admin/gallery.php')) ?>">Clear</a></div><?php endif; ?>
  </form>

  <div class="admin-card">
    <?php if ($all): ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($all as $g):
              $loc = image_location_status($g['image']);
            ?>
              <tr>
                <td>
                  <img class="table-img" src="<?= e(image_src($g['image'])) ?>" alt="">
                  <?php if ($loc === 'missing'): ?><br><small class="text-danger">File missing</small><?php elseif ($loc === 'outreach'): ?><br><small class="text-secondary">Outreach copy</small><?php endif; ?>
                </td>
                <td><strong><?= e($g['title']) ?></strong><br><small class="text-secondary"><?= e(excerpt($g['caption'], 55)) ?></small></td>
                <td><?= e($g['category']) ?></td>
                <td class="text-nowrap">
                  <form method="post" action="<?= e(url('admin/gallery.php')) ?>" class="d-inline">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="do" value="reorder"><input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
                    <button class="btn btn-sm btn-outline-secondary" name="direction" value="up" title="Move up"><i class="bi bi-arrow-up"></i></button>
                    <button class="btn btn-sm btn-outline-secondary" name="direction" value="down" title="Move down"><i class="bi bi-arrow-down"></i></button>
                  </form>
                  <small class="text-secondary ms-1"><?= (int) $g['sort_order'] ?></small>
                </td>
                <td>
                  <?php if (empty($g['consent_confirmed'])): ?><span class="badge bg-secondary">Hidden</span><?php else: ?><span class="badge bg-success">Public</span><?php endif; ?>
                </td>
                <td class="text-nowrap">
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/gallery.php?action=edit&id=' . $g['id'])) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                  <a class="btn btn-sm btn-outline-info" href="<?= e(url('admin/content.php?tab=about')) ?>" title="Use in page content"><i class="bi bi-layout-text-window"></i></a>
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
      <p class="text-secondary mb-0">No gallery entries match this filter. Upload photos above, or clear the filter.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
