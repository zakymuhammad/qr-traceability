// assets/app.js
// Auto-dismiss flash alert (pop up) setelah beberapa detik.
// Berlaku untuk .alert (halaman dalam) dan .alert-box (halaman login/register).
(function () {
  var AUTO_HIDE_MS = 4000; // hilang otomatis setelah 4 detik

  function dismiss(el) {
    if (!el || el.dataset.dismissed) return;
    el.dataset.dismissed = '1';
    el.style.transition = 'opacity .4s ease, transform .4s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateY(-6px)';
    setTimeout(function () { if (el && el.parentNode) el.parentNode.removeChild(el); }, 420);
  }

  function init() {
    var alerts = document.querySelectorAll('.alert, .alert-box');
    alerts.forEach(function (el) {
      // Klik untuk menutup manual
      el.style.cursor = 'pointer';
      el.title = 'Klik untuk menutup';
      el.addEventListener('click', function () { dismiss(el); });
      // Hilang otomatis
      setTimeout(function () { dismiss(el); }, AUTO_HIDE_MS);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
