/* =========================================================
   Your Survival Kit — frontend interactions
   ========================================================= */
document.addEventListener('DOMContentLoaded', function () {

  /* Sticky navbar shrink */
  const navbar = document.querySelector('.ys-navbar');
  const onScroll = () => navbar && navbar.classList.toggle('scrolled', window.scrollY > 40);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  /* Bootstrap tooltips */
  if (window.bootstrap) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  }

  /* Animated counters */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.count, 10);
        const suffix = el.dataset.suffix || '';
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 45));
        const timer = setInterval(() => {
          current += step;
          if (current >= target) { current = target; clearInterval(timer); }
          el.textContent = current.toLocaleString() + suffix;
        }, 24);
        observer.unobserve(el);
      });
    }, { threshold: .4 });
    counters.forEach(el => observer.observe(el));
  }

  /* Gallery lightbox */
  const lb = document.getElementById('ysLightbox');
  if (lb) {
    const lbImg = lb.querySelector('img');
    const lbCaption = lb.querySelector('.lb-caption');
    const items = Array.from(document.querySelectorAll('.gallery-item'));
    let index = 0;

    const show = (i) => {
      if (!items.length) return;
      index = (i + items.length) % items.length;
      const item = items[index];
      lbImg.src = item.dataset.full || item.querySelector('img').src;
      lbImg.alt = item.dataset.title || '';
      lbCaption.textContent = item.dataset.title || '';
    };
    items.forEach((item, i) => item.addEventListener('click', () => { show(i); lb.classList.add('open'); }));
    lb.querySelector('.lb-close').addEventListener('click', () => lb.classList.remove('open'));
    lb.querySelector('.lb-prev').addEventListener('click', () => show(index - 1));
    lb.querySelector('.lb-next').addEventListener('click', () => show(index + 1));
    lb.addEventListener('click', (ev) => { if (ev.target === lb) lb.classList.remove('open'); });
    document.addEventListener('keydown', (ev) => {
      if (!lb.classList.contains('open')) return;
      if (ev.key === 'Escape') lb.classList.remove('open');
      if (ev.key === 'ArrowLeft') show(index - 1);
      if (ev.key === 'ArrowRight') show(index + 1);
    });
  }

  /* Gallery category filtering */
  document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      document.querySelectorAll('.gallery-item').forEach(item => {
        item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
      });
    });
  });

  /* Confirm destructive actions */
  document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', (ev) => {
      if (!window.confirm(form.dataset.confirm || 'Are you sure?')) ev.preventDefault();
    });
  });

  /* Auto-dismiss alerts after 6 seconds */
  document.querySelectorAll('.alert-dismissible').forEach(alert => {
    setTimeout(() => {
      const inst = window.bootstrap ? bootstrap.Alert.getInstance(alert) : null;
      if (inst) inst.close(); else alert.remove();
    }, 6000);
  });

  /* Image preview in admin upload forms */
  const imageInput = document.querySelector('[data-preview]');
  if (imageInput) {
    imageInput.addEventListener('change', function () {
      const target = document.querySelector(this.dataset.preview);
      if (target && this.files && this.files[0]) {
        target.src = URL.createObjectURL(this.files[0]);
        target.style.display = 'block';
      }
    });
  }

  /* Character counter for textareas */
  document.querySelectorAll('[data-counter]').forEach(field => {
    const out = document.querySelector(field.dataset.counter);
    if (!out) return;
    const update = () => out.textContent = field.value.length;
    field.addEventListener('input', update);
    update();
  });
});
