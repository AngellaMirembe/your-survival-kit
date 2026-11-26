<?php
$adminActive = 'events.php';
$pageTitle   = 'Events';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/mail.php';

$action = $_GET['action'] ?? 'list';
$id     = (int) ($_GET['id'] ?? 0);

/* ---------------- Save ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    csrf_verify();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['event_date'] ?? '');
    $time        = trim($_POST['event_time'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $status      = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'published';
    $notify      = !empty($_POST['notify']);

    if ($title === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        flash('error', 'A title and a valid event date are required.');
        redirect('admin/events.php');
    }

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = upload_image($_FILES['image'], 'event');
        if (!$image) flash('warning', 'The image could not be uploaded (JPG, PNG, WebP or GIF, max 5 MB).');
    }

    if ($id) {
        $sql = 'UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, status=?'
             . ($image ? ', image=?' : '') . ' WHERE id=?';
        $params = [$title, $description, $date, $time ?: null, $location ?: null, $status];
        if ($image) $params[] = $image;
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        flash('success', 'Event updated.');
        $savedId = $id;
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO events (title, description, event_date, event_time, location, image, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $description, $date, $time ?: null, $location ?: null, $image, $status]);
        flash('success', 'Event created.');
        $savedId = (int) $pdo->lastInsertId();
    }

    if (!$id && $notify && $status === 'published') {
        $row = $pdo->prepare('SELECT * FROM events WHERE id = ?');
        $row->execute([$savedId]);
        if ($event = $row->fetch()) {
            $sent = notify_subscribers_about_event($event);
            flash('success', $sent
                ? $sent . ' confirmed subscriber(s) emailed about this event.'
                : 'Event saved. No confirmed subscribers to email yet.');
        }
    }

    redirect('admin/events.php');
}

/* ---------------- Delete ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    csrf_verify();
    $del = (int) ($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT image FROM events WHERE id = ?');
    $row->execute([$del]);
    if ($ev = $row->fetch()) {
        delete_image($ev['image']);
        $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$del]);
        flash('success', 'Event deleted.');
    }
    redirect('admin/events.php');
}

/* ---------------- Form data ---------------- */
$event = [
    'id' => 0, 'title' => '', 'description' => '', 'event_date' => date('Y-m-d'),
    'event_time' => '', 'location' => '', 'image' => null, 'status' => 'published',
];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$id]);
    $event = $stmt->fetch() ?: $event;
}

if ($action === 'new' || $action === 'edit'): ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $action === 'edit' ? 'Edit event' : 'New event' ?></h2>
    <a href="<?= e(url('admin/events.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All events</a>
  </div>

  <form method="post" action="<?= e(url('admin/events.php')) ?>" enctype="multipart/form-data" class="admin-card">
    <?php csrf_field(); ?>
    <input type="hidden" name="do" value="save">
    <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
    <div class="row g-3">
      <div class="col-lg-8">
        <label class="form-label" for="title">Event title *</label>
        <input class="form-control" id="title" name="title" value="<?= e($_POST['title'] ?? $event['title']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" id="status" name="status">
          <option value="published" <?= ($_POST['status'] ?? $event['status']) === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= ($_POST['status'] ?? $event['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="event_date">Date *</label>
        <input type="date" class="form-control" id="event_date" name="event_date" value="<?= e($_POST['event_date'] ?? $event['event_date']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="event_time">Time <small class="text-secondary">(optional)</small></label>
        <input class="form-control" id="event_time" name="event_time" value="<?= e($_POST['event_time'] ?? $event['event_time']) ?>" placeholder="e.g. 10:00am">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="location">Location</label>
        <input class="form-control" id="location" name="location" value="<?= e($_POST['location'] ?? $event['location']) ?>" placeholder="Leave blank if shared privately">
      </div>
      <div class="col-12">
        <label class="form-label" for="description">What is happening?</label>
        <textarea class="form-control" id="description" name="description" rows="5"><?= e($_POST['description'] ?? $event['description']) ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="image">Event image</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" data-preview="#imagePreview">
        <img id="imagePreview" src="<?= e($event['image'] ? UPLOAD_URL . rawurlencode($event['image']) : asset('img/placeholder.jpg')) ?>" alt="Preview" class="mt-2 rounded" style="max-height:140px;<?= $event['image'] ? '' : 'display:none' ?>">
      </div>
      <div class="col-md-6">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" id="notify" name="notify" value="1" <?= $action === 'new' ? 'checked' : '' ?>>
          <label class="form-check-label" for="notify">Email confirmed subscribers about this new event</label>
        </div>
        <small class="text-secondary">Only works for a newly created, published event. Existing subscribers who have verified their email will receive the update.</small>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button class="btn btn-ys" type="submit"><i class="bi bi-check2 me-1"></i> Save event</button>
      <a href="<?= e(url('admin/events.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>

<?php else:
    $all = $pdo->query('SELECT * FROM events ORDER BY event_date ASC, id DESC')->fetchAll();
    $now = date('Y-m-d');
?>
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div><h2 class="mb-1">Upcoming events</h2><p class="text-secondary mb-0">Add an event here and it appears on the public Events tab. Tick “email subscribers” to announce it.</p></div>
    <a href="<?= e(url('admin/events.php?action=new')) ?>" class="btn btn-ys"><i class="bi bi-plus-lg me-1"></i> New event</a>
  </div>

  <div class="admin-card">
    <?php if ($all): ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead><tr><th>Event</th><th>Date</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($all as $ev): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($ev['image']): ?><img src="<?= e(image_src($ev['image'])) ?>" class="table-img" alt=""><?php endif; ?>
                    <div><strong><?= e($ev['title']) ?></strong><br><small class="text-secondary"><?= e(excerpt($ev['description'] ?? '', 60)) ?></small></div>
                  </div>
                </td>
                <td><small><?= e(date('M j, Y', strtotime($ev['event_date']))) ?><?= $ev['event_time'] ? ' · ' . e($ev['event_time']) : '' ?></small></td>
                <td><small><?= e($ev['location'] ?: 'Shared privately') ?></small></td>
                <td>
                  <span class="status-pill status-<?= e($ev['status']) ?>"><?= e(ucfirst($ev['status'])) ?></span>
                  <?php if ($ev['event_date'] < $now): ?><br><small class="text-secondary">Past</small><?php endif; ?>
                </td>
                <td class="text-nowrap">
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/events.php?action=edit&id=' . (int) $ev['id'])) ?>"><i class="bi bi-pencil"></i></a>
                  <form method="post" action="<?= e(url('admin/events.php')) ?>" class="d-inline" data-confirm="Delete this event?">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $ev['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-secondary mb-0">No events yet. Click <strong>New event</strong> to add the one you mentioned.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
