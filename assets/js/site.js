/* Sprintings — site interactions (vanilla JS, no dependencies). Everything degrades gracefully:
   menus link to /products/, forms post normally, FAQs stay open, the map is a link. */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches;
  var raf = window.requestAnimationFrame || function (f) { return setTimeout(f, 16); };

  /* ---------------------------------------------------------- Header: scroll state + progress */
  var header = $('[data-header]');
  var bar = $('.progress span');
  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    raf(function () {
      var y = window.scrollY || 0;
      if (header) header.classList.toggle('is-scrolled', y > 8);
      if (bar) {
        var h = document.documentElement.scrollHeight - innerHeight;
        bar.style.setProperty('--p', h > 0 ? Math.min(1, y / h).toFixed(4) : 0);
      }
      ticking = false;
    });
  }
  addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------------------------------------------------------- Product search (mega-menu, drawer) */
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function bindSearch(scope) {
    var input = $('[data-mega-search]', scope);
    if (!input) return;
    var items = $$('[data-mega-item]', scope);
    var groups = $$('[data-mega-group]', scope);
    var empty = $('[data-mega-empty]', scope);
    var count = $('[data-mega-count]', scope);
    items.forEach(function (li) { var l = $('[data-label]', li); li._label = l ? l.textContent : ''; });
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      var words = q.split(/\s+/).filter(Boolean);
      var shown = 0;
      items.forEach(function (li) {
        var hit = !words.length || words.every(function (w) { return li.getAttribute('data-name').indexOf(w) > -1; });
        li.hidden = !hit;
        var l = $('[data-label]', li);
        if (l) {
          if (hit && words.length) {
            var re = new RegExp('(' + words.map(function (w) { return w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }).join('|') + ')', 'ig');
            l.innerHTML = esc(li._label).replace(re, '<mark>$1</mark>');
          } else { l.textContent = li._label; }
        }
        if (hit) shown++;
      });
      groups.forEach(function (g) { g.hidden = !$$('[data-mega-item]', g).some(function (li) { return !li.hidden; }); });
      if (empty) empty.hidden = shown > 0;
      if (count) count.textContent = shown + (shown === 1 ? ' product' : ' products');
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { var first = items.filter(function (li) { return !li.hidden; })[0]; if (first) { e.preventDefault(); $('a', first).click(); } }
      if (e.key === 'ArrowDown') { var f = items.filter(function (li) { return !li.hidden; })[0]; if (f) { e.preventDefault(); $('a', f).focus(); } }
    });
  }

  /* ---------------------------------------------------------- Mega-menu */
  var mega = $('[data-mega]');
  var megaBtn = $('[data-mega-toggle]');
  if (mega && megaBtn) {
    mega.hidden = false;
    bindSearch(mega);
    var closeTimer;
    var setMega = function (open, focus) {
      clearTimeout(closeTimer);
      mega.classList.toggle('is-open', open);
      megaBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      mega.setAttribute('aria-hidden', open ? 'false' : 'true');
      if ('inert' in mega) mega.inert = !open;
      if (open && focus) setTimeout(function () { var i = $('[data-mega-search]', mega); if (i) i.focus(); }, 60);
    };
    setMega(false);
    megaBtn.addEventListener('click', function () { setMega(megaBtn.getAttribute('aria-expanded') !== 'true', true); });
    if (fine) {
      megaBtn.addEventListener('mouseenter', function () { setMega(true, false); });
      [megaBtn, mega].forEach(function (el) {
        el.addEventListener('mouseleave', function () { closeTimer = setTimeout(function () { if (!mega.contains(document.activeElement)) setMega(false); }, 260); });
        el.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
      });
    }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && mega.classList.contains('is-open')) { setMega(false); megaBtn.focus(); } });
    document.addEventListener('click', function (e) { if (mega.classList.contains('is-open') && !mega.contains(e.target) && !megaBtn.contains(e.target)) setMega(false); });
    mega.addEventListener('focusout', function (e) { if (e.relatedTarget && !mega.contains(e.relatedTarget) && e.relatedTarget !== megaBtn) setMega(false); });
  }

  /* ---------------------------------------------------------- Drawer (mobile menu) */
  var drawer = $('[data-drawer]');
  var openBtn = $('[data-drawer-open]');
  if (drawer && openBtn) {
    var lastFocus;
    var setDrawer = function (open) {
      drawer.classList.toggle('is-open', open);
      drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.documentElement.style.overflow = open ? 'hidden' : '';
      if (open) { lastFocus = document.activeElement; setTimeout(function () { var c = $('.drawer__close', drawer); if (c) c.focus(); }, 50); }
      else if (lastFocus) lastFocus.focus();
    };
    drawer.setAttribute('aria-hidden', 'true');
    if ('inert' in drawer) { drawer.inert = true; }
    openBtn.addEventListener('click', function () { if ('inert' in drawer) drawer.inert = false; setDrawer(true); });
    $$('[data-drawer-close]', drawer).forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); if ('inert' in drawer) drawer.inert = true; }); });
    drawer.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { setDrawer(false); if ('inert' in drawer) drawer.inert = true; }
      if (e.key === 'Tab') {
        var f = $$('a[href], button:not([disabled]), input', drawer).filter(function (x) { return x.offsetParent !== null; });
        if (!f.length) return;
        if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
      }
    });
    var pBtn = $('[data-drawer-products]', drawer);
    if (pBtn) {
      var panel = document.getElementById(pBtn.getAttribute('aria-controls'));
      // The product list is copied from the mega-menu on first open, to keep the page light
      var slot = $('[data-mega-clone]', panel), src = $('[data-mega-results]', mega || document);
      var filled = false;
      var fill = function () {
        if (filled || !slot || !src) return;
        filled = true;
        slot.appendChild(src.cloneNode(true));
        var empty = $('[data-mega-empty]', mega || document);
        if (empty) { var e2 = empty.cloneNode(true); e2.hidden = true; slot.appendChild(e2); }
        $$('mark', slot).forEach(function (m) { m.replaceWith(document.createTextNode(m.textContent)); });
        $$('[hidden]', slot).forEach(function (x) { if (!x.hasAttribute('data-mega-empty')) x.hidden = false; });
        bindSearch(panel);
      };
      pBtn.addEventListener('click', fill);
      pBtn.addEventListener('click', function () {
        var open = pBtn.getAttribute('aria-expanded') !== 'true';
        pBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.hidden = !open;
      });
    }
  }

  /* ---------------------------------------------------------- Floating "Get a Quote" pill */
  var pill = $('[data-quote-pill]');
  if (pill && 'IntersectionObserver' in window) {
    var hideFor = $$('.callbar, .quote-panel, .wizard, .site-footer, .hero .btn-row');
    var visible = new Set();
    var io0 = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) visible.add(e.target); else visible.delete(e.target); });
      pill.classList.toggle('is-hidden', visible.size > 0);
    });
    hideFor.forEach(function (el) { io0.observe(el); });
  }

  /* ---------------------------------------------------------- Reveal on scroll */
  var reveals = $$('.reveal');
  if (reduce || !('IntersectionObserver' in window)) {
    reveals.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    reveals.forEach(function (el) { io.observe(el); });
  }
  var run = $$('[data-run]');
  if (run.length && 'IntersectionObserver' in window) {
    var io2 = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.style.setProperty('--run', 1); var t = $('.process__track span', e.target); if (t) t.style.transform = 'scaleX(1)'; io2.unobserve(e.target); } });
    }, { threshold: 0.35 });
    run.forEach(function (el) { io2.observe(el); });
  }

  /* ---------------------------------------------------------- Magnetic buttons + card tilt */
  if (fine && !reduce) {
    $$('.btn--magnetic').forEach(function (b) {
      b.addEventListener('pointermove', function (e) {
        var r = b.getBoundingClientRect();
        var x = (e.clientX - r.left - r.width / 2) / r.width, y = (e.clientY - r.top - r.height / 2) / r.height;
        b.style.transform = 'translate(' + (x * 10).toFixed(1) + 'px,' + (y * 8 - 2).toFixed(1) + 'px)';
      });
      b.addEventListener('pointerleave', function () { b.style.transform = ''; });
    });
    $$('[data-tilt]').forEach(function (c) {
      var sheet = $('.pcard__sheet', c);
      if (!sheet) return;
      c.addEventListener('pointermove', function (e) {
        var r = sheet.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
        sheet.style.setProperty('--ry', (x * 10).toFixed(2) + 'deg');
        sheet.style.setProperty('--rx', (-y * 10).toFixed(2) + 'deg');
      });
      c.addEventListener('pointerleave', function () { sheet.style.setProperty('--ry', '0deg'); sheet.style.setProperty('--rx', '0deg'); });
    });
    var stack = $('[data-parallax]');
    if (stack) {
      var sheets = $$('.stack__sheet', stack);
      addEventListener('scroll', function () {
        var y = Math.min(400, scrollY);
        sheets.forEach(function (s, i) { s.style.translate = '0 ' + (-(y * (0.04 + i * 0.03))).toFixed(1) + 'px'; });
      }, { passive: true });
    }
  }

  /* ---------------------------------------------------------- Gallery */
  $$('[data-gallery]').forEach(function (g) {
    var slides = $$('[data-slide]', g), thumbs = $$('[data-thumb]', g), i = 0;
    if (slides.length < 2) return;
    function show(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('is-active', k === i); s.setAttribute('aria-hidden', k === i ? 'false' : 'true'); });
      thumbs.forEach(function (t, k) { if (k === i) t.setAttribute('aria-current', 'true'); else t.removeAttribute('aria-current'); });
    }
    thumbs.forEach(function (t, k) { t.addEventListener('click', function () { show(k); }); });
    var p = $('[data-gallery-prev]', g), n = $('[data-gallery-next]', g);
    if (p) p.addEventListener('click', function () { show(i - 1); });
    if (n) n.addEventListener('click', function () { show(i + 1); });
    g.addEventListener('keydown', function (e) { if (e.key === 'ArrowLeft') show(i - 1); if (e.key === 'ArrowRight') show(i + 1); });
    var sx = null;
    g.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    g.addEventListener('touchend', function (e) { if (sx === null) return; var dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 40) show(i + (dx < 0 ? 1 : -1)); sx = null; });
  });

  /* ---------------------------------------------------------- Review carousel */
  $$('[data-carousel]').forEach(function (c) {
    var t = $('[data-carousel-track]', c);
    if (!t) return;
    var step = function (d) { var card = t.firstElementChild; var w = card ? card.getBoundingClientRect().width + 18 : 300; t.scrollBy({ left: d * w, behavior: reduce ? 'auto' : 'smooth' }); };
    var p = $('[data-carousel-prev]', c), n = $('[data-carousel-next]', c);
    if (p) p.addEventListener('click', function () { step(-1); });
    if (n) n.addEventListener('click', function () { step(1); });
  });

  /* ---------------------------------------------------------- FAQ accordion */
  $$('[data-accordion]').forEach(function (acc) {
    $$('.faq__q button', acc).forEach(function (b) {
      var panel = document.getElementById(b.getAttribute('aria-controls'));
      if (!panel) return;
      panel.setAttribute('data-collapsed', '');
      b.addEventListener('click', function () {
        var open = b.getAttribute('aria-expanded') !== 'true';
        b.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) panel.removeAttribute('data-collapsed'); else panel.setAttribute('data-collapsed', '');
      });
    });
  });

  /* ---------------------------------------------------------- Opening hours: today + open now (Long Beach time) */
  $$('[data-hours]').forEach(function (tbl) {
    var now;
    try {
      var parts = new Intl.DateTimeFormat('en-US', { timeZone: 'America/Los_Angeles', weekday: 'short', hour: 'numeric', minute: 'numeric', hour12: false }).formatToParts(new Date());
      var get = function (t) { return (parts.filter(function (p) { return p.type === t; })[0] || {}).value; };
      now = { day: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].indexOf(get('weekday')) + 1, min: (+get('hour') % 24) * 60 + (+get('minute')) };
    } catch (e) { return; }
    var openNow = false, found = false;
    $$('tr[data-iso]', tbl).forEach(function (tr) {
      if (tr.getAttribute('data-iso').split(',').indexOf(String(now.day)) < 0) return;
      tr.classList.add('is-today'); found = true;
      var m = (tr.getAttribute('data-time') || '').match(/(\d+):(\d+)\s*(AM|PM)\s*[–-]\s*(\d+):(\d+)\s*(AM|PM)/i);
      if (m) {
        var toMin = function (h, mi, ap) { h = +h % 12; if (/pm/i.test(ap)) h += 12; return h * 60 + (+mi); };
        openNow = now.min >= toMin(m[1], m[2], m[3]) && now.min < toMin(m[4], m[5], m[6]);
      }
    });
    var badge = tbl.parentNode && $('[data-open-now]', tbl.parentNode);
    if (badge && found) { badge.hidden = false; badge.textContent = openNow ? 'Open now' : 'Closed now'; badge.classList.toggle('is-open', openNow); }
  });

  /* ---------------------------------------------------------- Products index: filter + search */
  var filt = $('[data-filter]');
  if (filt) {
    var cards = $$('[data-card]');
    var blocks = $$('[data-cat-block]');
    var tabs = $$('[data-filter-cat]', filt);
    var search = $('[data-filter-search]', filt);
    var none = $('[data-no-results]');
    var live = $('[data-filter-live]');
    var cat = '';
    var apply = function () {
      var words = (search ? search.value : '').trim().toLowerCase().split(/\s+/).filter(Boolean);
      var shown = 0;
      cards.forEach(function (c) {
        var ok = (!cat || c.getAttribute('data-cat') === cat) && words.every(function (w) { return c.getAttribute('data-name').indexOf(w) > -1; });
        (c.closest('[data-card-wrap]') || c).hidden = !ok;
        if (ok) shown++;
      });
      blocks.forEach(function (b) { b.hidden = !$$('[data-card]', b).some(function (c) { return !(c.closest('[data-card-wrap]') || c).hidden; }); });
      if (none) none.hidden = shown > 0;
      if (live) live.textContent = shown + ' products shown';
    };
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        cat = t.getAttribute('data-filter-cat');
        tabs.forEach(function (x) { x.setAttribute('aria-pressed', x === t ? 'true' : 'false'); });
        apply();
        if (history.replaceState) history.replaceState(null, '', cat ? '#' + cat : location.pathname);
      });
    });
    if (search) search.addEventListener('input', apply);
    var h = location.hash.replace('#', '');
    var startTab = tabs.filter(function (t) { return t.getAttribute('data-filter-cat') === h; })[0];
    if (startTab) startTab.click();
  }

  /* ---------------------------------------------------------- Share (blog) */
  $$('[data-copy-link]').forEach(function (b) {
    b.addEventListener('click', function () {
      var msg = $('[data-share-msg]');
      var done = function () { if (msg) msg.textContent = 'Link copied.'; };
      if (navigator.share) { navigator.share({ title: document.title, url: location.href }).catch(function () {}); return; }
      if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(done); else { prompt('Copy this link:', location.href); }
    });
  });

  /* ---------------------------------------------------------- Cookie notice */
  var ck = $('[data-cookie]');
  if (ck) {
    var seen = false;
    try { seen = localStorage.getItem('cookie-ok') === '1'; } catch (e) {}
    if (!seen) ck.hidden = false;
    var ok = $('[data-cookie-ok]', ck);
    if (ok) ok.addEventListener('click', function () { ck.hidden = true; try { localStorage.setItem('cookie-ok', '1'); } catch (e) {} });
  }

  /* ---------------------------------------------------------- Forms: files, wizard, AJAX submit with upload progress */
  function fmtSize(b) { return b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }
  var OK_EXT = /\.(pdf|ai|eps|psd|png|jpe?g|tiff?|zip)$/i;

  $$('[data-form]').forEach(function (form) {
    var status = $('[data-form-status]', form);
    var fileInput = $('input[type=file]', form);
    var list = $('[data-file-list]', form);
    var files = [];

    function setError(field, msg) {
      var wrap = field.closest('.field');
      if (!wrap) return;
      wrap.classList.toggle('has-error', !!msg);
      var e = $('.field__error', wrap);
      if (msg) {
        if (!e) { e = document.createElement('p'); e.className = 'field__error'; e.id = (field.id || 'f') + '-err'; wrap.appendChild(e); }
        e.textContent = msg;
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', e.id);
      } else if (e) { e.remove(); field.removeAttribute('aria-invalid'); field.removeAttribute('aria-describedby'); }
    }
    function check(field) {
      var v = field.value.trim(), msg = '';
      var label = ((field.closest('.field') || {}).querySelector ? (field.closest('.field').querySelector('label') || {}).textContent || '' : '').replace('*', '').trim();
      if (field.required && !v) msg = 'Please enter your ' + label.toLowerCase() + '.';
      else if (field.type === 'email' && v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) msg = 'Please enter a valid email address.';
      else if (field.type === 'tel' && v && v.replace(/\D/g, '').length < 10) msg = 'Please enter a valid phone number, including the area code.';
      setError(field, msg);
      return !msg;
    }
    $$('input:not([type=hidden]):not([type=file]):not([name=website]), textarea', form).forEach(function (f) {
      f.addEventListener('blur', function () { if (f.value || f.closest('.has-error')) check(f); });
      f.addEventListener('input', function () { if (f.closest('.has-error')) check(f); });
    });

    // Files: keep our own list so files can be removed before sending
    function renderFiles() {
      if (!list) return;
      list.innerHTML = '';
      files.forEach(function (f, i) {
        var li = document.createElement('li');
        var bad = !OK_EXT.test(f.name) ? 'Not an accepted file type' : f.size > +fileInput.getAttribute('data-max-bytes') ? 'Larger than 50 MB' : '';
        if (bad) li.className = 'is-bad';
        li.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg><span></span><small></small><button type="button" aria-label="Remove file"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>';
        $('span', li).textContent = f.name;
        $('small', li).textContent = bad || fmtSize(f.size);
        $('button', li).addEventListener('click', function () { files.splice(i, 1); renderFiles(); });
        list.appendChild(li);
      });
    }
    if (fileInput) {
      var drop = fileInput.closest('[data-drop]');
      fileInput.addEventListener('change', function () {
        var max = +fileInput.getAttribute('data-max') || 5;
        Array.prototype.forEach.call(fileInput.files, function (f) {
          if (files.length < max && !files.some(function (x) { return x.name === f.name && x.size === f.size; })) files.push(f);
        });
        if (fileInput.files.length && files.length >= max && status) { status.className = 'form__status'; status.textContent = 'Up to ' + max + ' files. Zip them together to send more.'; }
        fileInput.value = '';
        renderFiles();
      });
      if (drop) {
        ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.add('is-over'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.remove('is-over'); }); });
      }
    }

    // Wizard
    var stepsEls = $$('[data-step]', form);
    var current = 1;
    var stepNav = document.querySelector('[data-wizard-steps]');
    var barEl = $('[data-wizard-bar]', form);
    function goStep(n, focus) {
      current = n;
      stepsEls.forEach(function (s) { s.classList.toggle('is-current', +s.getAttribute('data-step') === n); });
      if (barEl) barEl.style.width = (n / stepsEls.length * 100) + '%';
      if (stepNav) $$('button', stepNav).forEach(function (b) {
        var k = +b.getAttribute('data-goto');
        if (k === n) b.setAttribute('aria-current', 'step'); else b.removeAttribute('aria-current');
        b.classList.toggle('is-done', k < n);
        b.disabled = k > n;
      });
      if (focus) { var t = $('[data-step="' + n + '"] legend', form); if (t) { t.setAttribute('tabindex', '-1'); t.focus(); } var top = form.getBoundingClientRect().top + scrollY - 110; if (top < scrollY) scrollTo({ top: top, behavior: reduce ? 'auto' : 'smooth' }); }
    }
    function validStep(n) {
      var ok = true, first = null;
      $$('[data-step="' + n + '"] input:not([type=file]):not([name=website]), [data-step="' + n + '"] textarea', form).forEach(function (f) { if (!check(f)) { ok = false; first = first || f; } });
      if (first) first.focus();
      return ok;
    }
    if (stepsEls.length) {
      $$('[data-wizard-next]', form).forEach(function (b) { b.addEventListener('click', function () { if (validStep(current)) goStep(current + 1, true); }); });
      $$('[data-wizard-prev]', form).forEach(function (b) { b.addEventListener('click', function () { goStep(current - 1, true); }); });
      if (stepNav) $$('button', stepNav).forEach(function (b) { b.addEventListener('click', function () { var k = +b.getAttribute('data-goto'); if (k < current) goStep(k, true); }); });
      goStep(1, false);
    }

    form.addEventListener('submit', function (e) {
      if (!window.FormData || !window.XMLHttpRequest) return;
      e.preventDefault();
      var fields = $$('input:not([type=hidden]):not([type=file]):not([name=website]), textarea', form);
      var bad = fields.filter(function (f) { return !check(f); });
      if (bad.length) {
        var step = bad[0].closest('[data-step]');
        if (step && stepsEls.length) goStep(+step.getAttribute('data-step'), false);
        bad[0].focus();
        if (status) { status.className = 'form__status form__status--error'; status.textContent = 'Please check the highlighted fields.'; }
        return;
      }
      if (files.some(function (f) { return !OK_EXT.test(f.name) || f.size > +fileInput.getAttribute('data-max-bytes'); })) {
        if (status) { status.className = 'form__status form__status--error'; status.textContent = 'Please remove the files marked in red.'; }
        return;
      }
      var data = new FormData(form);
      data.delete('files[]');
      files.forEach(function (f) { data.append('files[]', f, f.name); });
      var btn = $('[type=submit]', form);
      var label = btn ? btn.innerHTML : '';
      if (btn) { btn.disabled = true; btn.innerHTML = files.length ? 'Uploading…' : 'Sending…'; }
      if (status) { status.className = 'form__status'; status.textContent = ''; }
      var prog = $('[data-upload-progress]', form);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', form.action);
      xhr.setRequestHeader('Accept', 'application/json');
      if (files.length && prog) {
        prog.hidden = false;
        xhr.upload.addEventListener('progress', function (ev) {
          if (ev.lengthComputable) { var pc = Math.round(ev.loaded / ev.total * 100); $('span', prog).style.width = pc + '%'; if (btn) btn.textContent = 'Uploading… ' + pc + '%'; }
        });
      }
      xhr.onload = function () {
        var res = null;
        try { res = JSON.parse(xhr.responseText); } catch (er) {}
        if (prog) prog.hidden = true;
        if (res && res.ok) {
          if (res.redirect) { location.href = res.redirect; return; }
          form.innerHTML = '<div class="form-success" role="status" tabindex="-1"><p style="margin:0;font-weight:600">' + esc(res.message || form.getAttribute('data-success') || 'Thank you!') + '</p></div>';
          if (form.classList.contains('form--compact')) form.firstChild.style.padding = '8px 0';
          form.firstChild.focus();
          return;
        }
        if (btn) { btn.disabled = false; btn.innerHTML = label; }
        if (status) { status.className = 'form__status form__status--error'; status.textContent = (res && res.error) || (xhr.status === 413 ? 'Your files are too large to send. Please send smaller files or email them to us.' : 'Sorry, something went wrong. Please try again or call us.'); }
      };
      xhr.onerror = function () {
        if (btn) { btn.disabled = false; btn.innerHTML = label; }
        if (prog) prog.hidden = true;
        if (status) { status.className = 'form__status form__status--error'; status.textContent = 'Connection problem. Please check your internet connection and try again.'; }
      };
      xhr.send(data);
    });
  });
})();
