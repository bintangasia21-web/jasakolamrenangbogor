/*
 * Scroll-reveal ringan (IntersectionObserver murni, tanpa dependency).
 * Elemen mendapat class "reveal" HANYA lewat skrip ini -- kalau JS
 * gagal dimuat atau browser tidak dukung IntersectionObserver, class
 * itu tidak pernah ditambahkan sama sekali sehingga konten tetap
 * terlihat normal (CSS animasinya baru aktif kalau <html> ditandai
 * "js-reveal-ready" oleh skrip ini).
 */
(function () {
  "use strict";
  if (!("IntersectionObserver" in window)) return;

  document.documentElement.classList.add("js-reveal-ready");

  var selectors = [
    ".section-head", ".service-card", ".area-card", ".portfolio-card",
    ".step", ".why-item", ".testimonial-card", ".info-box", ".faq-item",
    ".trust-bar-item", ".empty-state"
  ];
  var els = document.querySelectorAll(selectors.join(","));

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: "0px 0px -40px 0px" });

  els.forEach(function (el, i) {
    el.classList.add("reveal");
    el.style.transitionDelay = (i % 4) * 0.06 + "s";
    observer.observe(el);
  });
})();
