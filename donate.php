<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Support a Kit';
$pageDescription = 'Send Mobile Money to Your Survival Kit in Uganda. No online payment or PIN is collected on this website.';
$activePage      = 'donate';
$ogImage         = asset('img/outreach/outreach-21.jpg');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name    = trim($_POST['donor_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phoneRaw = trim($_POST['phone'] ?? '');
    $choice  = $_POST['amount'] ?? '';
    $custom  = trim($_POST['custom_amount'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $amount = ($choice === 'custom') ? (float) $custom : (float) $choice;
    $phone  = ug_phone($phoneRaw);

    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!$phone) $errors[] = 'Please enter a valid Ugandan phone number, e.g. 0790 866761 or +256 790 866761.';
    if ($amount < 1000 || $amount > 5000000) $errors[] = 'Please choose or enter an amount between 1,000 and 5,000,000 UGX.';

    if ($errors) {
        set_old(['donor_name' => $name, 'email' => $email, 'phone' => $phoneRaw, 'message' => $message, 'amount' => $choice]);
        flash('error', implode(' ', $errors));
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO donations (donor_name, email, phone, amount, currency, frequency, method, message, status)
             VALUES (?, ?, ?, ?, ?, "once", "mobile_money", ?, "pending")'
        );
        $stmt->execute([$name, $email, $phone, $amount, CURRENCY, $message ?: null]);

        $_SESSION['donation_receipt'] = [
            'id'     => (int) $pdo->lastInsertId(),
            'name'   => $name,
            'phone'  => $phone,
            'amount' => $amount,
        ];
        clear_old();
        redirect('donate?done=1');
    }
}

$receipt = null;
if (isset($_GET['done']) && !empty($_SESSION['donation_receipt'])) {
    $receipt = $_SESSION['donation_receipt'];
    unset($_SESSION['donation_receipt']);
}

$momoNumber = setting('mobile_money_number', '+256 790 866761');
$momoName   = setting('mobile_money_name', 'Amuron Rachel');
$waNumber   = setting('whatsapp_number', '+256 765 976851');

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Donate</li>
    </ol></nav>
    <span class="eyebrow">Give a kit</span>
    <h1>Your gift becomes something real.</h1>
    <p>Mobile Money only. We are a small volunteer team, so we record your request here and follow it up personally.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">

    <?php if ($receipt): ?>
      <?php
      $confirmText = 'Hello Your Survival Kit. I have sent ' . CURRENCY . ' ' . number_format($receipt['amount'], 0)
          . ' by Mobile Money. Name: ' . $receipt['name'] . '. Phone: ' . $receipt['phone'] . '. Please confirm.';
      ?>
      <div class="card-ys momo-box mb-5">
        <div class="row align-items-center gy-4">
          <div class="col-lg-7">
            <span class="eyebrow">Step 2 of 2</span>
            <h2 class="section-title h3">Send the money on your phone</h2>
            <p class="text-secondary">Thank you, <?= e($receipt['name']) ?>. Your request for <strong><?= e(CURRENCY) ?> <?= e(number_format($receipt['amount'], 0)) ?></strong> is recorded.</p>
            <div class="momo-number">
              <small class="d-block text-secondary mb-1">Send Mobile Money to</small>
              <strong id="momoNumber"><?= e($momoNumber) ?></strong>
              <small class="d-block text-secondary mt-1">Account name: <?= e($momoName) ?></small>
            </div>
            <button type="button" class="btn btn-outline-ys btn-sm mt-3" onclick="copyMomo()"><i class="bi bi-copy me-1"></i> Copy number</button>
            <span id="copyStatus" class="small text-green ms-2" role="status" aria-live="polite"></span>
          </div>
          <div class="col-lg-5">
            <div class="step-box">
              <h5 class="mb-3"><i class="bi bi-phone me-2 text-brown"></i> How to pay</h5>
              <ol class="small mb-3">
                <li class="mb-2">Open your Mobile Money menu on your phone.</li>
                <li class="mb-2">Send <strong><?= e(CURRENCY) ?> <?= e(number_format($receipt['amount'], 0)) ?></strong> to <strong><?= e($momoNumber) ?></strong>.</li>
                <li class="mb-2">You will get a prompt on <strong>your own phone</strong>. Enter your PIN there.</li>
                <li>Then tap the WhatsApp button to tell us you have sent it.</li>
              </ol>
              <div class="alert alert-warning small mb-3"><i class="bi bi-shield-lock me-1"></i> We will never ask for your PIN. Enter it only on your phone's official payment prompt.</div>
              <a class="btn btn-success w-100" href="<?= e(whatsapp_link($waNumber, $confirmText)) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i> I have sent it — confirm on WhatsApp</a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="row g-4 g-lg-5">
      <div class="col-lg-7">
        <div class="admin-card" style="border-radius:var(--ys-radius);padding:36px">
          <h3 class="mb-2">Make a donation request</h3>
          <p class="text-secondary small mb-4">
            <i class="bi bi-shield-check text-green me-1"></i>
            <?= e(setting('donation_note', 'Mobile Money only. No payment is taken on this website.')) ?>
          </p>

          <form method="post" action="<?= e(url('donate')) ?>" novalidate>
            <?php csrf_field(); ?>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label" for="donor_name">Full name *</label>
                <input class="form-control" id="donor_name" name="donor_name" value="<?= old('donor_name') ?>" required autocomplete="name">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email address *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
              </div>
              <div class="col-12">
                <label class="form-label" for="phone">Your Ugandan phone number * <span class="text-secondary">(for follow-up)</span></label>
                <input class="form-control" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="e.g. 0790 866761" required autocomplete="tel">
              </div>
            </div>

            <label class="form-label d-block mb-3">Choose an amount (<?= e(CURRENCY) ?>)</label>
            <div class="row g-2 amount-option mb-3">
              <?php
              $tiers = [
                ['10000', 'Notebook & pencil set', 'bi-pencil-square'],
                ['25000', 'A full school kit', 'bi-bag-heart'],
                ['50000', 'Care & clothing items', 'bi-person-bounding-box'],
                ['100000', 'Support an outreach', 'bi-emoji-smile'],
              ];
              foreach ($tiers as $t):
                  $checked = old('amount', '25000') === $t[0] ? 'checked' : ''; ?>
                <div class="col-6 col-md-3">
                  <input type="radio" id="amt<?= $t[0] ?>" name="amount" value="<?= $t[0] ?>" <?= $checked ?>>
                  <label for="amt<?= $t[0] ?>">
                    <i class="bi <?= $t[2] ?> d-block mb-1" style="font-size:1.4rem;color:var(--ys-green-dark)"></i>
                    <strong><?= e(CURRENCY) ?> <?= $t[0] ?></strong>
                    <small><?= e($t[1]) ?></small>
                  </label>
                </div>
              <?php endforeach; ?>
              <div class="col-6 col-md-3">
                <input type="radio" id="amtCustom" name="amount" value="custom" <?= old('amount') === 'custom' ? 'checked' : '' ?>>
                <label for="amtCustom">
                  <i class="bi bi-pencil d-block mb-1" style="font-size:1.4rem;color:var(--ys-green-dark)"></i>
                  <strong>Other</strong><small>Enter your own</small>
                </label>
              </div>
            </div>
            <div class="mb-4" id="customAmountWrap" <?= old('amount') === 'custom' ? '' : 'style="display:none"' ?>>
              <label class="form-label" for="custom_amount">Custom amount (UGX)</label>
              <input type="number" min="1000" step="1000" class="form-control" id="custom_amount" name="custom_amount" value="<?= old('custom_amount') ?>" placeholder="e.g. 35000">
            </div>

            <div class="mb-4">
              <label class="form-label" for="message">A note for the team <span class="text-secondary">(optional)</span></label>
              <textarea class="form-control" id="message" name="message" rows="3" placeholder="Tell us how you would like your gift used…"><?= old('message') ?></textarea>
            </div>

            <button class="btn btn-ys btn-lg w-100" type="submit"><i class="bi bi-heart-fill me-2"></i> Continue to payment details</button>
            <p class="text-secondary small text-center mt-3 mb-0">No online charge is made. You complete payment by Mobile Money on your own phone.</p>
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card-ys mb-4">
          <div class="icon-box green"><i class="bi bi-box-seam"></i></div>
          <h4>Prefer to give items?</h4>
          <p>We always need notebooks, pencils, soap, clothes and sturdy bags. Send a WhatsApp message and we will arrange a handover.</p>
          <a class="btn btn-outline-ys btn-sm" href="<?= e(whatsapp_link($waNumber, 'Hello! I would like to donate items to Your Survival Kit.')) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i> Message the team</a>
        </div>

        <div class="card-ys">
          <h4 class="mb-3">Questions first?</h4>
          <ul class="list-unstyled small mb-3">
            <li class="mb-2"><i class="bi bi-check-circle-fill text-green me-2"></i> Email: <a href="mailto:<?= e(setting('site_email')) ?>"><?= e(setting('site_email')) ?></a></li>
            <li class="mb-2"><i class="bi bi-check-circle-fill text-green me-2"></i> WhatsApp: <?= e($waNumber) ?></li>
            <li class="mb-0"><i class="bi bi-check-circle-fill text-green me-2"></i> Channel: <?= e(setting('whatsapp_channel', 'YOUR SURVIVAL KIT')) ?></li>
          </ul>
          <a class="btn btn-outline-ys btn-sm" href="<?= e(url('contact')) ?>">Contact the team</a>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function copyMomo() {
  var el = document.getElementById('momoNumber');
  if (!el) return;
  var text = el.textContent.trim();
  var status = document.getElementById('copyStatus');
  function done(ok) {
    if (!status) return;
    status.textContent = ok ? 'Copied — paste it in your MoMo app.' : 'Select and copy the number above.';
  }
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(function () { done(true); }, function () { done(false); });
  } else {
    done(false);
  }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
