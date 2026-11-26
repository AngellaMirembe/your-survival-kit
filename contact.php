<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Contact Us';
$pageDescription = 'Get in touch with our small youth-led team in Uganda — ask a question, arrange a donation drop-off or partner with us.';
$activePage      = 'contact';
$ogImage         = asset('img/outreach/outreach-20.jpg');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General enquiry');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($message) < 10) $errors[] = 'Please write a message of at least 10 characters.';

    if ($errors) {
        set_old(compact('name', 'email', 'subject', 'message'));
        flash('error', implode(' ', $errors));
    } else {
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $subject, $message]);
        clear_old();
        flash('success', 'Message received. Thank you — we usually reply within two working days.');
        redirect('contact');
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Contact</li>
    </ol></nav>
    <span class="eyebrow">Say hello</span>
    <h1>We would love to hear from you.</h1>
    <p>Questions, ideas, partnership offers or a box of notebooks to drop off — start here.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-7">
        <div class="admin-card" style="border-radius:var(--ys-radius);padding:36px">
          <h3 class="mb-2">Send us a message</h3>
          <p class="text-secondary small mb-4">Fill in the form and our team will get back to you.</p>

          <form method="post" action="<?= e(url('contact')) ?>" novalidate>
            <?php csrf_field(); ?>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="name">Your name *</label>
                <input class="form-control" id="name" name="name" value="<?= old('name') ?>" required autocomplete="name">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email address *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
              </div>
              <div class="col-12">
                <label class="form-label" for="subject">Subject</label>
                <select class="form-select" id="subject" name="subject">
                  <?php
                  $subjects = ['General enquiry', 'Donating items', 'Volunteering', 'Partnership', 'Press & media', 'Other'];
                  foreach ($subjects as $s): ?>
                    <option <?= old('subject') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="message">Message *</label>
                <textarea class="form-control" id="message" name="message" rows="6" required placeholder="How can we help?"><?= old('message') ?></textarea>
              </div>
            </div>
            <button class="btn btn-ys btn-lg" type="submit"><i class="bi bi-send me-2"></i> Send message</button>
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card-ys mb-4">
          <h4 class="mb-4">Contact details</h4>
          <ul class="list-unstyled footer-contact mb-0">
            <?php if ($address = setting('site_address')): ?>
              <li><i class="bi bi-geo-alt text-brown"></i><span><strong>Where we work</strong><br><?= e($address) ?></span></li>
            <?php endif; ?>
            <?php if ($email = setting('site_email')): ?>
              <li><i class="bi bi-envelope text-brown"></i><span><strong>Email</strong><br><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></span></li>
            <?php endif; ?>
            <?php if ($phone = setting('site_phone')): ?>
              <li><i class="bi bi-telephone text-brown"></i><span><strong>Phone</strong><br><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', $phone)) ?>"><?= e($phone) ?></a></span></li>
            <?php endif; ?>
            <?php if (setting('whatsapp_number')): ?>
              <li><i class="bi bi-whatsapp text-brown"></i><span>
                <strong>WhatsApp — talk to the CEO directly</strong><br>
                <a href="<?= e(whatsapp_link(null, 'Hello Shanis Pretty, I would like to speak with you.')) ?>" target="_blank" rel="noopener"><?= e(setting('whatsapp_number')) ?></a>
                <?php if ($channel = setting('whatsapp_channel')): ?><br><small class="text-secondary">Channel: <?= e($channel) ?></small><?php endif; ?>
              </span></li>
            <?php endif; ?>
            <?php if ($hours = setting('office_hours')): ?>
              <li><i class="bi bi-clock text-brown"></i><span><strong>Team availability</strong><br><?= e($hours) ?></span></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="card-ys mb-4">
          <div class="d-flex gap-3 align-items-start">
            <i class="bi bi-geo-alt fs-3 text-brown"></i>
            <div>
              <h5 class="mb-1">We work on the ground in Uganda</h5>
              <p class="small text-secondary mb-0">We do not have a public office. Collection and delivery happen at schools, community spaces and family homes. Send a message and we will arrange a time and place that works.</p>
            </div>
          </div>
        </div>

        <div class="accordion" id="faqAccordion">
          <h4 class="mb-3">Frequently asked</h4>
          <?php
          $faqs = [
            ['How do I donate items?', 'Send us a message with what you have. We will arrange a handover or agree a collection point.'],
            ['How does Mobile Money giving work?', 'Choose an amount, enter your number, then send the money on your own phone. Enter your PIN only on your phone’s official prompt — we never ask for it.'],
            ['Can my school host a drive?', 'Absolutely. We can bring a short wish list and help you sort what comes in.'],
            ['Do you share photos of children?', 'Only with permission from the child and their guardian. Ask us to remove an image and we will act promptly.'],
          ];
          foreach ($faqs as $i => $f): ?>
            <div class="accordion-item border-0 mb-2 rounded-3 overflow-hidden">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                  <?= e($f[0]) ?>
                </button>
              </h2>
              <div id="faq<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body small text-secondary"><?= e($f[1]) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
