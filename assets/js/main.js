/*
 * main.js — Merender bagian dinamis halaman publik (index.html) dari data.js,
 * dengan localStorage ("jkrb_data") sebagai lapisan pratinjau perubahan admin.
 * Juga menghasilkan JSON-LD LocalBusiness & FAQPage secara otomatis agar
 * selalu sinkron dengan konten FAQ dan info bisnis yang tampil.
 */
(function () {
  "use strict";

  var STORAGE_KEY = "jkrb_data";

  function getData() {
    var base = window.SITE_DATA || {};
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw) {
        var override = JSON.parse(raw);
        return {
          business: Object.assign({}, base.business, override.business),
          areas: override.areas || base.areas,
          faq: override.faq || base.faq,
          portfolio: override.portfolio || base.portfolio
        };
      }
    } catch (e) {
      /* localStorage tidak tersedia atau data rusak, gunakan default */
    }
    return base;
  }

  function el(tag, cls, html) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (html !== undefined) e.innerHTML = html;
    return e;
  }

  function waLink(business, presetMessage) {
    var msg = encodeURIComponent(presetMessage || business.whatsappMessage || "Halo, saya ingin bertanya.");
    return "https://wa.me/" + business.whatsapp + "?text=" + msg;
  }

  function placeholderSvg(title, color1, color2) {
    var initials = (title || "Kolam Renang").split(" ").slice(0, 2).map(function (w) { return w[0]; }).join("").toUpperCase();
    var svg =
      '<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' + title + '">' +
      '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">' +
      '<stop offset="0%" stop-color="' + color1 + '"/><stop offset="100%" stop-color="' + color2 + '"/>' +
      "</linearGradient></defs>" +
      '<rect width="400" height="300" fill="url(#g)"/>' +
      '<path d="M0 230 Q 50 210 100 230 T 200 230 T 300 230 T 400 230 V300 H0 Z" fill="rgba(255,255,255,0.15)"/>' +
      '<path d="M0 250 Q 50 232 100 250 T 200 250 T 300 250 T 400 250 V300 H0 Z" fill="rgba(255,255,255,0.22)"/>' +
      '<circle cx="200" cy="120" r="46" fill="rgba(255,255,255,0.18)"/>' +
      '<text x="200" y="132" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff" text-anchor="middle">' + initials + "</text>" +
      "</svg>";
    return svg;
  }

  function renderBusinessInfo(business) {
    document.querySelectorAll("[data-field]").forEach(function (node) {
      var field = node.getAttribute("data-field");
      if (business[field] !== undefined) {
        if (node.tagName === "A") {
          if (field === "phoneDisplay") node.textContent = business.phoneDisplay;
          else if (field === "email") node.textContent = business.email;
        } else {
          node.textContent = business[field];
        }
      }
    });

    document.querySelectorAll("a[data-href='tel']").forEach(function (a) {
      a.href = "tel:" + business.phoneHref;
      a.textContent = business.phoneDisplay;
    });
    document.querySelectorAll("a[data-href='mail']").forEach(function (a) {
      a.href = "mailto:" + business.email;
      a.textContent = business.email;
    });
    document.querySelectorAll("a[data-href='wa']").forEach(function (a) {
      a.href = waLink(business);
    });
    document.querySelectorAll("[data-field='addressLine']").forEach(function (n) {
      n.textContent = business.addressLine;
    });
    document.querySelectorAll("[data-field='hours']").forEach(function (n) {
      n.textContent = "Senin–Jumat " + business.hoursWeekday + " • Sabtu–Minggu " + business.hoursWeekend;
    });
    document.querySelectorAll("iframe[data-map]").forEach(function (f) {
      f.src = business.mapsUrl;
    });
    document.querySelectorAll("[data-field='yearsExperience']").forEach(function (n) {
      n.textContent = business.yearsExperience + "+";
    });
    document.querySelectorAll("[data-field='projectsDone']").forEach(function (n) {
      n.textContent = business.projectsDone + "+";
    });
  }

  function renderAreas(areas) {
    var mount = document.getElementById("area-grid");
    if (!mount) return;
    mount.innerHTML = "";
    areas.forEach(function (area) {
      var a = el("a", "area-card");
      a.href = area.link || "index.html#kontak";
      a.innerHTML =
        '<div class="area-chip-row"><span class="area-chip">' + area.name + '</span><span class="arrow">&rarr;</span></div>' +
        "<h3>Kolam Renang " + area.name + "</h3>" +
        "<p>" + area.desc + "</p>";
      mount.appendChild(a);
    });
  }

  var PRIORITY_RADIUS_METERS = 4000;

  function renderAreaMap(areas) {
    var mount = document.getElementById("area-map");
    if (!mount || typeof L === "undefined") return;

    if (mount._leafletMap) {
      mount._leafletMap.remove();
      mount._leafletMap = null;
    }

    var withCoords = (areas || []).filter(function (a) {
      return typeof a.lat === "number" && typeof a.lng === "number";
    });
    if (!withCoords.length) {
      mount.innerHTML = '<p style="padding:20px;color:var(--gray-500)">Peta belum memiliki titik lokasi area. Tambahkan lat/lng area lewat panel admin.</p>';
      return;
    }

    var map = L.map(mount, { scrollWheelZoom: false });
    mount._leafletMap = map;
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

  function renderPortfolio(portfolio) {
    var mount = document.getElementById("portfolio-grid");
    if (!mount) return;
    mount.innerHTML = "";
    portfolio.forEach(function (item) {
      var card = el("div", "portfolio-card");
      var thumbHtml = item.image
        ? '<img src="' + item.image + '" alt="' + item.title + '" loading="lazy">'
        : placeholderSvg(item.title, item.color1 || "#1478c8", item.color2 || "#00b8d9");
      card.innerHTML =
        '<div class="portfolio-thumb">' + thumbHtml + "</div>" +
        '<div class="portfolio-body">' +
        '<span class="tag">' + item.area + "</span>" +
        "<h3>" + item.title + "</h3>" +
        "<p>" + item.desc + "</p>" +
        "</div>";
      mount.appendChild(card);
    });
  }

  function renderFaq(faq) {
    var mount = document.getElementById("faq-list");
    if (!mount) return;
    mount.innerHTML = "";
    faq.forEach(function (item, i) {
      var d = el("details", "faq-item");
      if (i === 0) d.setAttribute("open", "");
      d.innerHTML = "<summary>" + item.q + "</summary><p>" + item.a + "</p>";
      mount.appendChild(d);
    });

    var ld = {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      mainEntity: faq.map(function (item) {
        return {
          "@type": "Question",
          name: item.q,
          acceptedAnswer: { "@type": "Answer", text: item.a }
        };
      })
    };
    injectJsonLd("faq-jsonld", ld);
  }

  function renderLocalBusinessLd(business) {
    var ld = {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "@id": business.domain + "/#business",
      name: business.name,
      description: business.description,
      image: business.domain + "/assets/img/og-image.svg",
      url: business.domain + "/",
      telephone: business.phoneHref,
      email: business.email,
      priceRange: business.priceRange,
      address: {
        "@type": "PostalAddress",
        streetAddress: business.addressLine,
        addressLocality: business.city,
        addressRegion: business.region,
        postalCode: business.postalCode,
        addressCountry: business.country
      },
      areaServed: (window.SITE_DATA.areas || []).map(function (a) { return a.name; }),
      openingHoursSpecification: [
        {
          "@type": "OpeningHoursSpecification",
          dayOfWeek: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
          opens: business.hoursWeekday.split(/–|-/)[0].trim().replace(".", ":"),
          closes: business.hoursWeekday.split(/–|-/)[1].trim().replace(".", ":")
        },
        {
          "@type": "OpeningHoursSpecification",
          dayOfWeek: ["Saturday", "Sunday"],
          opens: business.hoursWeekend.split(/–|-/)[0].trim().replace(".", ":"),
          closes: business.hoursWeekend.split(/–|-/)[1].trim().replace(".", ":")
        }
      ]
    };
    injectJsonLd("localbusiness-jsonld", ld);
  }

  function injectJsonLd(id, obj) {
    var existing = document.getElementById(id);
    if (existing) existing.remove();
    var script = document.createElement("script");
    script.type = "application/ld+json";
    script.id = id;
    script.textContent = JSON.stringify(obj, null, 2);
    document.head.appendChild(script);
  }

  function bindNav() {
    var toggle = document.querySelector(".nav-toggle");
    var links = document.querySelector(".nav-links");
    if (toggle && links) {
      toggle.addEventListener("click", function () {
        links.classList.toggle("open");
      });
      links.querySelectorAll("a").forEach(function (a) {
        a.addEventListener("click", function () { links.classList.remove("open"); });
      });
    }
    var year = document.getElementById("year");
    if (year) year.textContent = new Date().getFullYear();
  }

  function bindWaFloat(business) {
    var wa = document.getElementById("wa-float");
    if (wa) wa.href = waLink(business);
  }

  function init() {
    var data = getData();
    if (!data.business) return;
    renderBusinessInfo(data.business);
    renderAreas(data.areas || []);
    renderAreaMap(data.areas || []);
    renderPortfolio(data.portfolio || []);
    renderFaq(data.faq || []);
    if (!window.SITE_SKIP_AUTO_LD) renderLocalBusinessLd(data.business);
    bindNav();
    bindWaFloat(data.business);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
