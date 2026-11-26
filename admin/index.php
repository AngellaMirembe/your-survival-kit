<?php
$adminActive = '';
$pageTitle   = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$counts = [
  'news'        => $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn(),
  'gallery'     => $pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn(),
  'donations'   => $pdo->query('SELECT COUNT(*) FROM donations')->fetchColumn(),
  'raised'      => $pdo->query('SELECT COALESCE(SUM(amount),0) FROM donations WHERE status != "cancelled"')->fetchColumn(),
  'unread'      => $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn(),
  'volunteers'  => $pdo->query('SELECT COUNT(*) FROM volunteers')->fetchColumn(),
  'subscribers' => $pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn(),
  'verified'    => $pdo->query('SELECT COUNT(*) FROM subscribers WHERE verified_at IS NOT NULL AND unsubscribed_at IS NULL')->fetchColumn(),
  'events'      => $pdo->query('SELECT COUNT(*) FROM events WHERE status = "published" AND event_date >= CURDATE()')->fetchColumn(),
];

// JOIN example: news with its author
$recentNews = $pdo->query(
    'SELECT n.id, n.title, n.status, n.published_at, a.name AS author_name
     FROM news n LEFT JOIN admin_users a ON a.id = n.author_id
     ORDER BY n.created_at DESC LIMIT 5'
)->fetchAll();

$recentDonations = $pdo->query('SELECT * FROM donations ORDER BY created_at DESC LIMIT 6')->fetchAll();
$recentMessages  = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 5')->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h2 class="mb-1">Dashboard</h2>
    <p class="text-secondary mb-0">Welcome back, <?= e($admin['name']) ?>. Here is what is happening today.</p>
  </div>
  <a href="<?= e(url()) ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> View live site</a>
</div>

<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Requests received', $counts['donations'], 'bi-cash-stack', ''],
    ['Donation requests', CURRENCY . ' ' . number_format((float) $counts['raised'], 0), 'bi-cash-coin', 'green'],
    ['Stories', $counts['news'], 'bi-newspaper', ''],
    ['Gallery photos', $counts['gallery'], 'bi-images', 'green'],
    ['Unread messages', $counts['unread'], 'bi-envelope', 'gold'],
    ['Volunteers', $counts['volunteers'], 'bi-people', ''],
    ['Confirmed emails', $counts['verified'], 'bi-envelope-check', 'green'],
    ['Upcoming events', $counts['events'], 'bi-calendar-event', ''],
    ['Published stories', $pdo->query('SELECT COUNT(*) FROM news WHERE status="published"')->fetchColumn(), 'bi-check-circle', 'green'],
  ];
  foreach ($cards as $c): ?>
    <div class="col-6 col-lg-3">
      <div class="admin-stat <?= $c[3] ?>">
        <div class="d-flex justify-content-between align-items-start">
          <div><span><?= e($c[0]) ?></span><h3><?= e($c[1]) ?></h3></div>
          <i class="bi <?= $c[2] ?> text-secondary" style="font-size:1.4rem"></i>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent donation requests</h5>
        <a href="<?= e(url('admin/donations.php')) ?>" class="small">View all</a>
      </div>
      <?php if ($recentDonations): ?>
        <div class="table-responsive">
          <table class="admin-table">
            <thead><tr><th>Donor</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($recentDonations as $d): ?>
                <tr>
                  <td><strong><?= e($d['donor_name']) ?></strong><br><small class="text-secondary"><?= e($d['email']) ?></small></td>
                  <td><?= e(CURRENCY) ?> <?= e(number_format((float) $d['amount'], 0)) ?></td>
                  <td><?= e(str_replace('_', ' ', $d['method'])) ?></td>
                  <td><span class="status-pill status-<?= e($d['status']) ?>"><?= e(ucfirst($d['status'])) ?></span></td>
                  <td><small><?= e(time_ago($d['created_at'])) ?></small></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-secondary mb-0">No requests yet. They will appear here as soon as someone starts a donation.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="admin-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Unread messages</h5>
        <a href="<?= e(url('admin/messages.php')) ?>" class="small">View all</a>
      </div>
      <?php if ($recentMessages): ?>
        <div class="list-group list-group-flush">
          <?php foreach ($recentMessages as $m): ?>
            <a href="<?= e(url('admin/messages.php?view=' . $m['id'])) ?>" class="list-group-item px-0 border-0 d-flex gap-3 align-items-start">
              <i class="bi <?= $m['is_read'] ? 'bi-envelope-open' : 'bi-envelope-fill text-brown' ?> mt-1"></i>
              <div class="small">
                <strong><?= e($m['name']) ?></strong> — <?= e($m['subject']) ?>
                <div class="text-secondary"><?= e(excerpt($m['message'], 70)) ?> <span class="ms-1"><?= e(time_ago($m['created_at'])) ?></span></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-secondary mb-0">No messages yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="admin-card">
  <h5 class="mb-3">Latest stories <span class="text-secondary small fw-normal">(author join)</span></h5>
  <div class="table-responsive">
    <table class="admin-table">
      <thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Published</th></tr></thead>
      <tbody>
        <?php foreach ($recentNews as $n): ?>
          <tr>
            <td><a href="<?= e(url('admin/news.php?action=edit&id=' . $n['id'])) ?>"><?= e($n['title']) ?></a></td>
            <td><?= e($n['author_name'] ?? 'Unassigned') ?></td>
            <td><span class="status-pill status-<?= e($n['status']) ?>"><?= e(ucfirst($n['status'])) ?></span></td>
            <td><small><?= e($n['published_at'] ? date('M j, Y', strtotime($n['published_at'])) : '—') ?></small></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
