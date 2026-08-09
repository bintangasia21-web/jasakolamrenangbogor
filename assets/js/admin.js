/*
 * admin.js — Logika panel admin (client-side) untuk situs Jasa Kolam
 * Renang Bogor.
 *
 * Semua bagian (Info Bisnis, Area, FAQ, Foto/Portofolio) sudah live:
 * tombol "Simpan..." di tiap tab menulis langsung ke database MySQL
 * lewat save-data.php, dan get-data.php membacanya kembali untuk semua
 * pengunjung — tidak perlu unduh/upload data.js manual lagi.
 *
 * localStorage ("jkrb_data") tetap dipakai sebagai lapisan pratinjau
 * cepat di browser admin (supaya iframe pratinjau langsung ter-update
 * sebelum sempat tersimpan ke server), bukan sumber kebenaran utama.
 */
(function () {
  "use strict";
  var STORAGE_KEY = "jkrb_data";
  var state = null;

  function clone(obj) { return JSON.parse(JSON.stringify(obj)); }

  function fetchLiveBase() {
    return fetch("get-data.php", { cache: "no-store" })
      .then(function (r) {
        if (!r.ok) throw new Error("get-data.php tidak tersedia");
        return r.json();
      })
      .catch(function () {
        return clone(window.SITE_DATA);
      });
  }

  function loadState(base) {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw) {
        var override = JSON.parse(raw);
        return {
          business: Object.assign({}, base.business, override.business || {}),
          areas: override.areas || base.areas,
          faq: override.faq || base.faq,
          portfolio: override.portfolio || base.portfolio,
          testimonials: override.testimonials || base.testimonials || [],
          areaPhotos: override.areaPhotos || base.areaPhotos || []
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
    "projectsDone", "domain",
    "yearFounded", "activeCustomers", "employeeCount", "monthlyRevenue"
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
      syncSectionLive("business", state.business);
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
        '<small class="picker-hint">' + (hasCoords ? "" : "Belum ada titik lokasi. Klik \"Pilih Lokasi di Peta\" lalu klik titiknya di peta di atas, atau isi Latitude/Longitude manual.") + '</small>' +
        '<div class="admin-card-head" style="margin-top:16px"><h4 style="margin:0">Galeri Foto Area Ini</h4></div>' +
        '<div data-gallery></div>' +
        '<div class="field"><label>Unggah foto untuk area ini (langsung live)</label><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-upload-area="' + idx + '"><small data-upload-status>Foto khusus area ini, terpisah dari galeri Portofolio.</small></div>';

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
      renderAreaGallery(card.querySelector("[data-gallery]"), area.link);
      card.querySelector("[data-upload-area]").addEventListener("change", function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var status = card.querySelector("[data-upload-status]");
        var fd = new FormData();
        fd.append("photo", file);
        status.textContent = "Mengunggah...";
        fetch("upload-photo.php", { method: "POST", body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data.success) {
              status.textContent = data.message || "Upload gagal.";
              toast(data.message || "Upload gagal.");
              return;
            }
            state.areaPhotos.push({ areaLink: state.areas[idx].link, photo: data.url, caption: "" });
            status.textContent = "Foto tersimpan. Mempublikasikan ke situs live...";
            return syncSectionLive("area_photos", state.areaPhotos).then(function () {
              status.textContent = "Foto tersimpan & sudah live di situs.";
              renderAreaGallery(card.querySelector("[data-gallery]"), state.areas[idx].link);
            });
          })
          .catch(function () {
            status.textContent = "Gagal menghubungi server saat upload.";
            toast("Gagal menghubungi server saat upload.");
          });
      });
      mount.appendChild(card);
    });
    refreshPickerMarkers();
  }

  function renderAreaGallery(container, areaLink) {
    if (!container) return;
    container.innerHTML = "";
    var items = state.areaPhotos.filter(function (p) { return p.areaLink === areaLink; });
    if (items.length === 0) {
      container.innerHTML = '<small class="picker-hint">Belum ada foto untuk area ini.</small>';
      return;
    }
    items.forEach(function (photoItem) {
      var globalIdx = state.areaPhotos.indexOf(photoItem);
      var row = document.createElement("div");
      row.className = "thumb-preview";
      row.style.cssText = "display:flex;align-items:center;gap:10px;height:auto;padding:8px;margin-bottom:8px";
      row.innerHTML =
        '<img src="' + photoItem.photo + '" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:8px;flex:none">' +
        '<input type="text" data-caption placeholder="Keterangan foto (opsional)" value="' + (photoItem.caption || "").toString().replace(/"/g, "&quot;") + '" style="flex:1;padding:8px 10px;border:1px solid var(--gray-300);border-radius:8px">' +
        '<button type="button" class="btn btn-sm btn-danger" data-delete-photo>Hapus</button>';
      row.querySelector("[data-caption]").addEventListener("blur", function (e) {
        state.areaPhotos[globalIdx].caption = e.target.value;
        syncSectionLive("area_photos", state.areaPhotos);
      });
      row.querySelector("[data-delete-photo]").addEventListener("click", function () {
        state.areaPhotos.splice(globalIdx, 1);
        syncSectionLive("area_photos", state.areaPhotos);
        renderAreaGallery(container, areaLink);
      });
      container.appendChild(row);
    });
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
      state.areas.push({ name: "Area Baru", link: "/", desc: "Deskripsi area layanan.", lat: null, lng: null, priority: true });
      renderAreas();
    });
    document.getElementById("btn-save-areas").addEventListener("click", function () {
      persist();
      syncSectionLive("areas", state.areas);
    });
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
    document.getElementById("btn-save-faq").addEventListener("click", function () {
      persist();
      syncSectionLive("faq", state.faq);
    });
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
        '<small class="picker-hint">Isi dengan tautan gambar biasa (https://... atau assets/...). JANGAN tempel kode base64/data:image di sini — gunakan tombol unggah di bawah.</small>' +
        '<div class="field"><label>Atau unggah foto (langsung live)</label><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-upload="' + idx + '"><small data-upload-status>Foto yang diunggah langsung tersimpan di server dan tampil bagi semua pengunjung.</small></div>';

      var MAX_IMAGE_URL_LENGTH = 500;
      card.querySelectorAll("[data-field]").forEach(function (inp) {
        inp.addEventListener("input", function () {
          if (inp.dataset.field === "image" && inp.value.length > MAX_IMAGE_URL_LENGTH) {
            inp.value = "";
            toast("Teks terlalu panjang untuk kolom URL (kemungkinan kode base64). Gunakan tombol \"Atau unggah foto\" di bawahnya untuk mengunggah file.");
            return;
          }
          state.portfolio[idx][inp.dataset.field] = inp.value;
          if (inp.dataset.field === "image") updateThumb(card, state.portfolio[idx]);
        });
      });
      card.querySelector("[data-upload]").addEventListener("change", function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var status = card.querySelector("[data-upload-status]");
        var fd = new FormData();
        fd.append("photo", file);
        status.textContent = "Mengunggah...";
        fetch("upload-photo.php", { method: "POST", body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data.success) {
              status.textContent = data.message || "Upload gagal.";
              toast(data.message || "Upload gagal.");
              return;
            }
            state.portfolio[idx].image = data.url;
            card.querySelector("[data-field='image']").value = data.url;
            updateThumb(card, state.portfolio[idx]);
            status.textContent = "Foto tersimpan. Mempublikasikan ke situs live...";
            return syncSectionLive("portfolio", state.portfolio).then(function () {
              status.textContent = "Foto tersimpan & sudah live di situs.";
            });
          })
          .catch(function () {
            status.textContent = "Gagal menghubungi server saat upload.";
            toast("Gagal menghubungi server saat upload.");
          });
      });
      card.querySelector("[data-remove]").addEventListener("click", function () {
        state.portfolio.splice(idx, 1);
        renderPortfolio();
        syncSectionLive("portfolio", state.portfolio);
      });
      mount.appendChild(card);
    });
  }

  function syncSectionLive(section, payload) {
    var fd = new FormData();
    fd.append("section", section);
    fd.append("payload", JSON.stringify(payload));
    return fetch("save-data.php", { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        toast(data.message || (data.success ? "Perubahan live diperbarui." : "Gagal memperbarui data live."));
        refreshPreview();
        return data;
      })
      .catch(function () {
        toast("Gagal menghubungi server untuk publikasi live.");
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
    document.getElementById("btn-save-portfolio").addEventListener("click", function () {
      persist();
      syncSectionLive("portfolio", state.portfolio);
    });
  }

  /* ---------- Testimonial ---------- */
  function renderTestimonials() {
    var mount = document.getElementById("testimonial-list-admin");
    mount.innerHTML = "";
    state.testimonials.forEach(function (item, idx) {
      var card = document.createElement("div");
      card.className = "admin-card";
      var thumbHtml = item.photo
        ? '<img src="' + item.photo + '" alt="">'
        : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--blue-600);font-weight:700;background:linear-gradient(135deg,#1478c8,#00b8d9)">Tanpa Foto</div>';
      card.innerHTML =
        '<div class="admin-card-head"><h3>Testimonial #' + (idx + 1) + '</h3>' +
        '<button type="button" class="btn btn-sm btn-danger" data-remove="' + idx + '">Hapus</button></div>' +
        '<div class="thumb-preview" data-thumb>' + thumbHtml + "</div>" +
        '<div class="form-grid">' +
        field("Nama Pelanggan", "text", "name", item.name) +
        field("Area", "text", "area", item.area) +
        "</div>" +
        '<div class="form-grid">' +
        field("Layanan", "text", "service", item.service) +
        field("Tanggal (opsional)", "date", "date", item.date) +
        "</div>" +
        field("Isi Testimoni", "textarea", "content", item.content) +
        '<div class="field"><label>Status</label><select data-field="status">' +
        '<option value="draft"' + (item.status !== "published" ? " selected" : "") + '>Draft</option>' +
        '<option value="published"' + (item.status === "published" ? " selected" : "") + '>Diterbitkan (Live)</option>' +
        "</select></div>" +
        '<div class="field"><label>Atau unggah foto pelanggan (opsional, langsung live)</label><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-upload="' + idx + '"><small data-upload-status>Foto opsional — testimoni tetap bisa diterbitkan tanpa foto.</small></div>';

      card.querySelectorAll("[data-field]").forEach(function (inp) {
        inp.addEventListener("input", function () {
          state.testimonials[idx][inp.dataset.field] = inp.value;
        });
        inp.addEventListener("change", function () {
          state.testimonials[idx][inp.dataset.field] = inp.value;
        });
      });
      card.querySelector("[data-upload]").addEventListener("change", function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var status = card.querySelector("[data-upload-status]");
        var fd = new FormData();
        fd.append("photo", file);
        status.textContent = "Mengunggah...";
        fetch("upload-photo.php", { method: "POST", body: fd })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data.success) {
              status.textContent = data.message || "Upload gagal.";
              toast(data.message || "Upload gagal.");
              return;
            }
            state.testimonials[idx].photo = data.url;
            card.querySelector("[data-thumb]").innerHTML = '<img src="' + data.url + '" alt="">';
            status.textContent = "Foto tersimpan. Mempublikasikan ke situs live...";
            return syncSectionLive("testimonials", state.testimonials).then(function () {
              status.textContent = "Foto tersimpan & sudah live di situs.";
            });
          })
          .catch(function () {
            status.textContent = "Gagal menghubungi server saat upload.";
            toast("Gagal menghubungi server saat upload.");
          });
      });
      card.querySelector("[data-remove]").addEventListener("click", function () {
        state.testimonials.splice(idx, 1);
        renderTestimonials();
        syncSectionLive("testimonials", state.testimonials);
      });
      mount.appendChild(card);
    });
  }

  function bindTestimonialButtons() {
    document.getElementById("btn-add-testimonial").addEventListener("click", function () {
      state.testimonials.push({ name: "", area: "", service: "", content: "", photo: null, date: "", status: "draft" });
      renderTestimonials();
    });
    document.getElementById("btn-save-testimonial").addEventListener("click", function () {
      persist();
      syncSectionLive("testimonials", state.testimonials);
    });
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
    renderTestimonials();
  }

  /* ---------- Keamanan (ganti password) ---------- */
  function bindPasswordForm() {
    var form = document.getElementById("password-form");
    if (!form) return;
    var msg = document.getElementById("password-msg");
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var current = form.current_password.value;
      var next = form.new_password.value;
      var confirmVal = form.new_password_confirm.value;

      if (next !== confirmVal) {
        msg.style.color = "#c0392b";
        msg.textContent = "Password baru dan konfirmasi tidak sama.";
        return;
      }

      var submitBtn = form.querySelector("button[type=submit]");
      submitBtn.disabled = true;
      msg.style.color = "var(--gray-600)";
      msg.textContent = "Menyimpan...";

      fetch("change-password.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "current_password=" + encodeURIComponent(current) + "&new_password=" + encodeURIComponent(next)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          msg.style.color = data.success ? "#1eb857" : "#c0392b";
          msg.textContent = data.message;
          if (data.success) form.reset();
        })
        .catch(function () {
          msg.style.color = "#c0392b";
          msg.textContent = "Gagal menghubungi server. Coba lagi.";
        })
        .finally(function () { submitBtn.disabled = false; });
    });
  }

  /* ---------- Halaman SEO / Layanan / Artikel (semua berbasis tabel "pages") ---------- */
  var pagesState = [];
  var PAGES_PER_PAGE = 50;
  var currentPageFaq = [];
  var typeLabels = { area: "Area", combo: "Kombinasi", service: "Layanan", article: "Artikel", portfolio: "Portofolio", page: "Pendukung" };
  // Konteks editor halaman aktif saat ini -- dipakai supaya satu form
  // #page-editor bisa dipanggil dari 3 tab berbeda (Halaman Kombinasi,
  // Layanan, Artikel) dan tahu tipe mana yang harus di-preset untuk baris
  // baru serta tabel mana yang perlu di-refresh setelah simpan/hapus.
  var pageEditorContext = { presetType: null, onDone: null };

  function loadPagesList() {
    return fetch("get-pages.php", { cache: "no-store" })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          pagesState = data.pages;
          renderPagesTable();
          layananTable.reset();
          artikelTable.reset();
        } else {
          toast(data.message || "Gagal memuat daftar halaman.");
        }
      })
      .catch(function () { toast("Gagal menghubungi server."); });
  }

  function deletePageRow(id, onDone) {
    if (!confirm("Hapus halaman ini? Tindakan ini tidak bisa dibatalkan.")) return;
    var fd = new FormData();
    fd.append("id", id);
    fetch("delete-page.php", { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        toast(data.message || (data.success ? "Halaman dihapus." : "Gagal menghapus."));
        if (data.success) {
          loadPagesList();
          if (onDone) onDone();
        }
      })
      .catch(function () { toast("Gagal menghubungi server."); });
  }

  var pagesPageIndex = 0;
  function getFilteredPages() {
    var q = (document.getElementById("pages-search").value || "").toLowerCase();
    var typeFilter = document.getElementById("pages-filter-type").value;
    var statusFilter = document.getElementById("pages-filter-status").value;
    return pagesState.filter(function (p) {
      if (typeFilter && p.type !== typeFilter) return false;
      if (statusFilter && p.status !== statusFilter) return false;
      if (q && (p.title || "").toLowerCase().indexOf(q) === -1 && (p.url_path || "").toLowerCase().indexOf(q) === -1 && (p.target_keyword || "").toLowerCase().indexOf(q) === -1) return false;
      return true;
    });
  }

  function renderPagesTable() {
    var filtered = getFilteredPages();
    document.getElementById("pages-count").textContent = filtered.length + " dari " + pagesState.length + " halaman";

    var totalPages = Math.max(1, Math.ceil(filtered.length / PAGES_PER_PAGE));
    if (pagesPageIndex >= totalPages) pagesPageIndex = totalPages - 1;
    var start = pagesPageIndex * PAGES_PER_PAGE;
    var pageItems = filtered.slice(start, start + PAGES_PER_PAGE);

    var tbody = document.getElementById("pages-table-body");
    tbody.innerHTML = "";
    pageItems.forEach(function (p) {
      var tr = document.createElement("tr");
      tr.innerHTML =
        "<td>" + (p.tier || "-") + "</td>" +
        "<td title=\"" + (p.title || "").replace(/"/g, "&quot;") + "\">" + (p.title || "") + "</td>" +
        "<td>" + (p.url_path || "") + "</td>" +
        "<td>" + (p.target_keyword || "") + "</td>" +
        "<td><span class=\"status-badge " + p.status + "\">" + (p.status === "published" ? "Live" : "Draft") + "</span></td>" +
        "<td><button type=\"button\" class=\"btn btn-sm btn-secondary\" data-edit-page=\"" + p.id + "\">Edit</button> <button type=\"button\" class=\"btn btn-sm btn-danger\" data-del-page=\"" + p.id + "\">Hapus</button></td>";
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll("[data-edit-page]").forEach(function (btn) {
      btn.addEventListener("click", function () { openPageEditor(btn.dataset.editPage, { presetType: null, onDone: renderPagesTable }); });
    });
    tbody.querySelectorAll("[data-del-page]").forEach(function (btn) {
      btn.addEventListener("click", function () { deletePageRow(btn.dataset.delPage, renderPagesTable); });
    });

    var pager = document.getElementById("pages-pagination");
    pager.innerHTML = "";
    if (totalPages > 1) {
      var info = document.createElement("span");
      info.style.cssText = "align-self:center;color:var(--gray-600);font-size:.85rem";
      info.textContent = "Halaman " + (pagesPageIndex + 1) + " / " + totalPages;
      var prev = document.createElement("button");
      prev.type = "button"; prev.className = "btn btn-sm btn-ghost"; prev.textContent = "← Sebelumnya";
      prev.disabled = pagesPageIndex === 0;
      prev.addEventListener("click", function () { pagesPageIndex--; renderPagesTable(); });
      var next = document.createElement("button");
      next.type = "button"; next.className = "btn btn-sm btn-ghost"; next.textContent = "Berikutnya →";
      next.disabled = pagesPageIndex >= totalPages - 1;
      next.addEventListener("click", function () { pagesPageIndex++; renderPagesTable(); });
      pager.appendChild(prev); pager.appendChild(info); pager.appendChild(next);
    }
  }

  // Tabel yang difilter ke satu tipe saja (dipakai tab Layanan & Artikel).
  // Sumber datanya tetap array pagesState yang sama (dimuat sekali lewat
  // loadPagesList()) -- cuma ditampilkan dengan filter type tetap, search,
  // dan pagination sendiri-sendiri per tab.
  function makeScopedPagesTable(type, ids) {
    var pageIndex = 0;
    function getFiltered() {
      var q = (document.getElementById(ids.search).value || "").toLowerCase();
      var statusFilter = document.getElementById(ids.statusFilter).value;
      return pagesState.filter(function (p) {
        if (p.type !== type) return false;
        if (statusFilter && p.status !== statusFilter) return false;
        if (q && (p.title || "").toLowerCase().indexOf(q) === -1 && (p.url_path || "").toLowerCase().indexOf(q) === -1 && (p.target_keyword || "").toLowerCase().indexOf(q) === -1) return false;
        return true;
      });
    }
    function render() {
      var filtered = getFiltered();
      document.getElementById(ids.count).textContent = filtered.length + " halaman";
      var totalPages = Math.max(1, Math.ceil(filtered.length / PAGES_PER_PAGE));
      if (pageIndex >= totalPages) pageIndex = totalPages - 1;
      var start = pageIndex * PAGES_PER_PAGE;
      var pageItems = filtered.slice(start, start + PAGES_PER_PAGE);
      var tbody = document.getElementById(ids.tbody);
      tbody.innerHTML = "";
      pageItems.forEach(function (p) {
        var tr = document.createElement("tr");
        var coverCell = ids.showCover ? ("<td>" + (p.cover_image ? '<img src="' + p.cover_image + '" alt="" style="width:48px;height:34px;object-fit:cover;border-radius:4px">' : '—') + "</td>") : "";
        tr.innerHTML = coverCell +
          "<td title=\"" + (p.title || "").replace(/"/g, "&quot;") + "\">" + (p.title || "") + "</td>" +
          "<td>" + (p.url_path || "") + "</td>" +
          "<td>" + (p.target_keyword || "") + "</td>" +
          "<td><span class=\"status-badge " + p.status + "\">" + (p.status === "published" ? "Live" : "Draft") + "</span></td>" +
          "<td><button type=\"button\" class=\"btn btn-sm btn-secondary\" data-edit=\"" + p.id + "\">Edit</button> <button type=\"button\" class=\"btn btn-sm btn-danger\" data-del=\"" + p.id + "\">Hapus</button></td>";
        tbody.appendChild(tr);
      });
      tbody.querySelectorAll("[data-edit]").forEach(function (btn) {
        btn.addEventListener("click", function () { openPageEditor(btn.dataset.edit, { presetType: type, onDone: render }); });
      });
      tbody.querySelectorAll("[data-del]").forEach(function (btn) {
        btn.addEventListener("click", function () { deletePageRow(btn.dataset.del, render); });
      });

      var pager = document.getElementById(ids.pagination);
      pager.innerHTML = "";
      if (totalPages > 1) {
        var info = document.createElement("span");
        info.style.cssText = "align-self:center;color:var(--gray-600);font-size:.85rem";
        info.textContent = "Halaman " + (pageIndex + 1) + " / " + totalPages;
        var prev = document.createElement("button");
        prev.type = "button"; prev.className = "btn btn-sm btn-ghost"; prev.textContent = "← Sebelumnya";
        prev.disabled = pageIndex === 0;
        prev.addEventListener("click", function () { pageIndex--; render(); });
        var next = document.createElement("button");
        next.type = "button"; next.className = "btn btn-sm btn-ghost"; next.textContent = "Berikutnya →";
        next.disabled = pageIndex >= totalPages - 1;
        next.addEventListener("click", function () { pageIndex++; render(); });
        pager.appendChild(prev); pager.appendChild(info); pager.appendChild(next);
      }
    }
    return { render: render, reset: function () { pageIndex = 0; render(); } };
  }

  var layananTable = makeScopedPagesTable("service", {
    search: "layanan-search", statusFilter: "layanan-filter-status", count: "layanan-count",
    tbody: "layanan-table-body", pagination: "layanan-pagination", showCover: false
  });
  var artikelTable = makeScopedPagesTable("article", {
    search: "artikel-search", statusFilter: "artikel-filter-status", count: "artikel-count",
    tbody: "artikel-table-body", pagination: "artikel-pagination", showCover: true
  });

  function renderPeFaq() {
    var mount = document.getElementById("pe-faq-list");
    mount.innerHTML = "";
    currentPageFaq.forEach(function (item, idx) {
      var card = document.createElement("div");
      card.className = "admin-card";
      card.innerHTML =
        '<div class="admin-card-head"><h3>FAQ #' + (idx + 1) + '</h3><button type="button" class="btn btn-sm btn-danger" data-remove-faq="' + idx + '">Hapus</button></div>' +
        field("Pertanyaan", "text", "q", item.q) +
        field("Jawaban", "textarea", "a", item.a);
      card.querySelectorAll("[data-field]").forEach(function (inp) {
        inp.addEventListener("input", function () { currentPageFaq[idx][inp.dataset.field] = inp.value; });
      });
      card.querySelector("[data-remove-faq]").addEventListener("click", function () {
        currentPageFaq.splice(idx, 1);
        renderPeFaq();
      });
      mount.appendChild(card);
    });
  }

  function resetPageEditorForm() {
    ["pe-id", "pe-tier", "pe-url", "pe-title", "pe-meta-title", "pe-keyword", "pe-meta-desc", "pe-h1", "pe-area-ref", "pe-service-ref", "pe-intro", "pe-content", "pe-cover-image"].forEach(function (id) {
      document.getElementById(id).value = "";
    });
    document.getElementById("pe-type").value = "area";
    document.getElementById("pe-type").disabled = false;
    document.getElementById("pe-status").value = "draft";
    document.getElementById("pe-cover-upload").value = "";
    document.getElementById("pe-cover-status").textContent = "Unggah gambar sampul — langsung tersimpan & live begitu artikel disimpan.";
    updateCoverPreview("");
    document.getElementById("btn-delete-page").style.display = "none";
    currentPageFaq = [];
    renderPeFaq();
    document.getElementById("page-editor-msg").textContent = "";
  }

  function updateCoverPreview(url) {
    var preview = document.getElementById("pe-cover-preview");
    preview.innerHTML = url
      ? '<img src="' + url + '" alt="">'
      : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--gray-500);background:var(--gray-100);font-size:.8rem">Belum ada sampul</div>';
  }

  function openPageEditor(id, opts) {
    opts = opts || {};
    pageEditorContext = { presetType: opts.presetType || null, onDone: opts.onDone || loadPagesList };

    var editor = document.getElementById("page-editor");
    editor.style.display = "block";
    editor.scrollIntoView({ behavior: "smooth", block: "start" });
    resetPageEditorForm();

    if (opts.presetType) {
      document.getElementById("pe-type").value = opts.presetType;
      document.getElementById("pe-type").disabled = true;
    }

    if (!id) {
      document.getElementById("page-editor-title").textContent = opts.presetType ? "Tambah " + (typeLabels[opts.presetType] || "Halaman") + " Baru" : "Tambah Halaman Baru";
      return;
    }
    document.getElementById("page-editor-title").textContent = "Memuat...";
    fetch("get-pages.php?id=" + encodeURIComponent(id), { cache: "no-store" })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) { toast(data.message || "Gagal memuat halaman."); return; }
        var p = data.page;
        document.getElementById("page-editor-title").textContent = "Edit: " + p.title;
        document.getElementById("pe-id").value = p.id;
        document.getElementById("pe-type").value = p.type;
        document.getElementById("pe-status").value = p.status;
        document.getElementById("pe-tier").value = p.tier || "";
        document.getElementById("pe-url").value = p.url_path || "";
        document.getElementById("pe-title").value = p.title || "";
        document.getElementById("pe-meta-title").value = p.meta_title || "";
        document.getElementById("pe-keyword").value = p.target_keyword || "";
        document.getElementById("pe-meta-desc").value = p.meta_description || "";
        document.getElementById("pe-h1").value = p.h1 || "";
        document.getElementById("pe-area-ref").value = p.area_ref || "";
        document.getElementById("pe-service-ref").value = p.service_ref || "";
        document.getElementById("pe-intro").value = p.intro || "";
        document.getElementById("pe-content").value = p.content || "";
        document.getElementById("pe-cover-image").value = p.cover_image || "";
        updateCoverPreview(p.cover_image || "");
        currentPageFaq = p.faq || [];
        renderPeFaq();
        document.getElementById("btn-delete-page").style.display = "inline-block";
      })
      .catch(function () { toast("Gagal menghubungi server."); });
  }

  function bindPagesUI() {
    document.getElementById("pages-search").addEventListener("input", function () { pagesPageIndex = 0; renderPagesTable(); });
    document.getElementById("pages-filter-type").addEventListener("change", function () { pagesPageIndex = 0; renderPagesTable(); });
    document.getElementById("pages-filter-status").addEventListener("change", function () { pagesPageIndex = 0; renderPagesTable(); });
    document.getElementById("btn-refresh-pages").addEventListener("click", loadPagesList);
    document.getElementById("btn-add-page").addEventListener("click", function () { openPageEditor(null, { onDone: renderPagesTable }); });
    document.getElementById("btn-close-editor").addEventListener("click", function () {
      document.getElementById("page-editor").style.display = "none";
    });
    document.getElementById("btn-pe-add-faq").addEventListener("click", function () {
      currentPageFaq.push({ q: "Pertanyaan baru?", a: "Jawaban." });
      renderPeFaq();
    });

    document.getElementById("pe-cover-upload").addEventListener("change", function (e) {
      var file = e.target.files[0];
      if (!file) return;
      var status = document.getElementById("pe-cover-status");
      var fd = new FormData();
      fd.append("photo", file);
      status.textContent = "Mengunggah...";
      fetch("upload-photo.php", { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) {
            status.textContent = data.message || "Upload gagal.";
            toast(data.message || "Upload gagal.");
            return;
          }
          document.getElementById("pe-cover-image").value = data.url;
          updateCoverPreview(data.url);
          status.textContent = "Sampul tersimpan. Jangan lupa tekan \"Simpan Halaman\" untuk mempublikasikannya.";
        })
        .catch(function () {
          status.textContent = "Gagal menghubungi server saat upload.";
          toast("Gagal menghubungi server saat upload.");
        });
    });

    document.getElementById("btn-delete-page").addEventListener("click", function () {
      var id = document.getElementById("pe-id").value;
      if (!id) return;
      deletePageRow(id, pageEditorContext.onDone);
      document.getElementById("page-editor").style.display = "none";
    });

    document.getElementById("btn-save-page").addEventListener("click", function () {
      var msg = document.getElementById("page-editor-msg");
      var payload = {
        id: document.getElementById("pe-id").value || null,
        type: document.getElementById("pe-type").value,
        status: document.getElementById("pe-status").value,
        tier: document.getElementById("pe-tier").value,
        url_path: document.getElementById("pe-url").value,
        title: document.getElementById("pe-title").value,
        meta_title: document.getElementById("pe-meta-title").value,
        target_keyword: document.getElementById("pe-keyword").value,
        meta_description: document.getElementById("pe-meta-desc").value,
        h1: document.getElementById("pe-h1").value,
        area_ref: document.getElementById("pe-area-ref").value,
        service_ref: document.getElementById("pe-service-ref").value,
        intro: document.getElementById("pe-intro").value,
        content: document.getElementById("pe-content").value,
        cover_image: document.getElementById("pe-cover-image").value || null,
        faq: currentPageFaq
      };
      if (!payload.url_path || !payload.title) {
        msg.style.color = "#c0392b";
        msg.textContent = "URL dan judul wajib diisi.";
        return;
      }
      msg.style.color = "var(--gray-600)";
      msg.textContent = "Menyimpan...";
      var fd = new FormData();
      fd.append("payload", JSON.stringify(payload));
      var onDone = pageEditorContext.onDone || loadPagesList;
      fetch("save-page.php", { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          msg.style.color = data.success ? "#1eb857" : "#c0392b";
          msg.textContent = data.message;
          if (data.success) {
            loadPagesList();
            if (data.id) {
              document.getElementById("pe-id").value = data.id;
              document.getElementById("btn-delete-page").style.display = "inline-block";
            }
          }
        })
        .catch(function () {
          msg.style.color = "#c0392b";
          msg.textContent = "Gagal menghubungi server.";
        });
    });

    document.getElementById("btn-import-pages").addEventListener("click", function () {
      var raw = document.getElementById("import-textarea").value.trim();
      if (!raw) { toast("Tempel data terlebih dahulu."); return; }
      var lines = raw.split("\n").filter(function (l) { return l.trim() !== ""; });
      var header = lines[0].split("\t").map(function (h) { return h.trim().toLowerCase(); });
      var idx = {
        prioritas: header.indexOf("prioritas"),
        kategori: header.indexOf("kategori"),
        judul: header.indexOf("judul halaman"),
        url: header.indexOf("url"),
        keyword: header.indexOf("target kata kunci"),
        status: header.indexOf("status")
      };
      var rows = lines.slice(1).map(function (line) {
        var cols = line.split("\t");
        return {
          prioritas: idx.prioritas > -1 ? cols[idx.prioritas] : "",
          kategori: idx.kategori > -1 ? cols[idx.kategori] : "",
          judul: idx.judul > -1 ? cols[idx.judul] : "",
          url: idx.url > -1 ? cols[idx.url] : "",
          keyword: idx.keyword > -1 ? cols[idx.keyword] : "",
          status: idx.status > -1 ? cols[idx.status] : ""
        };
      });
      var fd = new FormData();
      fd.append("payload", JSON.stringify(rows));
      toast("Mengimpor " + rows.length + " baris...");
      fetch("import-pages.php", { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          toast(data.message + (data.inserted !== undefined ? " (" + data.inserted + " masuk, " + data.skipped + " dilewati)" : ""));
          if (data.success) loadPagesList();
        })
        .catch(function () { toast("Gagal menghubungi server."); });
    });
  }

  // Tombol search/status/tambah/muat-ulang untuk tab Layanan & Artikel --
  // pola sama persis, cuma beda id elemen & tipe & tabel target.
  function bindScopedPagesTabUI(type, table, ids) {
    document.getElementById(ids.search).addEventListener("input", table.reset);
    document.getElementById(ids.statusFilter).addEventListener("change", table.reset);
    document.getElementById(ids.refreshBtn).addEventListener("click", loadPagesList);
    document.getElementById(ids.addBtn).addEventListener("click", function () {
      openPageEditor(null, { presetType: type, onDone: table.render });
    });
  }

  function init() {
    initTabs();
    initPickerMap();
    bindBusinessForm();
    bindAreaButtons();
    bindFaqButtons();
    bindPortfolioButtons();
    bindTestimonialButtons();
    bindExportImport();
    bindPasswordForm();
    bindPagesUI();
    bindScopedPagesTabUI("service", layananTable, {
      search: "layanan-search", statusFilter: "layanan-filter-status", refreshBtn: "btn-refresh-layanan", addBtn: "btn-add-layanan"
    });
    bindScopedPagesTabUI("article", artikelTable, {
      search: "artikel-search", statusFilter: "artikel-filter-status", refreshBtn: "btn-refresh-artikel", addBtn: "btn-add-artikel"
    });
    loadPagesList();
    fetchLiveBase().then(function (base) {
      state = loadState(base);
      renderAll();
    });
  }

  document.addEventListener("DOMContentLoaded", init);
})();
