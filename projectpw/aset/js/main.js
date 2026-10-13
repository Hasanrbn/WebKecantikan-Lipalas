/* Lipalas Beauty Clinic - skrip bersama. Tanpa dependensi.
   Semua fitur bersifat tambahan: halaman tetap bisa dipakai tanpa JavaScript,
   dan validasi server tetap menjadi penentu akhir. */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  /* ---------- Header: garis tipis muncul setelah halaman digulir ---------- */
  (function headerState() {
    var header = $('.site-header');
    var sentinel = $('#scroll-sentinel');
    if (!header) { return; }
    if ('IntersectionObserver' in window && sentinel) {
      new IntersectionObserver(function (entries) {
        header.classList.toggle('is-scrolled', !entries[0].isIntersecting);
      }).observe(sentinel);
    } else {
      var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 24); };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    }
  })();

  /* ---------- Menu mobile ---------- */
  (function mobileMenu() {
    var toggle = $('.nav-toggle');
    var menu = $('#site-menu');
    if (!toggle || !menu) { return; }

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      menu.classList.toggle('is-open', open);
    }
    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    menu.addEventListener('click', function (e) {
      if (e.target.closest && e.target.closest('a')) { setOpen(false); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        toggle.focus();
      }
    });
    window.matchMedia('(min-width: 992px)').addEventListener('change', function (e) {
      if (e.matches) { setOpen(false); }
    });
  })();

  /* ---------- Menu aktif mengikuti bagian yang sedang dibaca (beranda saja) ---------- */
  (function activeNav() {
    if (!$('#home') || !('IntersectionObserver' in window)) { return; }
    var map = {};
    $$('.site-nav__link').forEach(function (a) {
      var hash = a.hash;
      if (hash && $(hash)) { map[hash.slice(1)] = a; }
    });
    var ids = Object.keys(map);
    if (!ids.length) { return; }

    var visible = {};
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { visible[en.target.id] = en.isIntersecting; });
      var current = null;
      ids.forEach(function (id) { if (visible[id]) { current = id; } });
      ids.forEach(function (id) {
        if (id === current) { map[id].setAttribute('aria-current', 'true'); }
        else { map[id].removeAttribute('aria-current'); }
      });
    }, { rootMargin: '-35% 0px -55% 0px' });
    ids.forEach(function (id) { io.observe($('#' + id)); });
  })();

  /* ---------- FAQ/layanan: cadangan untuk browser tanpa <details name> ---------- */
  (function exclusiveDetails() {
    $$('details[name]').forEach(function (d) {
      d.addEventListener('toggle', function () {
        if (!d.open) { return; }
        $$('details[name="' + d.getAttribute('name') + '"]').forEach(function (other) {
          if (other !== d) { other.open = false; }
        });
      });
    });
  })();

  /* ---------- Tampil/sembunyikan password ---------- */
  $$('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = $(btn.getAttribute('data-toggle-password'));
      if (!input) { return; }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
  });

  /* ---------- Tanggal booking tidak boleh di masa lalu ---------- */
  $$('input[type="date"][data-min-today]').forEach(function (input) {
    var d = new Date();
    var pad = function (n) { return String(n).padStart(2, '0'); };
    input.min = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  });

  /* ---------- Validasi formulir di sisi browser (server tetap menentukan) ---------- */
  (function validation() {
    var ERR_ICON = '<svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>';

    function message(el) {
      var v = el.validity;
      if (v.customError) { return el.validationMessage; }
      if (v.valueMissing) {
        return el.getAttribute('data-msg-missing') || (el.tagName === 'SELECT' ? 'Pilih salah satu pilihan.' : 'Kolom ini wajib diisi.');
      }
      if (v.typeMismatch) {
        return el.type === 'email' ? 'Format email tidak valid. Contoh: nama@email.com' : 'Format isian tidak valid.';
      }
      if (v.patternMismatch) { return el.getAttribute('data-msg-pattern') || 'Format isian tidak valid.'; }
      if (v.tooShort) { return 'Minimal ' + el.minLength + ' karakter.'; }
      if (v.rangeUnderflow) { return 'Tanggal tidak boleh sebelum hari ini.'; }
      if (v.badInput) { return 'Isian tidak valid.'; }
      return el.validationMessage;
    }

    function errorNode(el) {
      var id = (el.id || el.name) + '-error';
      var node = document.getElementById(id);
      if (!node) {
        node = document.createElement('p');
        node.id = id;
        node.className = 'field__error';
        var anchor = el.closest('.password-wrap') || el;
        anchor.insertAdjacentElement('afterend', node);
      }
      return node;
    }

    function check(el) {
      var field = el.closest('.field');
      if (el.hasAttribute('data-match')) {
        var other = $(el.getAttribute('data-match'));
        el.setCustomValidity(other && el.value !== other.value ? 'Konfirmasi password tidak sama.' : '');
      }
      var ok = el.checkValidity();
      var node = errorNode(el);
      var desc = (el.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
      var hasErr = desc.indexOf(node.id) !== -1;
      if (ok) {
        node.hidden = true;
        node.innerHTML = '';
        if (field) { field.classList.remove('is-invalid'); }
        el.removeAttribute('aria-invalid');
        if (hasErr) { el.setAttribute('aria-describedby', desc.filter(function (d) { return d !== node.id; }).join(' ')); }
      } else {
        node.hidden = false;
        node.innerHTML = ERR_ICON;
        var span = document.createElement('span');
        span.textContent = message(el);
        node.appendChild(span);
        if (field) { field.classList.add('is-invalid'); }
        el.setAttribute('aria-invalid', 'true');
        if (!hasErr) { desc.push(node.id); el.setAttribute('aria-describedby', desc.join(' ')); }
      }
      return ok;
    }

    $$('form[data-validate]').forEach(function (form) {
      form.noValidate = true;   // pesan kita sendiri; tanpa JS, validasi bawaan browser tetap jalan
      var controls = $$('input, select, textarea', form).filter(function (el) {
        return el.type !== 'hidden' && el.type !== 'submit' && !el.readOnly && !el.disabled && el.name;
      });
      controls.forEach(function (el) {
        el.addEventListener('blur', function () { if (el.value !== '' || el.dataset.touched) { el.dataset.touched = '1'; check(el); } });
        var live = function () { if (el.dataset.touched || el.closest('.field.is-invalid')) { check(el); } };
        el.addEventListener('input', live);
        el.addEventListener('change', function () { el.dataset.touched = '1'; check(el); });
      });
      // Konfirmasi password perlu dicek ulang saat password utama berubah.
      controls.forEach(function (el) {
        if (el.hasAttribute('data-match')) {
          var other = $(el.getAttribute('data-match'));
          if (other) { other.addEventListener('input', function () { if (el.dataset.touched) { check(el); } }); }
        }
      });
      form.addEventListener('submit', function (e) {
        var first = null;
        controls.forEach(function (el) {
          el.dataset.touched = '1';
          if (!check(el) && !first) { first = el; }
        });
        if (first) {
          e.preventDefault();
          e.stopImmediatePropagation();
          first.focus();
        }
      });
    });
  })();

  /* ---------- Cegah klik ganda pada tombol kirim ---------- */
  (function singleSubmit() {
    $$('form[data-single-submit]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (e.defaultPrevented) { return; }
        var btn = $('button[type="submit"]', form);
        if (!btn) { return; }
        window.setTimeout(function () {
          btn.dataset.label = btn.textContent;
          btn.style.minWidth = btn.offsetWidth + 'px';
          btn.textContent = 'Mengirim...';
          btn.disabled = true;
        }, 0);
      });
    });
    // Tombol Back di browser: aktifkan kembali tombolnya.
    window.addEventListener('pageshow', function (e) {
      if (!e.persisted) { return; }
      $$('form[data-single-submit] button[type="submit"]').forEach(function (b) {
        b.disabled = false;
        if (b.dataset.label) { b.textContent = b.dataset.label; }
      });
    });
  })();

  /* ---------- Konfirmasi sebelum aksi berbahaya ---------- */
  (function confirmDialog() {
    var forms = $$('form[data-confirm]');
    if (!forms.length) { return; }
    var supportsDialog = typeof HTMLDialogElement === 'function';
    var dlg, textEl, titleEl, okBtn, pending;

    function build() {
      dlg = document.createElement('dialog');
      dlg.className = 'dialog';
      dlg.setAttribute('aria-labelledby', 'dlg-title');
      dlg.innerHTML =
        '<div class="dialog__body"><h2 id="dlg-title"></h2><p id="dlg-text"></p></div>' +
        '<form method="dialog" class="dialog__actions">' +
        '<button type="submit" value="cancel" class="button" autofocus>Batal</button>' +
        '<button type="submit" value="ok" class="button button--danger-solid"></button></form>';
      document.body.appendChild(dlg);
      titleEl = $('#dlg-title', dlg);
      textEl = $('#dlg-text', dlg);
      okBtn = $('button[value="ok"]', dlg);
      dlg.setAttribute('aria-describedby', 'dlg-text');
      dlg.addEventListener('close', function () {
        if (dlg.returnValue === 'ok' && pending) {
          var f = pending;
          pending = null;
          f.submit();
        }
        pending = null;
      });
    }

    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = form.getAttribute('data-confirm');
        if (!supportsDialog) {
          if (window.confirm(msg)) { form.submit(); }
          return;
        }
        if (!dlg) { build(); }
        pending = form;
        titleEl.textContent = form.getAttribute('data-confirm-title') || 'Lanjutkan?';
        textEl.textContent = msg;
        okBtn.textContent = form.getAttribute('data-confirm-ok') || 'Ya, lanjutkan';
        dlg.returnValue = '';
        dlg.showModal();
      });
    });
  })();

  /* ---------- Ringkasan layanan pada formulir booking ---------- */
  (function bookingSummary() {
    var sel = $('#service[data-summary-source]');
    var box = $('[data-summary]');
    if (!sel || !box) { return; }
    var date = $('#tanggal');
    var out = {
      service: $('[data-sum="service"]', box),
      time: $('[data-sum="duration"]', box),
      price: $('[data-sum="price"]', box),
      date: $('[data-sum="date"]', box),
      note: $('[data-sum="note"]', box)
    };
    var NONE = 'Belum dipilih';

    function render() {
      var opt = sel.options[sel.selectedIndex];
      var has = opt && opt.value !== '';
      out.service.textContent = has ? opt.value : NONE;
      out.time.textContent = has && opt.dataset.duration ? opt.dataset.duration : '-';
      out.price.textContent = has ? (opt.dataset.price || 'Hubungi klinik') : '-';
      if (out.note) { out.note.textContent = has && opt.dataset.blurb ? opt.dataset.blurb : 'Pilih perawatan untuk melihat ringkasannya.'; }
      if (date && out.date) {
        var v = date.value;
        if (v && /^\d{4}-\d{2}-\d{2}$/.test(v)) {
          var p = v.split('-');
          var d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
          try {
            out.date.textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
          } catch (err) { out.date.textContent = v; }
        } else {
          out.date.textContent = NONE;
        }
      }
    }
    sel.addEventListener('change', render);
    if (date) { date.addEventListener('input', render); date.addEventListener('change', render); }
    render();
  })();

  /* ---------- Penghitung karakter catatan ---------- */
  $$('[data-count-for]').forEach(function (counter) {
    var ta = document.getElementById(counter.getAttribute('data-count-for'));
    if (!ta) { return; }
    var max = ta.maxLength > 0 ? ta.maxLength : 1000;
    var update = function () { counter.textContent = ta.value.length + ' / ' + max; };
    ta.addEventListener('input', update);
    update();
  });

})();
