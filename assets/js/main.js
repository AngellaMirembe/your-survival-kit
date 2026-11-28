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
      lbImg.style.display = 'block';
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

  /* Confirm destructive actions (delegated so it keeps working after in-place updates) */
  document.addEventListener('submit', function (ev) {
    const form = ev.target.closest ? ev.target.closest('form[data-confirm]') : null;
    if (!form || form.dataset.noAjax !== undefined) return;
    if (!window.confirm(form.dataset.confirm || 'Are you sure?')) ev.preventDefault();
  });

  /* Keep admin actions on the same screen. The response replaces only .admin-content. */
  document.addEventListener('submit', async function (ev) {
    const form = ev.target;
    if (ev.defaultPrevented || !form || form.tagName !== 'FORM') return;
    if (!document.body.classList.contains('admin-body')) return;
    if (!form.closest('.admin-content')) return;
    if ((form.method || 'get').toLowerCase() !== 'post') return;
    if (form.dataset.noAjax !== undefined || form.target === '_blank') return;

    ev.preventDefault();
    const content = document.querySelector('.admin-content');
    if (!content) return;

    const button = ev.submitter || form.querySelector('[type="submit"]');
    const originalText = button ? button.innerHTML : '';
    if (button) { button.disabled = true; button.innerHTML = '<i class="bi bi-hourglass-split"></i> Working…'; }

    const data = new FormData(form);
    if (ev.submitter && ev.submitter.name) {
      data.delete(ev.submitter.name);
      data.append(ev.submitter.name, ev.submitter.value);
    }

    try {
      const response = await fetch(form.action || window.location.href, {
        method: 'POST',
        body: data,
        credentials: 'same-origin',
        redirect: 'follow',
        headers: { 'X-Requested-With': 'fetch' }
      });
      const html = await response.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const next = doc.querySelector('.admin-content');
      if (!next) { window.location.reload(); return; }

      const scrollY = window.scrollY;
      content.innerHTML = next.innerHTML;
      document.title = doc.title || document.title;
      if (response.url && response.url !== window.location.href) {
        window.history.replaceState(null, '', response.url);
      }
      window.scrollTo(0, scrollY);

      if (window.bootstrap) {
        content.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
      }
    } catch (err) {
      window.location.reload();
    } finally {
      if (button && button.isConnected) {
        button.disabled = false;
        button.innerHTML = originalText;
      }
    }
  });

  /* Image previews and step-row controls stay delegated after in-place updates. */
  document.addEventListener('change', function (ev) {
    const input = ev.target.closest ? ev.target.closest('[data-preview]') : null;
    if (!input) return;
    const target = document.querySelector(input.dataset.preview);
    if (target && input.files && input.files[0]) {
      target.src = URL.createObjectURL(input.files[0]);
      target.style.display = 'block';
    }
  });

  document.addEventListener('click', function (ev) {
    const add = ev.target.closest ? ev.target.closest('#add-step') : null;
    if (add) {
      const wrap = document.getElementById('step-rows');
      const key = add.dataset.stepKey;
      if (wrap && key) {
        const i = wrap.querySelectorAll('.step-row').length;
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 step-row';
        row.innerHTML = `<div class="col-md-3"><input class="form-control" name="steps[${key}][${i}][title]" placeholder="Step title" maxlength="80"></div>
          <div class="col-md-8"><input class="form-control" name="steps[${key}][${i}][text]" placeholder="Step description" maxlength="300"></div>
          <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-step" aria-label="Remove step"><i class="bi bi-trash"></i></button></div>`;
        wrap.appendChild(row);
      }
      return;
    }
    const remove = ev.target.closest ? ev.target.closest('.remove-step') : null;
    if (remove) remove.closest('.step-row').remove();
  });

  /* Auto-dismiss alerts after 6 seconds */
  document.querySelectorAll('.alert-dismissible').forEach(alert => {
    setTimeout(() => {
      const inst = window.bootstrap ? bootstrap.Alert.getInstance(alert) : null;
      if (inst) inst.close(); else alert.remove();
    }, 6000);
  });

  /* Character counter for textareas */
  document.querySelectorAll('[data-counter]').forEach(field => {
    const out = document.querySelector(field.dataset.counter);
    if (!out) return;
    const update = () => out.textContent = field.value.length;
    field.addEventListener('input', update);
    update();
  });

  /* Admin menu drawer (mobile) */
  const sidebar  = document.getElementById('adminSidebar');
  if (sidebar) {
    const toggle   = document.getElementById('adminMenuToggle');
    const closeBtn = document.getElementById('adminSidebarClose');
    const backdrop = document.getElementById('adminSidebarBackdrop');

    const setOpen = (open) => {
      sidebar.classList.toggle('open', open);
      if (backdrop) backdrop.classList.toggle('visible', open);
      if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    };

    if (toggle) {
      toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
    }
    if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
    if (backdrop) backdrop.addEventListener('click', () => setOpen(false));

    sidebar.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992) setOpen(false);
      });
    });

    document.addEventListener('keydown', (ev) => {
      if (ev.key === 'Escape' && sidebar.classList.contains('open')) setOpen(false);
    });
  }
});
