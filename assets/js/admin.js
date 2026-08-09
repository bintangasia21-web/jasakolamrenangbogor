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
      var seoPage = pagesState.filter(function (p) { return p.type === "area" && p.url_path === area.link; })[0];
      var seoStatusHtml;
      if (seoPage) {
        var mismatch = seoPage.status !== "published";
        seoStatusHtml =
          '<div class="admin-card-head" style="margin-top:16px"><h4 style="margin:0">Halaman Konten SEO</h4>' +
          '<button type="button" class="btn btn-sm btn-secondary" data-edit-area-page="' + seoPage.id + '">Edit Halaman Konten</button></div>' +
          '<p style="color:var(--gray-600);font-size:.85rem;margin:0 0 6px">' + (seoPage.title || "") + ' — <span class="status-badge ' + seoPage.status + '">' + (seoPage.status === "published" ? "Live" : "Draft") + '</span></p>' +
          (mismatch ? '<small style="color:#c0392b">⚠ Chip area ini aktif di beranda, tapi halaman kontennya masih draft (belum bisa diakses publik).</small>' : '');
      } else {
        seoStatusHtml =
          '<div class="admin-card-head" style="margin-top:16px"><h4 style="margin:0">Halaman Konten SEO</h4>' +
          '<button type="button" class="btn btn-sm btn-secondary" data-create-area-page="' + idx + '">+ Buat Halaman Konten</button></div>' +
          '<small style="color:var(--gray-500)">Belum ada halaman konten SEO untuk area ini — chip ini akan mengarah ke halaman yang belum tersedia sampai dibuat.</small>';
      }
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
        seoStatusHtml +
        '<div class="admin-card-head" style="margin-top:16px"><h4 style="margin:0">Galeri Foto Area Ini</h4></div>' +
        '<div data-gallery></div>' +
        '<div class="field"><label>Unggah foto untuk area ini (langsung live)</label><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-upload-area="' + idx + '"><small data-upload-status>Foto khusus area ini, terpisah dari galeri Portofolio.</small></div>' +
        '<label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:.85rem;color:var(--gray-600)"><input type="checkbox" data-remove-cascade> Saat hapus area ini, hapus juga halaman konten SEO &amp; foto galeri terkait</label>';

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
        var cascade = card.querySelector("[data-remove-cascade]").checked;
        if (activeAreaIndex === idx) activeAreaIndex = null;
        var removedLink = state.areas[idx].link;
        state.areas.splice(idx, 1);
        if (cascade) {
          var matchingPage = pagesState.filter(function (p) { return p.type === "area" && p.url_path === removedLink; })[0];
          state.areaPhotos = state.areaPhotos.filter(function (p) { return p.areaLink !== removedLink; });
          syncSectionLive("area_photos", state.areaPhotos);
          if (matchingPage) deletePageRow(matchingPage.id);
        }
        renderAreas();
      });
      card.querySelector("[data-pick]").addEventListener("click", function () {
        setActiveArea(idx);
      });
      if (seoPage) {
        card.querySelector("[data-edit-area-page]").addEventListener("click", function () {
          openPageEditor(seoPage.id, { presetType: "area", onDone: renderAreas });
        });
      } else {
        card.querySelector("[data-create-area-page]").addEventListener("click", function () {
          openPageEditor(null, { presetType: "area", onDone: renderAreas });
          document.getElementById("pe-url").value = area.link;
          document.getElementById("pe-area-ref").value = area.name;
          document.getElementById("pe-title").value = "Jasa Kolam Renang " + area.name;
        });
      }
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
  function slugify(text) {
    return (text || "")
      .toString().toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, "")
      .replace(/\s+/g, "-")
      .replace(/-+/g, "-")
      .replace(/^-|-$/g, "");
  }

  function renderPortfolio() {
    var mount = document.getElementById("portfolio-list-admin");
    mount.innerHTML = "";
    state.portfolio.forEach(function (item, idx) {
      var card = document.createElement("div");
      card.className = "admin-card";
      var thumbHtml = item.image
        ? '<img src="' + item.image + '" alt="">'
        : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--blue-600);font-weight:700;background:linear-gradient(135deg,' + (item.color1 || "#1478c8") + "," + (item.color2 || "#00b8d9") + ')">Placeholder</div>';

      var detailPage = item.detailLink ? pagesState.filter(function (p) { return p.type === "portfolio" && p.url_path === item.detailLink; })[0] : null;
      var detailHtml;
      if (detailPage) {
        detailHtml =
          '<div class="admin-card-head" style="margin-top:16px"><h4 style="margin:0">Halaman Detail Proyek</h4>' +
          '<button type="button" class="btn btn-sm btn-secondary" data-edit-detail="' + detailPage.id + '">Edit Halaman Detail</button></div>' +
          '<p style="color:var(--gray-600);font-size:.85rem;margin:0">' + detailPage.title + ' — <span class="status-badge ' + detailPage.status + '">' + (detailPage.status === "published" ? "Live" : "Draft") + '</span></p>';
      } else {
        detailHtml =
          '<div class="admin-card-head" style="margin-top:16px"><h4 style="margin:0">Halaman Detail Proyek (opsional)</h4>' +
          '<button type="button" class="btn btn-sm btn-secondary" data-create-detail="' + idx + '">+ Buat Halaman Detail</button></div>' +
          '<small style="color:var(--gray-500)">Belum ada halaman studi-kasus untuk proyek ini — kartu di beranda/portofolio akan tampil tanpa tautan sampai halaman ini dibuat.</small>';
      }

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
        '<div class="field"><label>Atau unggah foto (langsung live)</label><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-upload="' + idx + '"><small data-upload-status>Foto yang diunggah langsung tersimpan di server dan tampil bagi semua pengunjung.</small></div>' +
        detailHtml;

      if (detailPage) {
        card.querySelector("[data-edit-detail]").addEventListener("click", function () {
          openPageEditor(detailPage.id, { presetType: "portfolio", onDone: renderPortfolio });
        });
      } else {
        card.querySelector("[data-create-detail]").addEventListener("click", function () {
          var suggestedUrl = "/portofolio/" + (slugify(item.title) || "proyek-" + (idx + 1)) + "/";
          openPageEditor(null, {
            presetType: "portfolio",
            onDone: renderPortfolio,
            afterSave: function () {
              state.portfolio[idx].detailLink = suggestedUrl;
              syncSectionLive("portfolio", state.portfolio);
            }
          });
          document.getElementById("pe-url").value = suggestedUrl;
          document.getElementById("pe-title").value = item.title;
          document.getElementById("pe-area-ref").value = item.area;
        });
      }

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
  var pageEditorContext = { presetType: null, onDone: null, afterSave: null };

  function loadPagesList() {
    return fetch("get-pages.php", { cache: "no-store" })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          pagesState = data.pages;
          renderPagesTable();
          layananTable.reset();
          artikelTable.reset();
          // Kartu Area Layanan & Portfolio menampilkan status halaman SEO
          // terkait (dari pagesState) -- render ulang begitu data ini
          // tersedia, karena loadPagesList() & fetchLiveBase() jalan
          // paralel saat init() sehingga keduanya bisa saja sudah
          // dirender lebih dulu sebelum pagesState terisi.
          if (state && state.areas) renderAreas();
          if (state && state.portfolio) renderPortfolio();
          renderHalamanUtama();
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
    pageEditorContext = { presetType: opts.presetType || null, onDone: opts.onDone || loadPagesList, afterSave: opts.afterSave || null };

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
      var onDone = pageEditorContext.onDone;
      var afterSave = pageEditorContext.afterSave;
      fetch("save-page.php", { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          msg.style.color = data.success ? "#1eb857" : "#c0392b";
          msg.textContent = data.message;
          if (data.success) {
            loadPagesList().then(function () {
              if (onDone) onDone();
            });
            if (data.id) {
              document.getElementById("pe-id").value = data.id;
              document.getElementById("btn-delete-page").style.display = "inline-block";
            }
            if (afterSave) {
              afterSave(data.id, data.url_path);
              pageEditorContext.afterSave = null;
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

  /* ---------- Halaman Utama ---------- */
  // 5 halaman inti yang selalu ditautkan dari menu navigasi -- tidak
  // pernah bisa dihapus lewat tab ini (dua di antaranya, Layanan & Area
  // Layanan, juga diblokir langsung di delete-page.php sisi server).
  var CORE_HUB_PAGES = {
    "/layanan/": "Layanan",
    "/area-layanan/": "Area Layanan"
  };
  var HOME_SECTIONS_FIELDS = [
    { group: "Hero", fields: [
      { key: "hero_badge", label: "Badge", type: "text" },
      { key: "hero_h1", label: "H1", type: "text" },
      { key: "hero_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Tentang", fields: [
      { key: "tentang_eyebrow", label: "Eyebrow", type: "text" },
      { key: "tentang_h2", label: "Judul (H2)", type: "text" },
      { key: "tentang_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Layanan", fields: [
      { key: "layanan_eyebrow", label: "Eyebrow", type: "text" },
      { key: "layanan_h2", label: "Judul (H2)", type: "text" },
      { key: "layanan_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Area Layanan", fields: [
      { key: "area_eyebrow", label: "Eyebrow", type: "text" },
      { key: "area_h2", label: "Judul (H2)", type: "text" },
      { key: "area_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Masalah Kolam Renang", fields: [
      { key: "masalah_eyebrow", label: "Eyebrow", type: "text" },
      { key: "masalah_h2", label: "Judul (H2)", type: "text" },
      { key: "masalah_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Proyek Nyata", fields: [
      { key: "proyek_eyebrow", label: "Eyebrow", type: "text" },
      { key: "proyek_h2", label: "Judul (H2)", type: "text" },
      { key: "proyek_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Jenis Pelanggan", fields: [
      { key: "jenis_pelanggan_eyebrow", label: "Eyebrow", type: "text" },
      { key: "jenis_pelanggan_h2", label: "Judul (H2)", type: "text" },
      { key: "jenis_pelanggan_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Panduan Kolam Renang", fields: [
      { key: "panduan_eyebrow", label: "Eyebrow", type: "text" },
      { key: "panduan_h2", label: "Judul (H2)", type: "text" },
      { key: "panduan_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "FAQ", fields: [
      { key: "faq_eyebrow", label: "Eyebrow", type: "text" },
      { key: "faq_h2", label: "Judul (H2)", type: "text" },
      { key: "faq_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "Testimonial", fields: [
      { key: "testimonial_eyebrow", label: "Eyebrow", type: "text" },
      { key: "testimonial_h2", label: "Judul (H2)", type: "text" },
      { key: "testimonial_lead", label: "Lead", type: "textarea" }
    ] },
    { group: "CTA", fields: [
      { key: "cta_title", label: "Judul CTA", type: "text" },
      { key: "cta_subtitle", label: "Subjudul CTA", type: "textarea" }
    ] }
  ];
  var HUB_SECTIONS_FIELDS = [
    { group: null, fields: [
      { key: "h1", label: "H1", type: "text" },
      { key: "lead", label: "Lead / Intro", type: "textarea" }
    ] }
  ];

  function renderPageSectionsCard(container, pageKey, title, groups, publicUrl, photoField) {
    var card = document.createElement("div");
    card.className = "admin-card";
    var groupsHtml = groups.map(function (g) {
      var fieldsHtml = g.fields.map(function (f) {
        return field(f.label, f.type, f.key, "");
      }).join("");
      return (g.group ? '<h4 style="margin-top:16px">' + g.group + '</h4>' : '') + fieldsHtml;
    }).join("");
    var photoHtml = "";
    if (photoField) {
      photoHtml =
        '<div class="field" style="margin-top:16px">' +
        '<label>' + photoField.label + ' (opsional)</label>' +
        '<div class="thumb-preview" data-photo-preview style="width:220px;height:140px;margin-bottom:8px">' +
        '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--gray-500);background:var(--gray-100);font-size:.8rem">Belum ada foto</div>' +
        '</div>' +
        '<input type="hidden" data-field="' + photoField.key + '">' +
        '<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-photo-upload>' +
        '<small data-photo-status>Unggah foto — langsung tersimpan, tekan "Simpan ' + title + '" untuk mempublikasikannya.</small>' +
        '</div>';
    }
    card.innerHTML =
      '<div class="admin-card-head"><h3>' + title + '</h3>' +
      (publicUrl ? '<a href="' + publicUrl + '" target="_blank" rel="noopener" class="btn btn-sm btn-ghost">Lihat Halaman &rarr;</a>' : '') +
      '</div>' + groupsHtml + photoHtml +
      '<div class="toolbar" style="margin-top:12px"><button type="button" class="btn btn-primary btn-sm" data-save-sections>Simpan ' + title + '</button></div>' +
      '<p data-sections-msg style="margin-top:8px;font-size:.85rem"></p>';
    container.appendChild(card);

    function updatePhotoPreview(url) {
      var preview = card.querySelector("[data-photo-preview]");
      if (!preview) return;
      preview.innerHTML = url
        ? '<img src="' + url + '" alt="">'
        : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--gray-500);background:var(--gray-100);font-size:.8rem">Belum ada foto</div>';
    }

    if (photoField) {
      card.querySelector("[data-photo-upload]").addEventListener("change", function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var status = card.querySelector("[data-photo-status]");
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
            card.querySelector('[data-field="' + photoField.key + '"]').value = data.url;
            updatePhotoPreview(data.url);
            status.textContent = 'Foto tersimpan. Tekan "Simpan ' + title + '" untuk mempublikasikannya.';
          })
          .catch(function () {
            status.textContent = "Gagal menghubungi server saat upload.";
            toast("Gagal menghubungi server saat upload.");
          });
      });
    }

    fetch("get-page-sections.php?page_key=" + encodeURIComponent(pageKey), { cache: "no-store" })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var sections = (data && data.sections) || {};
        card.querySelectorAll("[data-field]").forEach(function (inp) {
          if (sections[inp.dataset.field] !== undefined) inp.value = sections[inp.dataset.field];
        });
        if (photoField && sections[photoField.key]) updatePhotoPreview(sections[photoField.key]);
      })
      .catch(function () { /* form tetap kosong, admin bisa isi manual */ });

    card.querySelector("[data-save-sections]").addEventListener("click", function () {
      var msg = card.querySelector("[data-sections-msg]");
      var payload = {};
      card.querySelectorAll("[data-field]").forEach(function (inp) {
        payload[inp.dataset.field] = inp.value;
      });
      msg.style.color = "var(--gray-600)";
      msg.textContent = "Menyimpan...";
      var fd = new FormData();
      fd.append("page_key", pageKey);
      fd.append("payload", JSON.stringify(payload));
      fetch("save-page-sections.php", { method: "POST", body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          msg.style.color = data.success ? "#1eb857" : "#c0392b";
          msg.textContent = data.message;
          refreshPreview();
        })
        .catch(function () {
          msg.style.color = "#c0392b";
          msg.textContent = "Gagal menghubungi server.";
        });
    });
  }

  var halamanUtamaStaticRendered = false;

  function renderHalamanUtama() {
    var mount = document.getElementById("halaman-utama-list");
    if (!mount.querySelector("#halaman-utama-dynamic")) {
      mount.innerHTML = '<div id="halaman-utama-static"></div><div id="halaman-utama-dynamic"></div>';
    }
    var staticMount = document.getElementById("halaman-utama-static");
    var mountDynamic = document.getElementById("halaman-utama-dynamic");
    mountDynamic.innerHTML = "";

    // Kartu berbasis page_sections (Beranda, Portofolio, FAQ, Kontak)
    // punya input aktif yang mungkin sedang diketik admin -- dibangun
    // SEKALI saja, TIDAK dibangun ulang tiap loadPagesList() jalan (yang
    // terjadi berkali-kali lintas tab lain), supaya input yang belum
    // disimpan tidak hilang begitu saja.
    if (!halamanUtamaStaticRendered) {
      renderPageSectionsCard(staticMount, "home", "Beranda", HOME_SECTIONS_FIELDS, "/", { key: "hero_image", label: "Foto Hero Beranda" });
      renderPageSectionsCard(staticMount, "portofolio", "Portofolio", HUB_SECTIONS_FIELDS, "/portofolio/");
      renderPageSectionsCard(staticMount, "faq", "FAQ", HUB_SECTIONS_FIELDS, "/faq/");
      renderPageSectionsCard(staticMount, "kontak", "Kontak", HUB_SECTIONS_FIELDS, "/kontak/");
      halamanUtamaStaticRendered = true;
    }

    var mount = mountDynamic;

    // 2-3. Layanan & Area Layanan -- sudah berbentuk baris "pages" (type=page),
    // jadi pakai editor pe-* yang sama seperti tab Layanan/Artikel/dll,
    // bukan page_sections. Hapus diblokir (dipakai nav header hardcode).
    Object.keys(CORE_HUB_PAGES).forEach(function (urlPath) {
      var label = CORE_HUB_PAGES[urlPath];
      var pageRow = pagesState.filter(function (p) { return p.url_path === urlPath; })[0];
      var card = document.createElement("div");
      card.className = "admin-card";
      if (pageRow) {
        card.innerHTML =
          '<div class="admin-card-head"><h3>' + label + '</h3>' +
          '<a href="' + urlPath + '" target="_blank" rel="noopener" class="btn btn-sm btn-ghost">Lihat Halaman &rarr;</a></div>' +
          '<p style="color:var(--gray-600);font-size:.85rem;margin:0 0 10px">' + (pageRow.title || "") + ' — <span class="status-badge ' + pageRow.status + '">' + (pageRow.status === "published" ? "Live" : "Draft") + '</span></p>' +
          '<button type="button" class="btn btn-secondary btn-sm" data-edit-hub="' + pageRow.id + '">Edit Konten Halaman</button>' +
          '<p style="color:var(--gray-500);font-size:.8rem;margin-top:10px">Halaman inti — tidak bisa dihapus karena masih ditautkan dari menu navigasi situs.</p>';
        card.querySelector("[data-edit-hub]").addEventListener("click", function () {
          openPageEditor(pageRow.id, { presetType: "page", onDone: renderHalamanUtama });
        });
      } else {
        card.innerHTML =
          '<div class="admin-card-head"><h3>' + label + '</h3></div>' +
          '<p style="color:#c0392b;font-size:.85rem">Halaman ini belum ada di database. Buat lewat tab "Halaman Kombinasi &amp; Lainnya" dengan URL <code>' + urlPath + '</code>.</p>';
      }
      mount.appendChild(card);
    });

    // 7. Halaman tambahan (type=page) yang dibuat admin di luar 5 halaman
    // inti -- boleh diedit & dihapus bebas.
    var extraPages = pagesState.filter(function (p) {
      return p.type === "page" && !CORE_HUB_PAGES.hasOwnProperty(p.url_path);
    });
    if (extraPages.length > 0) {
      var extraHead = document.createElement("h3");
      extraHead.style.cssText = "margin-top:24px";
      extraHead.textContent = "Halaman Tambahan";
      mount.appendChild(extraHead);
      extraPages.forEach(function (p) {
        var card = document.createElement("div");
        card.className = "admin-card";
        card.innerHTML =
          '<div class="admin-card-head"><h3>' + (p.title || p.url_path) + '</h3>' +
          '<div><button type="button" class="btn btn-sm btn-secondary" data-edit-extra="' + p.id + '">Edit</button> ' +
          '<button type="button" class="btn btn-sm btn-danger" data-del-extra="' + p.id + '">Hapus</button></div></div>' +
          '<p style="color:var(--gray-600);font-size:.85rem;margin:0">' + p.url_path + ' — <span class="status-badge ' + p.status + '">' + (p.status === "published" ? "Live" : "Draft") + '</span></p>';
        card.querySelector("[data-edit-extra]").addEventListener("click", function () {
          openPageEditor(p.id, { presetType: "page", onDone: renderHalamanUtama });
        });
        card.querySelector("[data-del-extra]").addEventListener("click", function () {
          deletePageRow(p.id, renderHalamanUtama);
        });
        mount.appendChild(card);
      });
    }
  }

  function bindHalamanUtamaUI() {
    document.getElementById("btn-add-halaman-utama").addEventListener("click", function () {
      openPageEditor(null, { presetType: "page", onDone: renderHalamanUtama });
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
    bindHalamanUtamaUI();
    loadPagesList();
    fetchLiveBase().then(function (base) {
      state = loadState(base);
      renderAll();
    });
  }

  document.addEventListener("DOMContentLoaded", init);
})();
