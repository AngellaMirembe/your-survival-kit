<?php
$adminActive = 'content.php';
$pageTitle   = 'Page content';
// Authenticate before any output so POST handlers can redirect cleanly.
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();

$tabs = [
    'home'      => 'Home',
    'about'     => 'About',
    'what'      => 'What we do',
    'volunteer' => 'Volunteer',
    'events'    => 'Events',
];
$tab = $_GET['tab'] ?? 'home';
if (!isset($tabs[$tab])) $tab = 'home';

$simpleFields = [
    'home' => [
        'home_hero_eyebrow' => 'Hero badge',
        'home_hero_title' => 'Hero title (first line)',
        'home_hero_title_accent' => 'Hero title (coloured line)',
        'home_hero_text' => 'Hero paragraph',
        'home_hero_credit' => 'Photo caption',
        'home_how_title' => '“How it works” heading',
        'home_how_text' => '“How it works” paragraph',
    ],
    'about' => [
        'about_team_eyebrow' => 'Meet the team badge',
        'about_team_title' => 'Meet the team heading',
        'about_team_text' => 'Meet the team paragraph',
        'about_story_paragraph' => 'Our story paragraph',
    ],
    'what' => [
        'what_intro_title' => 'Intro heading',
        'what_intro_text' => 'Intro paragraph',
    ],
    'volunteer' => [
        'volunteer_intro_text' => 'Volunteer intro paragraph',
    ],
    'events' => [
        'events_intro_text' => 'Events intro paragraph',
    ],
];

$imageFields = [
    'home' => [
        'home_hero_images' => 'Hero photos (up to 3)',
    ],
    'about' => [
        'about_team_images' => 'Team photos (up to 6)',
    ],
];

$stepFields = [
    'home' => ['home_how_steps' => '“How it works” steps'],
];

$errors = [];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $do = $_POST['do'] ?? '';

    if ($do === 'saveSection') {
        $saved = 0;
        foreach ($simpleFields[$tab] ?? [] as $key => $label) {
            if (array_key_exists($key, $_POST)) {
                $value = trim((string) $_POST[$key]);
                $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
                    ->execute([$key, $value]);
                $settings[$key] = $value;
                $saved++;
            }
        }
        if (isset($_POST['images']) && is_array($_POST['images'])) {
            foreach ($imageFields[$tab] ?? [] as $key => $label) {
                if (array_key_exists($key, $_POST['images'])) {
                    $raw = is_array($_POST['images'][$key]) ? $_POST['images'][$key] : [];
                    $images = [];
                    foreach ($raw as $img) {
                        $img = basename(trim((string) $img));
                        if ($img !== '' && image_location_status($img) !== 'missing' && image_location_status($img) !== 'blank') {
                            $images[] = $img;
                        }
                    }
                    $value = json_encode(array_slice($images, 0, $key === 'about_team_images' ? 6 : 3));
                    $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
                        ->execute([$key, $value]);
                    $settings[$key] = $value;
                    $saved++;
                }
            }
        }
        if (isset($_POST['steps']) && is_array($_POST['steps'])) {
            foreach ($stepFields[$tab] ?? [] as $key => $label) {
                if (array_key_exists($key, $_POST['steps'])) {
                    $steps = [];
                    foreach ($_POST['steps'][$key] ?? [] as $row) {
                        $title = trim((string) ($row['title'] ?? ''));
                        $text  = trim((string) ($row['text'] ?? ''));
                        if ($title !== '' || $text !== '') $steps[] = ['title' => $title, 'text' => $text];
                    }
                    $value = json_encode(array_slice($steps, 0, 3));
                    $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
                        ->execute([$key, $value]);
                    $settings[$key] = $value;
                    $saved++;
                }
            }
        }
        flash('success', "Saved $saved section setting" . ($saved === 1 ? '' : 's') . '.');
        redirect('admin/content.php?tab=' . urlencode($tab));
    }
}

// Layout only after POST handling, so redirects send no half-rendered page.
require_once __DIR__ . '/includes/header.php';

