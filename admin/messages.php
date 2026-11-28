<?php
$adminActive = 'messages.php';
$pageTitle   = 'Messages';
// Authenticate before any output so POST handlers can redirect cleanly.
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'toggle') {
        $pdo->prepare('UPDATE messages SET is_read = 1 - is_read WHERE id = ?')->execute([$id]);
        flash('success', 'Message status updated.');
    } elseif (($_POST['do'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash('success', 'Message deleted.');
    }
    redirect('admin/messages.php' . (isset($_GET['view']) ? '?view=' . (int) $_GET['view'] : ''));
}

// Layout only after POST handling, so redirects send no half-rendered page.
require_once __DIR__ . '/includes/header.php';

$view = (int) ($_GET['view'] ?? 0);
if ($view) {
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$view]);
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE id = ?');
    $stmt->execute([$view]);
    $message = $stmt->fetch();
} else {
    $message = null;
}

$filter = $_GET['filter'] ?? 'all';
$sql = $filter === 'unread' ? 'SELECT * FROM messages WHERE is_read=0 ORDER BY created_at DESC' : 'SELECT * FROM messages ORDER BY created_at DESC';
$rows = $pdo->query($sql)->fetchAll();
$unread = (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read=0')->fetchColumn();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div><h2 class="mb-1">Messages</h2><p class="text-secondary mb-0"><?= $unread ?> unread of <?= count($rows) ?> total.</p></div>
  <ul class="nav nav-pills">
    <li class="nav-item"><a class="nav-link btn-sm <?= $filter === 'all' ? 'active' : '' ?>" style="background:<?= $filter === 'all' ? 'var(--ys-brown)' : 'transparent' ?>;color:<?= $filter === 'all' ? '#fff' : 'var(--ys-brown)' ?>" href="<?= e(url('admin/messages.php')) ?>">All</a></li>
    <li class="nav-item"><a class="nav-link btn-sm <?= $filter === 'unread' ? 'active' : '' ?>" style="background:<?= $filter === 'unread' ? 'var(--ys-brown)' : 'transparent' ?>;color:<?= $filter === 'unread' ? '#fff' : 'var(--ys-brown)' ?>" href="<?= e(url('admin/messages.php?filter=unread')) ?>">Unread (<?= $unread ?>)</a></li>
  </ul>
</div>

<?php if ($message): ?>
  <div class="admin-card">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
      <div>
        <h4 class="mb-1"><?= e($message['subject']) ?></h4>
        <p class="text-secondary small mb-0">From <strong><?= e($message['name']) ?></strong> &lt;<?= e($message['email']) ?>&gt; • <?= e(date('M j, Y g:i a', strtotime($message['created_at']))) ?></p>
      </div>
      <div class="d-flex gap-2">
        <a href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e(rawurlencode($message['subject'])) ?>" class="btn btn-sm btn-ys"><i class="bi bi-reply me-1"></i> Reply by email</a>
        <a href="<?= e(url('admin/messages.php')) ?>" class="btn btn-sm btn-outline-secondary">Close</a>
      </div>
    </div>
    <div class="p-3 bg-light rounded-3" style="white-space:pre-wrap"><?= e($message['message']) ?></div>
  </div>
<?php endif; ?>

<div class="admin-card">
  <?php if ($rows): ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Status</th><th>Name</th><th>Subject</th><th>Preview</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $m): ?>
            <tr class="<?= $m['is_read'] ? '' : 'fw-bold' ?>">
              <td><span class="status-pill status-<?= $m['is_read'] ? 'read' : 'unread' ?>"><?= $m['is_read'] ? 'Read' : 'New' ?></span></td>
              <td><?= e($m['name']) ?><br><small class="text-secondary"><?= e($m['email']) ?></small></td>
              <td><a href="<?= e(url('admin/messages.php?view=' . $m['id'])) ?>"><?= e($m['subject']) ?></a></td>
              <td><small><?= e(excerpt($m['message'], 65)) ?></small></td>
              <td><small><?= e(time_ago($m['created_at'])) ?></small></td>
              <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/messages.php?view=' . $m['id'])) ?>" title="Read"><i class="bi bi-eye"></i></a>
                <form method="post" action="<?= e(url('admin/messages.php' . ($view ? '?view=' . $view : ''))) ?>" class="d-inline">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                  <button class="btn btn-sm btn-outline-secondary" title="Mark read/unread"><i class="bi <?= $m['is_read'] ? 'bi-envelope' : 'bi-envelope-open' ?>"></i></button>
                </form>
                <form method="post" action="<?= e(url('admin/messages.php')) ?>" class="d-inline" data-confirm="Delete this message?">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-secondary mb-0">No messages found.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
