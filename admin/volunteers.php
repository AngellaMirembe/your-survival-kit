<?php
$adminActive = 'volunteers.php';
$pageTitle   = 'Volunteers';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'status') {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['new', 'contacted', 'joined', 'declined'], true)) {
            $pdo->prepare('UPDATE volunteers SET status = ? WHERE id = ?')->execute([$status, $id]);
            flash('success', 'Volunteer status updated.');
        }
    } elseif (($_POST['do'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM volunteers WHERE id = ?')->execute([$id]);
        flash('success', 'Application deleted.');
    }
    redirect('admin/volunteers.php');
}

$filter = $_GET['status'] ?? '';
if (!in_array($filter, ['', 'new', 'contacted', 'joined', 'declined'], true)) $filter = '';
$stmt = $pdo->prepare($filter ? 'SELECT * FROM volunteers WHERE status=? ORDER BY created_at DESC' : 'SELECT * FROM volunteers ORDER BY created_at DESC');
$filter ? $stmt->execute([$filter]) : $stmt->execute();
$rows = $stmt->fetchAll();
$counts = $pdo->query('SELECT status, COUNT(*) FROM volunteers GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div><h2 class="mb-1">Volunteer applications</h2><p class="text-secondary mb-0">Everyone who offered their time.</p></div>
  <div class="d-flex flex-wrap gap-2">
    <?php foreach (['new', 'contacted', 'joined'] as $s): ?>
      <span class="admin-stat px-3 py-2"><span><?= e(ucfirst($s)) ?></span><h4 class="mb-0"><?= (int) ($counts[$s] ?? 0) ?></h4></span>
    <?php endforeach; ?>
  </div>
</div>

<div class="admin-card">
  <ul class="nav nav-pills mb-3">
    <?php foreach (['' => 'All', 'new' => 'New', 'contacted' => 'Contacted', 'joined' => 'Joined', 'declined' => 'Declined'] as $key => $label): ?>
      <li class="nav-item">
        <a class="nav-link btn-sm <?= $filter === $key ? 'active' : '' ?>" style="background:<?= $filter === $key ? 'var(--ys-brown)' : 'transparent' ?>;color:<?= $filter === $key ? '#fff' : 'var(--ys-brown)' ?>"
           href="<?= e(url('admin/volunteers.php' . ($key ? '?status=' . $key : ''))) ?>"><?= e($label) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if ($rows): ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Name</th><th>Interest</th><th>Availability</th><th>Message</th><th>Status</th><th>Applied</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $v): ?>
            <tr>
              <td><strong><?= e($v['name']) ?></strong><br><small class="text-secondary"><?= e($v['email']) ?><br><?= e($v['phone']) ?></small></td>
              <td><small><?= e($v['interest']) ?></small></td>
              <td><small><?= e($v['availability'] ?: '—') ?></small></td>
              <td><small><?= e(excerpt($v['message'] ?? '', 60) ?: '—') ?></small></td>
              <td><span class="status-pill status-<?= e($v['status']) ?>"><?= e(ucfirst($v['status'])) ?></span></td>
              <td><small><?= e(date('M j, Y', strtotime($v['created_at']))) ?></small></td>
              <td class="text-nowrap">
                <form method="post" action="<?= e(url('admin/volunteers.php')) ?>" class="d-flex gap-1">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="do" value="status"><input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                  <select name="status" class="form-select form-select-sm" style="width:auto">
                    <?php foreach (['new', 'contacted', 'joined', 'declined'] as $s): ?>
                      <option <?= $v['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-outline-primary" title="Update"><i class="bi bi-check"></i></button>
                </form>
                <form method="post" action="<?= e(url('admin/volunteers.php')) ?>" class="d-inline mt-1" data-confirm="Delete this application?">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-secondary mb-0">No applications found.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
