/*
 * admin.js — Logika panel admin lokal (client-side) untuk situs statis
 * Jasa Kolam Renang Bogor.
 *
 * Panel ini TIDAK memiliki backend/database. Perubahan disimpan sementara
 * di localStorage browser (kunci "jkrb_data") sehingga:
 *  - Pratinjau langsung terlihat di panel admin & di index.html/area
 *    HANYA pada browser yang sama.
 *  - Untuk membuat perubahan tampil bagi SEMUA pengunjung situs, admin
 *    WAJIB menekan "Unduh data.js" lalu meng-upload file hasil unduhan
 *    ke hosting, menimpa assets/js/data.js yang lama.
 */
(function () {
  "use strict";
  var STORAGE_KEY = "jkrb_data";
  var state = null;

  function clone(obj) { return JSON.parse(JSON.stringify(obj)); }

  function loadState() {
    var base = clone(window.SITE_DATA);
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw) {
        var override = JSON.parse(raw);
        return {
          business: Object.assign({}, base.business, override.business || {}),
          areas: override.areas || base.areas,
          faq: override.faq || base.faq,
          portfolio: override.portfolio || base.portfolio
        };
      }
    } catch (e) { /* ignore corrupt storage */ }
    return base;
  }

  function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    refreshPreview();
    toast("Perubahan disimpan ke pratinjau lokal browser ini.");
  }

  function toast(msg) {
    var t = document.getElementById("toast");
    t.textContent = msg;
    t.classList.add("show");
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.classList.remove("show"); }, 3200);
  }

  function refreshPreview() {
    var frame = document.getElementById("preview-frame");
    if (frame) frame.contentWindow.location.reload();
  }

  /* ---------- Tabs ---------- */
  function initTabs() {
    var buttons = document.querySelectorAll(".admin-nav button[data-tab]");
    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        buttons.forEach(function (b) { b.classList.remove("active"); });
        document.querySelectorAll(".admin-panel").forEach(function (p) { p.classList.remove("active"); });
        btn.classList.add("active");
        document.getElementById("panel-" + btn.dataset.tab).classList.add("active");
        if (btn.dataset.tab === "area" && pickerMap) {
          setTimeout(function () { pickerMap.invalidateSize(); }, 0);
        }
      });
    });
  }

  /* ---------- Business info ---------- */
  var businessFields = [
    "name", "tagline", "description", "phoneDisplay", "phoneHref", "whatsapp",
    "whatsappMessage", "email", "addressLine", "city", "region", "postalCode",
    "hoursWeekday", "hoursWeekend", "mapsQuery", "priceRange", "yearsExperience",
    "projectsDone", "domain"
  ];

  function renderBusinessForm() {
    var form = document.getElementById("business-form");
    businessFields.forEach(function (f) {
      var input = form.querySelector("[name='" + f + "']");
      if (input) input.value = state.business[f] !== undefined ? state.business[f] : "";
    });
  }

  function bindBusinessForm() {
    var form = document.getElementById("business-form");
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      businessFields.forEach(function (f) {
        var input = form.querySelector("[name='" + f + "']");
        if (input) state.business[f] = input.value;
      });
      persist();
    });
    document.getElementById("btn-build-map").addEventListener("click", function () {
      var q = form.querySelector("[name='mapsQuery']").value.trim();
      if (!q) { toast("Isi alamat/nama lokasi dahulu."); return; }
      state.business.mapsUrl = "https://maps.google.com/maps?q=" + encodeURIComponent(q) + "&output=embed";
      toast("Peta dibuat dari alamat: " + q);
    });
  }

  /* ---------- Areas ---------- */
  var PRIORITY_RADIUS_METERS = 4000;
  var BOGOR_DEFAULT_CENTER = [-6.595, 106.816];
  var pickerMap = null;
  var pickerLayers = [];
  var activeAreaIndex = null;

  function renderAreas() {
    var mount = document.getElementById("area-list");
    mount.innerHTML = "";
    state.areas.forEach(function (area, idx) {
      var card = document.createElement("div");
      card.className = "admin-card";
      var hasCoords = typeof area.lat === "number" && typeof area.lng === "number";
      card.innerHTML =
        '<div class="admin-card-head"><h3>Area #' + (idx + 1) + '</h3>' +
        '<button type="button" class="btn btn-sm btn-danger" data-remove="' + idx + '">Hapus</button></div>' +
        '<div class="form-grid">' +
        field("Nama Area", "text", "name", area.name) +
        field("Link Halaman", "text", "link", area.link) +
        '</div>' +
        field("Deskripsi Singkat", "textarea", "desc", area.desc) +
        '<div class="form-grid">' +
        '<div class="field"><label>Latitude</label><input type="number" step="any" data-field="lat" value="' + (hasCoords ? area.lat : "") + '" placeholder="-6.595"></div>' +
        '<div class="field"><label>Longitude</label><input type="number" step="any" data-field="lng" value="' + (hasCoords ? area.lng : "") + '" placeholder="106.816"></div>' +
        '</div>' +
        '<div class="form-grid">' +
        '<div class="field"><label>Zona Prioritas?</label><select data-field="priority"><option value="true"' + (area.priority ? " selected" : "") + '>Ya</option><option value="false"' + (!area.priority ? " selected" : "") + '>Tidak</option></select></div>' +
        '<div class="field"><label>&nbsp;</label><button type="button" class="btn btn-secondary btn-sm" data-pick="' + idx + '">📍 Pilih Lokasi di Peta</button></div>' +
        '</div>' +
        '<small class="picker-hint">' + (hasCoords ? "" : "Belum ada titik lokasi. Klik \"Pilih Lokasi di Peta\" lalu klik titiknya di peta di atas, atau isi Latitude/Longitude manual.") + '</small>';

      card.querySelectorAll("[data-field]").forEach(function (inp) {
        inp.addEventListener(inp.tagName === "SELECT" ? "change" : "input", function () {
          var f = inp.dataset.field;
          if (f === "lat" || f === "lng") {
            state.areas[idx][f] = inp.value === "" ? null : parseFloat(inp.value);
          } else if (f === "priority") {
            state.areas[idx][f] = inp.value === "true";
          } else {
            state.areas[idx][f] = inp.value;
          }
          refreshPickerMarkers();
        });
      });
      card.querySelector("[data-remove]").addEventListener("click", function () {
        if (activeAreaIndex === idx) activeAreaIndex = null;
        state.areas.splice(idx, 1);
        renderAreas();
      });
      card.querySelector("[data-pick]").addEventListener("click", function () {
        setActiveArea(idx);
      });
      mount.appendChild(card);
    });
    refreshPickerMarkers();
  }

  function field(label, type, name, value) {
    value = (value || "").toString().replace(/"/g, "&quot;");
    if (type === "textarea") {
      return '<div class="field full"><label>' + label + '</label><textarea data-field="' + name + '">' + value + "</textarea></div>";
    }
    return '<div class="field"><label>' + label + '</label><input type="' + type + '" data-field="' + name + '" value="' + value + '"></div>';
  }

  function setActiveArea(idx) {
    activeAreaIndex = idx;
    var status = document.getElementById("picker-status");
    if (status) status.textContent = "Klik titik lokasi di peta untuk area: " + (state.areas[idx].name || "(tanpa nama)");
    var mapEl = document.getElementById("admin-picker-map");
    if (mapEl) mapEl.scrollIntoView({ behavior: "smooth", block: "center" });
    if (pickerMap) pickerMap.invalidateSize();
  }

  function initPickerMap() {
    var mapEl = document.getElementById("admin-picker-map");
    if (!mapEl || typeof L === "undefined") return;
    pickerMap = L.map(mapEl).setView(BOGOR_DEFAULT_CENTER, 11);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(pickerMap);
    pickerMap.on("click", function (e) {
      if (activeAreaIndex === null || !state.areas[activeAreaIndex]) {
        toast("Pilih dulu area yang ingin diatur titiknya (tombol \"Pilih Lokasi di Peta\").");
        return;
      }
      state.areas[activeAreaIndex].lat = Math.round(e.latlng.lat * 100000) / 100000;
      state.areas[activeAreaIndex].lng = Math.round(e.latlng.lng * 100000) / 100000;
      renderAreas();
      toast("Titik lokasi diatur untuk area: " + state.areas[activeAreaIndex].name);
    });
  }

  function refreshPickerMarkers() {
    if (!pickerMap) return;
    pickerLayers.forEach(function (layer) { pickerMap.removeLayer(layer); });
    pickerLayers = [];
    state.areas.forEach(function (area, idx) {
      if (typeof area.lat !== "number" || typeof area.lng !== "number") return;
      var latlng = [area.lat, area.lng];
      if (area.priority) {
        var circle = L.circle(latlng, {
          radius: PRIORITY_RADIUS_METERS,
          color: "#1eb857", weight: 1, fillColor: "#25d366", fillOpacity: 0.18
        }).addTo(pickerMap);
        pickerLayers.push(circle);
      }
      var marker = L.marker(latlng, {
        opacity: idx === activeAreaIndex ? 1 : 0.75
      }).addTo(pickerMap).bindPopup("<strong>" + area.name + "</strong>");
      marker.on("click", function () { setActiveArea(idx); });
      pickerLayers.push(marker);
    });
  }

  function bindAreaButtons() {
    document.getElementById("btn-add-area").addEventListener("click", function () {
      state.areas.push({ name: "Area Baru", link: "index.html#kontak", desc: "Deskripsi area layanan.", lat: null, lng: null, priority: true });
      renderAreas();
    });
    document.getElementById("btn-save-areas").addEventListener("click", persist);
  }

  /* ---------- FAQ ---------- */
  function renderFaq() {
    var mount = document.getElementById("faq-list-admin");
    mount.innerHTML = "";
    state.faq.forEach(function (item, idx) {
      var card = document.createElement("div");
      card.className = "admin-card";
      card.innerHTML =
        '<div class="admin-card-head"><h3>FAQ #' + (idx + 1) + '</h3>' +
        '<button type="button" class="btn btn-sm btn-danger" data-remove="' + idx + '">Hapus</button></div>' +
        field("Pertanyaan", "text", "q", item.q) +
        field("Jawaban", "textarea", "a", item.a);
      card.querySelectorAll("[data-field]").forEach(function (inp) {
        inp.addEventListener("input", function () {
          state.faq[idx][inp.dataset.field] = inp.value;
        });
      });
      card.querySelector("[data-remove]").addEventListener("click", function () {
        state.faq.splice(idx, 1);
        renderFaq();
      });
      mount.appendChild(card);
    });
  }

  function bindFaqButtons() {
    document.getElementById("btn-add-faq").addEventListener("click", function () {
      state.faq.push({ q: "Pertanyaan baru?", a: "Jawaban untuk pertanyaan baru." });
      renderFaq();
    });
    document.getElementById("btn-save-faq").addEventListener("click", persist);
  }

  /* ---------- Portfolio / Foto ---------- */
  function renderPortfolio() {
    var mount = document.getElementById("portfolio-list-admin");
    mount.innerHTML = "";
    state.portfolio.forEach(function (item, idx) {
      var card = document.createElement("div");
      card.className = "admin-card";
      var thumbHtml = item.image
        ? '<img src="' + item.image + '" alt="">'
        : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--blue-600);font-weight:700;background:linear-gradient(135deg,' + (item.color1 || "#1478c8") + "," + (item.color2 || "#00b8d9") + ')">Placeholder</div>';
      card.innerHTML =
        '<div class="admin-card-head"><h3>Foto #' + (idx + 1) + '</h3>' +
        '<button type="button" class="btn btn-sm btn-danger" data-remove="' + idx + '">Hapus</button></div>' +
        '<div class="thumb-preview" data-thumb>' + thumbHtml + "</div>" +
        '<div class="form-grid">' +
        field("Judul", "text", "title", item.title) +
        field("Area", "text", "area", item.area) +
        "</div>" +
        field("Deskripsi", "textarea", "desc", item.desc) +
        field("URL Gambar (opsional)", "text", "image", item.image || "") +
        '<div class="field"><label>Atau unggah dari perangkat</label><input type="file" accept="image/*" data-upload="' + idx + '"><small>Gambar diunggah disimpan sementara di browser ini (base64) hanya untuk pratinjau. Untuk produksi, gunakan hosting gambar dan isi kolom URL di atas.</small></div>';

      card.querySelectorAll("[data-field]").forEach(function (inp) {
        inp.addEventListener("input", function () {
          state.portfolio[idx][inp.dataset.field] = inp.value;
          if (inp.dataset.field === "image") updateThumb(card, state.portfolio[idx]);
        });
      });
      card.querySelector("[data-upload]").addEventListener("change", function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function () {
          state.portfolio[idx].image = reader.result;
          card.querySelector("[data-field='image']").value = reader.result;
          updateThumb(card, state.portfolio[idx]);
          toast("Gambar dimuat untuk pratinjau lokal.");
        };
        reader.readAsDataURL(file);
      });
      card.querySelector("[data-remove]").addEventListener("click", function () {
        state.portfolio.splice(idx, 1);
        renderPortfolio();
      });
      mount.appendChild(card);
    });
  }

  function updateThumb(card, item) {
    var thumb = card.querySelector("[data-thumb]");
    thumb.innerHTML = item.image
      ? '<img src="' + item.image + '" alt="">'
      : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--blue-600);font-weight:700;background:linear-gradient(135deg,' + (item.color1 || "#1478c8") + "," + (item.color2 || "#00b8d9") + ')">Placeholder</div>';
  }

  function bindPortfolioButtons() {
    document.getElementById("btn-add-photo").addEventListener("click", function () {
      state.portfolio.push({ title: "Proyek Baru", area: "Bogor Kota", desc: "Deskripsi singkat proyek.", color1: "#1478c8", color2: "#00b8d9", image: null });
      renderPortfolio();
    });
    document.getElementById("btn-save-portfolio").addEventListener("click", persist);
  }

  /* ---------- Export / Import ---------- */
  function download(filename, text) {
    var blob = new Blob([text], { type: "text/plain;charset=utf-8" });
    var url = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = url; a.download = filename;
    document.body.appendChild(a); a.click(); a.remove();
    URL.revokeObjectURL(url);
  }

  function bindExportImport() {
    document.getElementById("btn-download-datajs").addEventListener("click", function () {
      var content =
        "/*\n * data.js — dihasilkan otomatis oleh admin.html pada " + new Date().toLocaleString("id-ID") + "\n" +
        " * Upload file ini ke hosting untuk menimpa assets/js/data.js agar\n" +
        " * perubahan tampil bagi seluruh pengunjung situs.\n */\nwindow.SITE_DATA = " + JSON.stringify(state, null, 2) + ";\n";
      download("data.js", content);
      toast("data.js diunduh. Upload ke hosting untuk menerapkan perubahan.");
    });

    document.getElementById("btn-export-json").addEventListener("click", function () {
      document.getElementById("json-io").value = JSON.stringify(state, null, 2);
      toast("Data saat ini ditampilkan sebagai JSON di bawah.");
    });

    document.getElementById("btn-import-json").addEventListener("click", function () {
      try {
        var parsed = JSON.parse(document.getElementById("json-io").value);
        state = parsed;
        persist();
        renderAll();
        toast("Data berhasil diimpor.");
      } catch (e) {
        toast("Gagal mengimpor: JSON tidak valid.");
      }
    });

    document.getElementById("btn-reset").addEventListener("click", function () {
      if (!confirm("Kembalikan semua data ke nilai default (bawaan situs)? Perubahan lokal akan hilang.")) return;
      localStorage.removeItem(STORAGE_KEY);
      state = clone(window.SITE_DATA);
      renderAll();
      refreshPreview();
      toast("Data dikembalikan ke default.");
    });
  }

  function renderAll() {
    renderBusinessForm();
    renderAreas();
    renderFaq();
    renderPortfolio();
  }

  function init() {
    state = loadState();
    initTabs();
    initPickerMap();
    bindBusinessForm();
    bindAreaButtons();
    bindFaqButtons();
    bindPortfolioButtons();
    bindExportImport();
    renderAll();
  }

  document.addEventListener("DOMContentLoaded", init);
})();
