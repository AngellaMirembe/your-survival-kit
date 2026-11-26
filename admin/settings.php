<?php
$adminActive = 'settings.php';
$pageTitle   = 'Settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/mail.php';

$contactKeys = [
    'site_name', 'tagline', 'site_email', 'support_email', 'site_phone', 'site_address',
    'office_hours', 'whatsapp_number', 'whatsapp_channel', 'mobile_money_number',
    'mobile_money_name', 'facebook', 'instagram', 'twitter', 'tiktok',
    'about_short', 'footer_note', 'donation_note',
];
$smtpKeys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure'];
$editableKeys = array_merge($contactKeys, $smtpKeys);

$labels = [
    'site_phone'           => 'Phone (optional)',
    'site_address'         => 'Where we work (no public office yet)',
    'office_hours'         => 'Team availability (not office hours)',
    'whatsapp_number'      => 'WhatsApp number for direct chat',
    'whatsapp_channel'     => 'WhatsApp channel name',
    'mobile_money_number'  => 'Mobile Money number shown to donors',
    'mobile_money_name'    => 'Mobile Money account name',
    'facebook'             => 'Facebook URL (optional)',
    'instagram'            => 'Instagram URL (optional)',
    'twitter'              => 'X / Twitter URL (optional)',
    'tiktok'               => 'TikTok URL (optional)',
    'smtp_host'            => 'SMTP host',
    'smtp_port'            => 'SMTP port',
    'smtp_user'            => 'SMTP username (full email)',
    'smtp_pass'            => 'SMTP app password',
    'smtp_secure'          => 'Encryption',
];