$availableImages = $pdo->query(
    "SELECT DISTINCT image FROM (
        SELECT image FROM gallery WHERE consent_confirmed = 1
        UNION SELECT 'outreach-01.jpg' UNION SELECT 'outreach-02.jpg' UNION SELECT 'outreach-03.jpg'
        UNION SELECT 'outreach-12.jpg' UNION SELECT 'outreach-13.jpg' UNION SELECT 'outreach-14.jpg' UNION SELECT 'outreach-15.jpg'
    ) x ORDER BY image"
)->fetchAll(PDO::FETCH_COLUMN);
$availableImages = array_values(array_filter($availableImages, fn($img) => image_location_status($img) !== 'missing'));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="mb-1">Page content</h1>
    <p class="text-secondary mb-0">Edit the words and photos shown on the public pages. Changes appear immediately.</p>
  </div>
  <a href="<?= e(url()) ?>" target="_blank" rel="noopener" class="btn btn-outline-ys btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> View site</a>
</div>

<ul class="nav nav-pills ys-tabs mb-4">
  <?php foreach ($tabs as $key => $label): ?>
    <li class="nav-item">
      <a class="nav-link <?= $key === $tab ? 'active' : '' ?>" href="<?= e(url('admin/content.php?tab=' . $key)) ?>"><?= e($label) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<form method="post" action="<?= e(url('admin/content.php?tab=' . $tab)) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="do" value="saveSection">

  <div class="card-ys p-4 mb-4">
    <h5 class="mb-3">Text</h5>
    <?php foreach ($simpleFields[$tab] ?? [] as $key => $label): ?>
      <div class="mb-3">
        <label class="form-label fw-bold small" for="field-<?= e($key) ?>"><?= e($label) ?></label>
        <input type="text" id="field-<?= e($key) ?>" name="<?= e($key) ?>" class="form-control" value="<?= e(setting($key)) ?>" maxlength="500" required>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($imageFields[$tab])): ?>
    <div class="card-ys p-4 mb-4">
      <h5 class="mb-1">Photos</h5>
      <p class="text-secondary small">Tick the photos you want in this section. Choose up to the number shown. Upload new photos in <a href="<?= e(url('admin/gallery.php?action=add')) ?>">Gallery → Add photo</a>.</p>
      <?php foreach ($imageFields[$tab] as $key => $label): ?>
        <?php $chosen = setting_json($key, []); $limit = $key === 'about_team_images' ? 6 : 3; ?>
        <fieldset class="mb-3">
          <legend class="form-label fw-bold small"><?= e($label) ?> (max <?= $limit ?>)</legend>
          <div class="row g-2">
            <?php foreach ($availableImages as $img): ?>
              <div class="col-6 col-sm-4 col-lg-3">
                <label class="content-image-choice d-block border rounded p-2 h-100">
                  <img src="<?= e(image_src($img)) ?>" alt="" class="w-100 rounded mb-2" style="height:90px;object-fit:cover">
                  <span class="d-flex align-items-start gap-2">
                    <input type="checkbox" name="images[<?= e($key) ?>][]" value="<?= e($img) ?>" class="form-check-input mt-1" <?= in_array($img, $chosen, true) ? 'checked' : '' ?>>
                    <span class="small text-break"><?= e($img) ?></span>
                  </span>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </fieldset>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($stepFields[$tab])): ?>
    <div class="card-ys p-4 mb-4">
      <h5 class="mb-3"><?= e(reset($stepFields[$tab])) ?></h5>
      <?php foreach ($stepFields[$tab] as $key => $label): ?>
        <?php $rows = setting_json($key, []); if (!$rows) $rows = [['title'=>'','text'=>'']]; ?>
        <div id="step-rows">
          <?php foreach ($rows as $i => $row): ?>
            <div class="row g-2 mb-2 step-row">
              <div class="col-md-3"><input class="form-control" name="steps[<?= e($key) ?>][<?= $i ?>][title]" placeholder="Step title" value="<?= e($row['title'] ?? '') ?>" maxlength="80"></div>
              <div class="col-md-8"><input class="form-control" name="steps[<?= e($key) ?>][<?= $i ?>][text]" placeholder="Step description" value="<?= e($row['text'] ?? '') ?>" maxlength="300"></div>
              <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-step" aria-label="Remove step"><i class="bi bi-trash"></i></button></div>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline-ys btn-sm" id="add-step" data-step-key="<?= e(array_key_first($stepFields[$tab] ?? [])) ?>"><i class="bi bi-plus-lg me-1"></i> Add step</button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <button type="submit" class="btn btn-ys"><i class="bi bi-check2-circle me-2"></i> Save changes</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
