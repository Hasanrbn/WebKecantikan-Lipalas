/* Lipalas Beauty Clinic - skrip bersama. Tanpa dependensi selain Bootstrap. */
(function () {
  'use strict';

  // Tombol tampil/sembunyi password: <button data-toggle-password="#idInput">
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.querySelector(btn.getAttribute('data-toggle-password'));
      if (!input) { return; }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      var icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye', show);
        icon.classList.toggle('fa-eye-slash', !show);
      }
    });
  });

  // Konfirmasi sebelum aksi berbahaya: <form data-confirm="Pesan konfirmasi">
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Tanggal booking tidak boleh di masa lalu (validasi server tetap menjadi penentu akhir).
  document.querySelectorAll('input[type="date"][data-min-today]').forEach(function (input) {
    var d = new Date();
    var pad = function (n) { return String(n).padStart(2, '0'); };
    input.min = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  });

  // Cegah klik ganda pada tombol kirim.
  document.querySelectorAll('form[data-single-submit]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        window.setTimeout(function () { btn.disabled = true; }, 0);
      }
    });
  });

  // Tombol kembali aktif lagi jika pengguna menekan "Back" di browser.
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      document.querySelectorAll('form[data-single-submit] button[type="submit"]').forEach(function (b) {
        b.disabled = false;
      });
    }
  });
})();
