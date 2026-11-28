</main>

<footer class="ys-footer">
  <div class="ys-container">
    <div class="row gy-5">
      <div class="col-lg-4">
        <img src="<?= e(asset('img/logo.png')) ?>" alt="Your Survival Kit logo" class="footer-logo">
        <h5>Your Survival Kit</h5>
        <p class="pe-lg-4"><?= e(setting('about_short', SITE_TAGLINE)) ?></p>
        <div class="mt-3">
          <?php foreach (['facebook' => 'bi-facebook', 'instagram' => 'bi-instagram', 'twitter' => 'bi-twitter-x', 'tiktok' => 'bi-music-note-beamed'] as $key => $icon): ?>
            <?php if ($link = setting($key)): ?>
              <a class="social-btn d-inline-grid" href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($key) ?>">
                <i class="bi <?= e($icon) ?>"></i>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h5>Explore</h5>
        <ul class="footer-links">
          <li><a href="<?= e(url()) ?>">Home</a></li>
          <li><a href="<?= e(url('about')) ?>">About Us</a></li>
          <li><a href="<?= e(url('what-we-do')) ?>">What We Do</a></li>
          <li><a href="<?= e(url('gallery')) ?>">Gallery</a></li>
          <li><a href="<?= e(url('news')) ?>">News & Stories</a></li>
          <li><a href="<?= e(url('events')) ?>">Upcoming Events</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h5>Get Involved</h5>
        <ul class="footer-links">
          <li><a href="<?= e(url('donate')) ?>">Donate</a></li>
          <li><a href="<?= e(url('volunteer') . '#apply') ?>">Volunteer</a></li>
          <li><a href="<?= e(url('volunteer') . '?interest=' . rawurlencode('Partner coordination') . '#apply') ?>">Partner With Us</a></li>
          <li><a href="<?= e(url('contact')) ?>">Contact</a></li>
          <li><a href="<?= e(url('admin/login.php')) ?>">Staff Login</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h5>Event updates</h5>
        <p class="mb-3">Confirm your email and we will tell you about upcoming outreaches. No noise.</p>
        <form action="<?= e(url('newsletter.php')) ?>" method="post" class="d-flex flex-wrap gap-2 mb-4">
          <?php csrf_field(); ?>
          <input type="email" name="email" class="form-control flex-grow-1" style="min-width:180px" placeholder="Your email address" required aria-label="Email address">
          <button class="btn btn-ys" type="submit">Join</button>
        </form>
        <h5>Reach us</h5>
        <ul class="footer-links footer-contact list-unstyled">
          <?php if ($address = setting('site_address')): ?>
            <li><i class="bi bi-geo-alt"></i><span><?= e($address) ?></span></li>
          <?php endif; ?>
          <?php if ($email = setting('site_email')): ?>
            <li><i class="bi bi-envelope"></i><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
          <?php endif; ?>
          <?php if ($phone = setting('site_phone')): ?>
            <li><i class="bi bi-telephone"></i><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', $phone)) ?>"><?= e($phone) ?></a></li>
          <?php endif; ?>
          <?php if (setting('whatsapp_number')): ?>
            <li><i class="bi bi-whatsapp"></i><a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">WhatsApp: <?= e(setting('whatsapp_number')) ?></a></li>
          <?php endif; ?>
          <?php if ($hours = setting('office_hours')): ?>
            <li><i class="bi bi-clock"></i><span><?= e($hours) ?></span></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
      <span>&copy; <?= date('Y') ?> Your Survival Kit. All rights reserved.</span>
      <span><i class="bi bi-heart-fill text-danger"></i> Built with care by young people, for young people.</span>
    </div>
  </div>
</footer>

<?php if (setting('whatsapp_number') && ($wa = whatsapp_link(null, 'Hello Your Survival Kit, I would like to talk to Shanis Pretty.'))): ?>
  <a class="whatsapp-float" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Chat with the team on WhatsApp">
    <i class="bi bi-whatsapp"></i><span>Chat on WhatsApp</span>
  </a>
<?php endif; ?>

<a class="staff-login-float" href="<?= e(url('admin/login.php?reauth=1')) ?>" title="Staff login — sign in again" aria-label="Staff login — sign in again">
  <i class="bi bi-person-badge"></i>
</a>

<!-- Lightbox (used by the gallery) -->
<div class="ys-lightbox" id="ysLightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
  <button class="lb-close" aria-label="Close">&times;</button>
  <button class="lb-nav lb-prev" aria-label="Previous image">&#8249;</button>
  <img alt="" style="display:none">
  <button class="lb-nav lb-next" aria-label="Next image">&#8250;</button>
  <div class="lb-caption"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js') . '?v=20261128b') ?>"></script>
</body>
</html>
