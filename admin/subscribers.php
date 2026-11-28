<?php
$adminActive = 'subscribers.php';
$pageTitle   = 'Subscribers';
// Authenticate before any output so POST handlers can redirect cleanly.
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';

    if ($do === 'add') {
        $email = trim($_POST['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $verifyToken = bin2hex(random_bytes(32));
            $unsubToken  = bin2hex(random_bytes(32));
            try {
                $pdo->prepare('INSERT INTO subscribers (email, verify_token, unsubscribe_token) VALUES (?, ?, ?)')
                    ->execute([$email, $verifyToken, $unsubToken]);
                $link = url('newsletter.php?verify=' . rawurlencode($verifyToken));
                $html = '<h2 style="color:#a96538">Confirm your email</h2><p>Please confirm your address to receive updates.</p>'
                      . '<p><a href="' . e($link) . '">Confirm my email</a></p>';
                send_site_email($email, 'Confirm your email — ' . setting('site_name', SITE_NAME), $html);
                flash('success', mail_smtp_configured()
                    ? 'Subscriber added and confirmation email sent.'
                    : 'Subscriber added.');
            } catch (PDOException $e) {
                flash('warning', 'That email is already subscribed.');
            }
        } else {
            flash('error', 'Enter a valid email address.');
        }
    } elseif ($do === 'verify') {
        $pdo->prepare('UPDATE subscribers SET verified_at = NOW(), verify_token = NULL WHERE id = ?')
            ->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Subscriber marked as verified.');
    } elseif ($do === 'resend') {
        $id = (int) ($_POST['id'] ?? 0);
        $row = $pdo->prepare('SELECT * FROM subscribers WHERE id = ?');
        $row->execute([$id]);
        if ($s = $row->fetch()) {
            $token = bin2hex(random_bytes(32));
            $pdo->prepare('UPDATE subscribers SET verify_token = ? WHERE id = ?')->execute([$token, $id]);
            $link = url('newsletter.php?verify=' . rawurlencode($token));
            $html = '<h2 style="color:#a96538">Confirm your email</h2><p><a href="' . e($link) . '">Confirm my email</a></p>';
            send_site_email($s['email'], 'Confirm your email — ' . setting('site_name', SITE_NAME), $html);
            flash('success', mail_smtp_configured()
                ? 'Confirmation email sent again.'
                : 'Confirmation link updated.');
        }
    } else {
        $pdo->prepare('DELETE FROM subscribers WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Subscriber removed.');
    }
    redirect('admin/subscribers.php');
}

// Layout only after POST handling, so redirects send no half-rendered page.
require_once __DIR__ . '/includes/header.php';

$rows = $pdo->query('SELECT * FROM subscribers ORDER BY created_at DESC')->fetchAll();
$verified = 0;
foreach ($rows as $r) {
    if ($r['verified_at'] && !$r['unsubscribed_at']) $verified++;
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div><h2 class="mb-1">Newsletter subscribers</h2><p class="text-secondary mb-0"><?= count($rows) ?> signed up · <?= $verified ?> confirmed for event updates.</p></div>
  <form method="post" action="<?= e(url('admin/subscribers.php')) ?>" class="d-flex flex-wrap gap-2">
    <?php csrf_field(); ?>
    <input type="email" name="email" class="form-control form-control-sm flex-grow-1" style="min-width:180px" placeholder="Add an email" required>
    <button class="btn btn-ys btn-sm" type="submit" name="do" value="add">Add</button>
  </form>
</div>

<div class="admin-card">
  <?php if ($rows): ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead><tr><th>Email</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $s): ?>
            <?php
            if ($s['unsubscribed_at']) { $status = 'Unsubscribed'; $class = 'status-draft'; }
            elseif ($s['verified_at']) { $status = 'Verified'; $class = 'status-published'; }
            else { $status = 'Awaiting confirmation'; $class = 'status-pending'; }
            ?>
            <tr>
              <td><?= e($s['email']) ?></td>
              <td><span class="status-pill <?= $class ?>"><?= e($status) ?></span></td>
              <td><small><?= e(date('M j, Y', strtotime($s['created_at']))) ?></small></td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  <?php if (!$s['verified_at'] && $s['verify_token']): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('newsletter.php?verify=' . rawurlencode($s['verify_token']))) ?>">Verify link</a>
                    <form method="post" action="<?= e(url('admin/subscribers.php')) ?>">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                      <button class="btn btn-sm btn-outline-secondary" name="do" value="resend">Resend</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" action="<?= e(url('admin/subscribers.php')) ?>" data-confirm="Remove this subscriber?">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" name="do" value="delete"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-secondary mb-0">No subscribers yet. They appear here when people join from the website footer.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