/* ---------------- Site settings ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';

    if ($do === 'save_settings') {
        foreach ($editableKeys as $key) {
            if (!array_key_exists($key, $_POST['setting'] ?? [])) continue;
            $value = trim($_POST['setting'][$key]);
            // A blank password means "keep the one already saved".
            if ($key === 'smtp_pass' && $value === '') continue;
            $pdo->prepare(
                'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            )->execute([$key, $value]);
        }
        flash('success', 'Settings saved. Refresh the public site to see text changes.');
    }

    elseif ($do === 'save_stat') {
        $id     = (int) ($_POST['stat_id'] ?? 0);
        $label  = trim($_POST['label'] ?? '');
        $value  = (int) ($_POST['value'] ?? 0);
        $suffix = trim($_POST['suffix'] ?? '');
        $icon   = trim($_POST['icon'] ?? 'bi-heart-fill');
        $order  = (int) ($_POST['sort_order'] ?? 0);
        if ($label === '') {
            flash('error', 'A stat label is required.');
        } elseif ($id) {
            $pdo->prepare('UPDATE stats SET label=?, value=?, suffix=?, icon=?, sort_order=? WHERE id=?')
                ->execute([$label, $value, $suffix, $icon, $order, $id]);
            flash('success', 'Stat updated.');
        } else {
            $pdo->prepare('INSERT INTO stats (label, value, suffix, icon, sort_order) VALUES (?,?,?,?,?)')
                ->execute([$label, $value, $suffix, $icon, $order]);
            flash('success', 'Stat added.');
        }
    }

    elseif ($do === 'delete_stat') {
        $pdo->prepare('DELETE FROM stats WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        flash('success', 'Stat deleted.');
    }

    elseif ($do === 'test_email') {
        $to = trim($_POST['test_email'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid email address to test.');
        } else {
            $html = '<h2 style="color:#a96538">Email delivery is working</h2>'
                  . '<p>This test was sent from ' . e(setting('site_name', SITE_NAME))
                  . ' using the SMTP settings saved in Admin → Settings.</p>';
            $ok = send_site_email($to, 'Test email — ' . setting('site_name', SITE_NAME), $html);
            flash($ok ? 'success' : 'error', $ok
                ? 'Test email sent to ' . $to . '. Check your inbox (and spam folder).'
                : 'Could not send the test email. Check the SMTP host, port and App Password, then try again.');
        }
    }

    elseif ($do === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $hash = $stmt->fetchColumn();
        if (!password_verify($current, $hash)) {
            flash('error', 'Your current password is incorrect.');
        } elseif (strlen($new) < 10 || $new !== $confirm) {
            flash('error', 'New password must be at least 10 characters and match the confirmation.');
        } else {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            flash('success', 'Password changed. Please sign in again.');
            redirect('admin/logout.php');
        }
    }

    redirect('admin/settings.php');
}

$stats = $pdo->query('SELECT * FROM stats ORDER BY sort_order')->fetchAll();
?>

<div class="mb-4"><h2 class="mb-1">Site settings</h2><p class="text-secondary mb-0">Change text, contact details and email delivery for the public website.</p></div>

<div class="row g-4">
  <div class="col-lg-7">
    <form method="post" action="<?= e(url('admin/settings.php')) ?>" class="admin-card">
      <?php csrf_field(); ?>
      <input type="hidden" name="do" value="save_settings">
      <h4 class="mb-3">Contact, WhatsApp &amp; Mobile Money details</h4>
      <div class="row g-3">
        <?php foreach ($contactKeys as $key): ?>
          <div class="col-md-6 <?= in_array($key, ['tagline','about_short','footer_note','donation_note'], true) ? 'col-12' : '' ?>">
            <label class="form-label" for="s_<?= e($key) ?>"><?= e($labels[$key] ?? ucwords(str_replace('_', ' ', $key))) ?></label>
            <?php if (in_array($key, ['tagline', 'about_short', 'footer_note', 'donation_note'], true)): ?>
              <textarea class="form-control form-control-sm" id="s_<?= e($key) ?>" name="setting[<?= e($key) ?>]" rows="2"><?= e($settings[$key] ?? '') ?></textarea>
            <?php else: ?>
              <input class="form-control form-control-sm" id="s_<?= e($key) ?>" name="setting[<?= e($key) ?>]" value="<?= e($settings[$key] ?? '') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <hr class="my-4">
      <h4 class="mb-1">Email delivery (SMTP)</h4>
      <p class="small text-secondary mb-3">
        Enter these and confirmation/event emails will be sent for real, including on localhost.
        For Gmail use <code>smtp.gmail.com</code>, port <code>587</code>, encryption <code>TLS</code>,
        your full Gmail address as username, and a <strong>Gmail App Password</strong> (not your normal login password).
      </p>
      <div class="row g-3">
        <?php foreach ($smtpKeys as $key): ?>
          <div class="col-md-6">
            <label class="form-label" for="s_<?= e($key) ?>"><?= e($labels[$key]) ?></label>
            <?php if ($key === 'smtp_secure'): ?>
              <select class="form-select form-select-sm" id="s_<?= e($key) ?>" name="setting[<?= e($key) ?>]">
                <?php $current = strtolower((string) ($settings[$key] ?? 'tls')); ?>
                <option value="tls"  <?= $current === 'tls' ? 'selected' : '' ?>>TLS (port 587)</option>
                <option value="ssl"  <?= $current === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
                <option value="none" <?= $current === 'none' ? 'selected' : '' ?>>None (local relay)</option>
              </select>
            <?php elseif ($key === 'smtp_pass'): ?>
              <input type="password" class="form-control form-control-sm" id="s_<?= e($key) ?>" name="setting[<?= e($key) ?>]" value="<?= e($settings[$key] ?? '') ?>" autocomplete="new-password" placeholder="<?= ($settings[$key] ?? '') !== '' ? 'Saved — leave blank to keep' : 'App password' ?>">
            <?php else: ?>
              <input class="form-control form-control-sm" id="s_<?= e($key) ?>" name="setting[<?= e($key) ?>]" value="<?= e($settings[$key] ?? '') ?>" <?= $key === 'smtp_port' ? 'type="number" min="1" max="65535"' : '' ?>>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="small text-secondary mt-3 mb-0">
        Status: <strong><?= mail_smtp_configured() ? 'SMTP configured — emails will be sent' : 'Not configured — no emails will leave this computer' ?></strong>.
        The password is stored only in this site's database, never shown on the public site.
      </p>
      <button class="btn btn-ys mt-4" type="submit"><i class="bi bi-check2 me-1"></i> Save settings</button>
    </form>

    <form method="post" action="<?= e(url('admin/settings.php')) ?>" class="admin-card mt-4">
      <?php csrf_field(); ?>
      <input type="hidden" name="do" value="test_email">
      <h4 class="mb-1">Send a test email</h4>
      <p class="small text-secondary mb-3">Save the SMTP settings first, then send a message to yourself to confirm delivery.</p>
      <div class="d-flex flex-wrap gap-2 align-items-end">
        <div class="flex-grow-1" style="min-width:180px">
          <label class="form-label" for="test_email">Send to</label>
          <input type="email" class="form-control form-control-sm" id="test_email" name="test_email" value="<?= e(setting('site_email', '')) ?>" required>
        </div>
        <button class="btn btn-outline-ys btn-sm" type="submit"><i class="bi bi-send me-1"></i> Send test</button>
      </div>
    </form>
  </div>

  <div class="col-lg-5">
    <form method="post" action="<?= e(url('admin/settings.php')) ?>" class="admin-card">
      <?php csrf_field(); ?>
      <input type="hidden" name="do" value="password">
      <h4 class="mb-1">Change password</h4>
      <p class="text-secondary small mb-3">Use a strong, unique password of at least 10 characters.</p>
      <div class="mb-2">
        <label class="form-label" for="current_password">Current password</label>
        <input type="password" class="form-control form-control-sm" id="current_password" name="current_password" required>
      </div>
      <div class="mb-2">
        <label class="form-label" for="new_password">New password</label>
        <input type="password" class="form-control form-control-sm" id="new_password" name="new_password" minlength="10" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="confirm_password">Confirm new password</label>
        <input type="password" class="form-control form-control-sm" id="confirm_password" name="confirm_password" minlength="10" required>
      </div>
      <button class="btn btn-outline-ys btn-sm" type="submit">Update password</button>
    </form>

    <div class="admin-card">
      <h4 class="mb-1">Database credentials</h4>
      <p class="text-secondary small mb-2">Edit <code>includes/config.php</code> to match your cPanel database user.</p>
      <ul class="small text-secondary mb-0">
        <li>Database: <strong>your_survival_kit</strong></li>
        <li>Charset: <strong>utf8mb4</strong></li>
        <li>Queries: <strong>PDO prepared statements</strong></li>
      </ul>
    </div>

    <div class="admin-card">
      <h4 class="mb-1">Photo consent</h4>
      <p class="small text-secondary mb-0">Before publishing an image of a child, confirm that the child and their guardian are happy for it to appear online. Remove any image immediately if asked.</p>
    </div>

    <div class="admin-card">
      <h4 class="mb-1">Payments</h4>
      <p class="small text-secondary mb-0">Mobile Money only. Donors complete payment on their own phone. Never collect or store a PIN on this website.</p>
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="mb-0">Impact numbers on the home page</h4>
    <button class="btn btn-outline-ys btn-sm" data-bs-toggle="collapse" data-bs-target="#statForm"><i class="bi bi-plus-lg me-1"></i> Add stat</button>
  </div>

  <div class="collapse mb-3" id="statForm">
    <form method="post" action="<?= e(url('admin/settings.php')) ?>" class="border rounded-3 p-3 bg-light">
      <?php csrf_field(); ?>
      <input type="hidden" name="do" value="save_stat"><input type="hidden" name="stat_id" value="0">
      <div class="row g-2">
        <div class="col-md-4"><label class="form-label small">Label</label><input class="form-control form-control-sm" name="label" required></div>
        <div class="col-md-2"><label class="form-label small">Value</label><input type="number" class="form-control form-control-sm" name="value" value="0"></div>
        <div class="col-md-2"><label class="form-label small">Suffix</label><input class="form-control form-control-sm" name="suffix" placeholder="+"></div>
        <div class="col-md-2"><label class="form-label small">Icon class</label><input class="form-control form-control-sm" name="icon" value="bi-heart-fill"></div>
        <div class="col-md-2"><label class="form-label small">Order</label><input type="number" class="form-control form-control-sm" name="sort_order" value="5"></div>
      </div>
      <button class="btn btn-ys btn-sm mt-3" type="submit">Add stat</button>
    </form>
  </div>

  <?php foreach ($stats as $s): ?>
    <div class="d-flex flex-wrap gap-3 align-items-end border-bottom pb-3 mb-3">
      <form method="post" action="<?= e(url('admin/settings.php')) ?>" class="row g-2 align-items-end flex-grow-1">
        <?php csrf_field(); ?>
        <input type="hidden" name="do" value="save_stat"><input type="hidden" name="stat_id" value="<?= (int) $s['id'] ?>">
        <div class="col-md-3 col-6"><label class="form-label small mb-1">Label</label><input class="form-control form-control-sm" name="label" value="<?= e($s['label']) ?>" required></div>
        <div class="col-md-2 col-6"><label class="form-label small mb-1">Value</label><input type="number" class="form-control form-control-sm" name="value" value="<?= (int) $s['value'] ?>"></div>
        <div class="col-md-1 col-6"><label class="form-label small mb-1">Suffix</label><input class="form-control form-control-sm" name="suffix" value="<?= e($s['suffix']) ?>"></div>
        <div class="col-md-2 col-6"><label class="form-label small mb-1">Icon class</label><input class="form-control form-control-sm" name="icon" value="<?= e($s['icon']) ?>"></div>
        <div class="col-md-2 col-6"><label class="form-label small mb-1">Order</label><input type="number" class="form-control form-control-sm" name="sort_order" value="<?= (int) $s['sort_order'] ?>"></div>
        <div class="col-md-2 col-12"><button class="btn btn-sm btn-outline-primary w-100" title="Save"><i class="bi bi-check me-1"></i>Save</button></div>
      </form>
      <form method="post" action="<?= e(url('admin/settings.php')) ?>" data-confirm="Delete this stat?">
        <?php csrf_field(); ?><input type="hidden" name="do" value="delete_stat"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
