<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Get Involved';
$pageDescription = 'Volunteer with Your Survival Kit, partner with us, or help gather supplies for children in need.';
$activePage      = 'volunteer';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $interest    = trim($_POST['interest'] ?? 'General help');
    $availability = trim($_POST['availability'] ?? '');
    $message     = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if ($errors) {
        set_old(compact('name', 'email', 'phone', 'interest', 'availability', 'message'));
        flash('error', implode(' ', $errors));
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO volunteers (name, email, phone, interest, availability, message) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $phone ?: null, $interest, $availability ?: null, $message ?: null]);
        clear_old();
        flash('success', 'Thank you for offering your time! We will be in touch soon with next steps.');
        redirect('volunteer');
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="ys-container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Get Involved</li>
    </ol></nav>
    <span class="eyebrow">Get involved</span>
    <h1>There is room for you here.</h1>
    <p>You do not need money to make a difference. Time, attention and a little organisation go a long way.</p>
  </div>
</section>

<section class="section">
  <div class="ys-container">
    <div class="row g-4 mb-5">
      <?php
      $ways = [
        ['bi-hand-thumbs-up', 'Give your time', 'Help sort donations, pack kits or visit a partner school. We train you in a single afternoon.'],
        ['bi-building', 'Partner with us', 'Schools, businesses and churches can host a collection drive or sponsor a programme.'],
        ['bi-box-seam', 'Collect items', 'Run a drive in your class, host or neighbourhood. We give you a clear list and a collection log.'],
        ['bi-megaphone', 'Spread the word', 'Share our stories, bring a friend, or write about what you notice in your own community.'],
      ];
      foreach ($ways as $w): ?>
        <div class="col-sm-6 col-lg-3">
          <div class="card-ys h-100">
            <div class="icon-box"><i class="bi <?= $w[0] ?>"></i></div>
            <h4><?= e($w[1]) ?></h4>
            <p><?= e($w[2]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="row g-4 g-lg-5 align-items-start">
      <div class="col-lg-5">
        <div class="feature-img mb-4"><img src="<?= e(asset('img/volunteer.jpg')) ?>" alt="Volunteers packing kits"></div>
        <h3>What happens after you sign up?</h3>
        <ol class="list-unstyled mt-3">
          <?php
          $steps = [
            'We read your application and match your interests to an active team.',
            'A volunteer lead calls or emails you to say hello and answer questions.',
            'You join a short induction — usually one afternoon, no experience needed.',
            'You show up, help a child, and tell us what you learned.',
          ];
          foreach ($steps as $i => $s): ?>
            <li class="d-flex gap-3 mb-3"><span class="badge-ys rounded-circle" style="width:30px;height:30px;display:grid;place-items:center"><?= $i + 1 ?></span><span class="text-secondary small"><?= e($s) ?></span></li>
          <?php endforeach; ?>
        </ol>
      </div>

      <div class="col-lg-7">
        <div class="admin-card" style="border-radius:var(--ys-radius);padding:36px">
          <h3 class="mb-2">Volunteer application</h3>
          <p class="text-secondary small mb-4">Tell us a little about yourself. It takes two minutes.</p>

          <form method="post" action="<?= e(url('volunteer')) ?>" novalidate>
            <?php csrf_field(); ?>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="name">Full name *</label>
                <input class="form-control" id="name" name="name" value="<?= old('name') ?>" required autocomplete="name">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email address *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="phone">Phone number</label>
                <input class="form-control" id="phone" name="phone" value="<?= old('phone') ?>" autocomplete="tel">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="interest">I am interested in…</label>
                <select class="form-select" id="interest" name="interest">
                  <?php
                  $interests = ['General help', 'Sorting & packing kits', 'School visits & mentorship', 'Collection drives', 'Photography & social media', 'Finance & administration', 'Partner coordination'];
                  foreach ($interests as $opt): ?>
                    <option <?= old('interest') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="availability">When are you usually available?</label>
                <input class="form-control" id="availability" name="availability" value="<?= old('availability') ?>" placeholder="e.g. Saturday mornings, weekday evenings">
              </div>
              <div class="col-12">
                <label class="form-label" for="message">Anything else we should know?</label>
                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Skills, experience, or why you want to help…"><?= old('message') ?></textarea>
              </div>
            </div>
            <button class="btn btn-ys btn-lg w-100" type="submit"><i class="bi bi-people me-2"></i> Send my application</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
