/*
 * main.js — Interaktivitas beranda (index.php): peta area interaktif
 * (Leaflet) dan navigasi (menu mobile, tombol smooth-scroll).
 *
 * Konten (info bisnis, area, portofolio, FAQ, JSON-LD) sudah di-render
 * penuh di server oleh index.php langsung dari database — file ini
 * TIDAK lagi bertanggung jawab merender konten, hanya interaktivitas
 * yang memang butuh JS (peta) dan perilaku UI (menu, scroll).
 */
(function () {
  "use strict";

  var PRIORITY_RADIUS_METERS = 4000;

  function renderAreaMap(areas) {
    var mount = document.getElementById("area-map");
    if (!mount || typeof L === "undefined") return;

    var withCoords = (areas || []).filter(function (a) {
      return typeof a.lat === "number" && typeof a.lng === "number";
    });
    if (!withCoords.length) {
      mount.innerHTML = '<p style="padding:20px;color:var(--gray-500)">Peta belum memiliki titik lokasi area. Tambahkan lat/lng area lewat panel admin.</p>';
      return;
    }

    var map = L.map(mount, { scrollWheelZoom: false });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var bounds = [];
    withCoords.forEach(function (area) {
      var latlng = [area.lat, area.lng];
      bounds.push(latlng);

      if (area.priority) {
        L.circle(latlng, {
          radius: PRIORITY_RADIUS_METERS,
          color: "#1eb857",
          weight: 1,
          fillColor: "#25d366",
          fillOpacity: 0.18
        }).addTo(map);
      }

      var popupHtml =
        "<strong>" + area.name + "</strong>" +
        (area.desc ? "<br>" + area.desc : "") +
        (area.priority ? '<br><span style="color:#1eb857;font-weight:600">Zona Prioritas</span>' : "") +
        (area.link ? '<br><a href="' + area.link + '" style="color:#1478c8;font-weight:600">Lihat halaman area &rarr;</a>' : "");

      L.marker(latlng).addTo(map).bindPopup(popupHtml);
    });

    if (bounds.length === 1) {
      map.setView(bounds[0], 12);
    } else {
      map.fitBounds(bounds, { padding: [30, 30] });
    }
  }

  function bindNav() {
    var toggle = document.querySelector(".nav-toggle");
    var links = document.querySelector(".nav-links");
    if (toggle && links) {
      toggle.addEventListener("click", function () {
        links.classList.toggle("open");
      });
      links.querySelectorAll("a, button").forEach(function (el) {
        el.addEventListener("click", function () { links.classList.remove("open"); });
      });
    }
    var year = document.getElementById("year");
    if (year) year.textContent = new Date().getFullYear();

    document.querySelectorAll("[data-scroll]").forEach(function (el) {
      el.addEventListener("click", function () {
        var target = document.getElementById(el.getAttribute("data-scroll"));
        if (target) target.scrollIntoView({ behavior: "smooth" });
      });
    });
  }

  function init() {
    renderAreaMap(window.AREA_MAP_DATA || []);
    bindNav();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
