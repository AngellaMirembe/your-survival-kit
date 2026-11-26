<?php
$adminActive = 'donations.php';
$pageTitle   = 'Donation pledges';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'status') {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            $pdo->prepare('UPDATE donations SET status = ? WHERE id = ?')->execute([$status, $id]);
            flash('success', 'Donation status updated.');
        }
    } elseif (($_POST['do'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM donations WHERE id = ?')->execute([$id]);
        flash('success', 'Donation request deleted.');
    }
    redirect('admin/donations.php');
}

$filter = $_GET['status'] ?? '';
$allowed = ['', 'pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($filter, $allowed, true)) $filter = '';

$where = $filter ? 'WHERE status = ?' : '';
$stmt = $pdo->prepare("SELECT * FROM donations $where ORDER BY created_at DESC");
$filter ? $stmt->execute([$filter]) : $stmt->execute();
$rows = $stmt->fetchAll();

$total = $pdo->query('SELECT COALESCE(SUM(amount),0), COUNT(*) FROM donations')->fetch(PDO::FETCH_NUM);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div><h2 class="mb-1">Donation pledges &amp; requests</h2><p class="text-secondary mb-0">Mobile Money requests only. The donor completes payment on their own phone; confirm receipt here.</p></div>
  <div class="admin-stat px-4 py-2" style="border-left-color:var(--ys-green-dark)">
    <span class="small">Total requested</span><h4 class="mb-0"><?= e(CURRENCY) ?> <?= e(number_format((float) $total[0], 0)) ?> <small class="text-secondary">/ <?= (int) $total[1] ?> requests</small></h4>
  </div>
</div>

<div class="admin-card">
  <ul class="nav nav-pills mb-3">
    <?php foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label): ?>
      <li class="nav-item">
        <a class="nav-link btn-sm <?= $filter === $key ? 'active' : '' ?>" style="background:<?= $filter === $key ? 'var(--ys-brown)' : 'transparent' ?>;color:<?= $filter === $key ? '#fff' : 'var(--ys-brown)' ?>"
           href="<?= e(url('admin/donations.php' . ($key ? '?status=' . $key : ''))) ?>"><?= e($label) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if ($rows): ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Donor</th><th>Amount</th><th>Frequency</th><th>Method</th><th>Note</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $d): ?>
            <tr>
              <td><strong><?= e($d['donor_name']) ?></strong><br><small class="text-secondary"><?= e($d['email']) ?><br><?= e($d['phone']) ?></small></td>
              <td class="fw-bold text-brown"><?= e(CURRENCY) ?> <?= e(number_format((float) $d['amount'], 0)) ?></td>
              <td><?= e(ucfirst($d['frequency'])) ?></td>
              <td><?= e(str_replace('_', ' ', $d['method'])) ?></td>
              <td><small><?= e(excerpt($d['message'] ?? '', 50) ?: '—') ?></small></td>
              <td><span class="status-pill status-<?= e($d['status']) ?>"><?= e(ucfirst($d['status'])) ?></span></td>
              <td><small><?= e(date('M j, Y', strtotime($d['created_at']))) ?></small></td>
              <td class="text-nowrap">
                <form method="post" action="<?= e(url('admin/donations.php')) ?>" class="d-flex gap-1 align-items-center">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="do" value="status"><input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                  <select name="status" class="form-select form-select-sm" style="width:auto">
                    <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
                      <option <?= $d['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-outline-primary" title="Update"><i class="bi bi-check"></i></button>
                </form>
                <form method="post" action="<?= e(url('admin/donations.php')) ?>" class="d-inline mt-1" data-confirm="Delete this request?">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-secondary mb-0">No donation requests found.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
