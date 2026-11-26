<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle       = 'Page not found';
$pageDescription = 'The page you requested could not be found.';
include __DIR__ . '/includes/header.php';
?>
<section class="section text-center">
  <div class="ys-container">
    <div style="font-size:5.5rem;font-family:var(--ys-font-head);font-weight:800;color:var(--ys-green)">404</div>
    <h1>This page took a different path.</h1>
    <p class="text-secondary lead-lg mx-auto">The page you were looking for does not exist or has moved. Let us get you back to something useful.</p>
    <div class="mt-4">
      <a href="<?= e(url()) ?>" class="btn btn-ys">Back home</a>
      <a href="<?= e(url('contact')) ?>" class="btn btn-outline-ys">Contact us</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
