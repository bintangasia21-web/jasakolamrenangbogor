# SERVICE PAGE SEO IMPLEMENTATION — PHASE 3
**Target:** `/layanan/pembuatan-kolam-renang-baru/` (satu halaman saja)
**Tanggal:** 2026-08-11
**Dasar:** `SERVICE-PEMBUATAN-KOLAM-RENANG-AUDIT.md`
**Status deploy:** ⏳ **BELUM di-commit/push** (sesuai instruksi eksplisit "Jangan commit. Jangan push. Tunggu review.") — semua perubahan ada di working tree lokal, siap direview sebelum dideploy.

---

## Before

Diambil dari fetch live halaman (dilakukan saat penyusunan `SERVICE-PEMBUATAN-KOLAM-RENANG-AUDIT.md`):

| Item | Nilai |
|---|---|
| Word count (badan + lead + FAQ) | ±171 kata |
| H1 | `Pembuatan Kolam Renang Baru` (1×) |
| H2 | 2× — "Pertanyaan Seputar Halaman Ini" (FAQ, shared), "Pembuatan Kolam Renang Baru" (CTA band, shared, duplikat H1) |
| H3 | 0 |
| H4 | 1× — "Tahapan Pengerjaan" (langsung setelah H1, skip H2 & H3) |
| Title | `Pembuatan Kolam Renang Baru \| Jasa Kolam Renang Bogor` (53 char) |
| Meta description | 170 karakter |
| Internal link di konten (bukan nav/footer) | 0 |
| Gambar | 0 |
| FAQ | 2 pertanyaan (durasi pengerjaan, biaya survei) |
| Schema | `LocalBusiness`, `BreadcrumbList`, `Service` (`areaServed`: string "Bogor"), `FAQPage` (2 entri) |

---

## After

Diverifikasi secara statis (lint PHP + parsing heading/link/word-count dari nilai yang akan disimpan) — **belum live**, menunggu skrip dijalankan setelah deploy disetujui.

| Item | Nilai |
|---|---|
| Word count (konten baru) | **338 kata** (naik dari ±99 kata badan lama; total halaman dengan lead+FAQ akan ±410 kata) |
| H1 | `Pembuatan Kolam Renang Baru` — **tidak diubah** (audit sudah menilai GOOD) |
| H2 | **6 baru** di konten: "Pembuatan Kolam Renang di Bogor", "Apa yang Kami Kerjakan", "Tahapan Pekerjaan", "Pengalaman dan Proyek", "Area yang Dilayani", "Konsultasi Pembuatan Kolam Renang" — ditambah 2 H2 shared yang tetap ada (FAQ, CTA band) |
| H3 | 0 (sengaja tidak ditambahkan — tidak ada sub-section yang genuinely butuh level ini, lihat catatan di §Files Modified) |
| H4 | 0 (H4 lama "Tahapan Pengerjaan" dihapus, diganti H2 biasa) |
| Title | **Tidak diubah** (audit sudah menilai GOOD, di luar scope perbaikan) |
| Meta description | **159 karakter** (dari 170) — natural, menyebut Bogor, fakta terverifikasi (10+ tahun, 350+ proyek), dan ajakan gratis survei |
| Internal link di konten | **8 baru**: 2 layanan terkait (waterproofing, instalasi filter&pompa), 2 area (Sentul, Bogor Kota) + 1 ke hub area, 2 portofolio (Villa Modern Sentul, Kolam Keluarga), 1 WhatsApp |
| Gambar | **1 baru** — foto portofolio asli "Kolam Renang Villa Modern di Sentul" (`photo.php?id=11`), `width="649" height="472"` (dari `getimagesize()` nyata), `alt` deskriptif, `loading="lazy"` |
| FAQ | **3 pertanyaan** — 2 lama dipertahankan persis, 1 baru ("Sudah berapa lama Jasa Kolam Renang Bogor menangani pembuatan kolam renang?") memakai HANYA fakta terverifikasi (10+ tahun, 350+ proyek) |
| Schema | **Tidak diubah** — `Service`/`LocalBusiness`/`BreadcrumbList` dihasilkan dari `title`/`intro`/`business` yang tidak disentuh; `FAQPage` otomatis mengikuti `faq_json` baru (3 entri, bukan 2) karena `page-router.php` membaca kolom itu apa adanya |

---

## Files Modified

| File | Alasan | Ringkasan | Homepage-only / halaman lain? |
|---|---|---|---|
| `fix-service-pembuatan-kolam-baru.php` (baru) | Implementasi seluruh FIX 1-6 dari brief — konten halaman ini 100% berasal dari 1 baris tabel `pages`, tidak bisa diubah lewat edit file kode | Skrip sekali-jalan (pola sama seperti skrip-skrip sebelumnya di proyek ini) yang meng-UPDATE kolom `meta_description`, `content`, `faq_json` HANYA untuk baris `url_path='/layanan/pembuatan-kolam-renang-baru/' AND type='service'` — query `WHERE` eksplisit mencegah baris lain tersentuh | **Ya, satu baris/satu halaman saja.** Tidak menyentuh baris `pages` lain (19 layanan lain, area, artikel, portofolio, halaman utama) |
| `.htaccess` | Melindungi skrip baru dengan Basic Auth yang sama seperti skrip admin lain | Menambahkan 1 nama file ke `FilesMatch` yang sudah ada | Tidak memengaruhi proteksi file lain |

**TIDAK ada file lain yang diubah.** Secara khusus, **tidak disentuh** sesuai instruksi:
- `inc/templates/service.php` (shared, 20 halaman)
- `render_cta_band()` / `render_faq_block()` / `render_local_business_ld()` (shared)
- `index.php`, template area/artikel/portofolio, `assets/css/style.css`, skema database, `robots.txt`, sitemap, redirect.

**Catatan teknis heading:** Heading H2 baru di `content` tidak memerlukan perubahan CSS — `style.css` baris 56 (`h1, h2, h3, h4 { margin: 0 0 .5em; color: var(--blue-900); ...}`) sudah membuat semua level heading H1-H4 memakai gaya visual dasar yang identik, jadi H2 baru akan tampil konsisten tanpa sentuhan apa pun ke stylesheet.

**Catatan H3 (tidak ditambahkan):** Brief menyebut "sub-section menggunakan H3 jika diperlukan". Setelah disusun, tidak ada sub-section di dalam 6 H2 baru yang genuinely butuh level H3 (mis. "Tahapan Pekerjaan" sudah direpresentasikan dengan tepat sebagai `<ol>` bernomor, bukan 6 heading terpisah) — menambahkan H3 di sini akan menciptakan struktur yang dipaksakan, bertentangan dengan instruksi "gunakan struktur yang natural" dan "jangan memasukkan keyword secara paksa".

---

## Verified Business Facts Used

- **10+ Tahun Pengalaman** — dipakai apa adanya di section "Pengalaman dan Proyek", meta description, dan FAQ baru.
- **350+ Proyek Selesai** — dipakai apa adanya di lokasi yang sama.

Tidak ada angka baru dibuat atau diubah.

---

## DATA_REQUIRED

Item-item ini **tidak ditambahkan ke konten** karena tidak ada data yang bisa diverifikasi dari source/database — sesuai instruksi, tidak dikarang:

| Item | Kenapa DATA_REQUIRED |
|---|---|
| Durasi/cakupan garansi ("bergaransi" di tahap 6) | Tidak ada kolom/dokumen kebijakan garansi di database maupun kode manapun yang menyebut durasi atau cakupan pastinya |
| Jenis kolam spesifik (skimmer/overflow/infinity/dll) | Tidak ada data teknis ini tersimpan di mana pun di source |
| Merek/spesifikasi material & sistem filtrasi | Hanya disebut generik "waterproofing dan keramik"/"sistem filtrasi" di source asli, tidak ada detail merek/tipe yang bisa diverifikasi |
| Durasi tiap tahap kerja individual (mis. berapa hari penggalian) | Hanya total durasi keseluruhan (3-6 minggu) yang tersedia & terverifikasi (sudah ada di FAQ #1, dipertahankan) |
| Testimoni khusus layanan pembuatan kolam | Tabel `testimonials` tidak punya baris `published` (temuan yang sama seperti audit homepage sebelumnya) |
| Rating | Tidak ada data rating asli — **sengaja tidak ditambahkan**, konsisten dengan kebijakan "tidak membuat rating palsu" |

Item-item ini tetap tercatat di §15 audit asli sebagai referensi kalau pemilik bisnis ingin melengkapi datanya di masa depan.

---

## OUT OF SCOPE

Tidak ada item yang di-STOP pada fase implementasi ini (berbeda dari audit, yang menemukan beberapa isu level-template). Rekapitulasi 2 isu yang SUDAH diketahui dari audit sebagai out-of-scope permanen untuk fase manapun yang dibatasi "satu halaman":

| Isu | Kenapa Out of Scope |
|---|---|
| H2 CTA band duplikat H1 (`render_cta_band($page['title'], ...)`) | Perbaikannya butuh edit `inc/templates/service.php`, dipakai 20 halaman layanan sekaligus — brief Phase 3 secara eksplisit melarang menyentuh file ini. **Tidak diubah.** |
| `Service` schema `areaServed` (string "Bogor" vs array 10 area di `LocalBusiness`) | Kedua schema dihasilkan dari sumber berbeda: `Service` schema hardcode `'areaServed' => 'Bogor'` di `inc/templates/service.php` (baris 35) — perbaikannya juga butuh edit shared template yang sama. **Tidak diubah**, sesuai instruksi "Jika memperbaikinya membutuhkan shared schema renderer: STOP dan laporkan." |

Tidak ditemukan isu BARU yang memerlukan perubahan shared component selama implementasi Phase 3 ini (semua 6 area FIX yang diminta bisa diselesaikan murni lewat konten 1 baris database).

---

## Tests Performed

Karena perubahan **belum di-deploy** (belum commit/push, sesuai instruksi), pengujian di bawah ini adalah **verifikasi statis/lokal** dari nilai yang SUDAH disiapkan di `fix-service-pembuatan-kolam-baru.php`, bukan pengujian terhadap situs live:

| # | Test | Metode | Hasil |
|---|---|---|---|
| 1 | Skrip PHP valid secara sintaks | `php -l` | ✅ Lolos |
| 2 | Struktur heading benar (H1 tetap 1×, H2 baru 6×, tidak ada H3/H4 tak sengaja) | Parsing regex terhadap string `$content` yang akan disimpan | ✅ Terverifikasi: 6× H2, 0× H3, 0× H4 |
| 3 | Semua 8 URL tujuan internal link valid (bukan tebakan) | `curl` HTTP status ke tiap URL target **sebelum** ditautkan | ✅ Semua `200 OK`: `/layanan/waterproofing-kolam-renang/`, `/layanan/instalasi-filter-pompa/`, `/area/sentul/`, `/area/bogor-kota/`, `/area-layanan/`, `/portofolio/kolam-renang-villa-modern-di-sentul/`, `/portofolio/kolam-renang-keluarga/` |
| 4 | Gambar yang disisipkan bukan gambar rusak, dimensi akurat | `getimagesize()` langsung terhadap `photo.php?id=11` yang sudah live | ✅ 649×472, `image/jpeg`, sesuai atribut `width`/`height` yang ditulis |
| 5 | CSS custom property yang dipakai (`--radius-md`, `--shadow-sm`) benar-benar ada | `grep` ke `style.css` | ✅ Ditemukan, tidak perlu perubahan CSS |
| 6 | Meta description panjang wajar | Hitung karakter | ✅ 159 karakter |
| 7 | FAQ baru hanya memakai fakta terverifikasi, tidak mengarang | Review manual isi `$faq` | ✅ FAQ #3 hanya memakai "10+ tahun"/"350+ proyek" |
| 8 | Query UPDATE hanya menyasar 1 baris | Review `WHERE url_path = :url_path AND type = 'service'` | ✅ Spesifik, tidak ada risiko UPDATE massal |
| 9 | Idempoten (aman dijalankan berulang) | Review logic `rowCount()` + fallback pengecekan baris | ✅ Sesuai pola skrip-skrip sebelumnya di proyek ini |

**Belum bisa diuji sampai deploy disetujui** (item dari brief §9 "TESTING"):
- HTTP 200 setelah perubahan live
- Canonical/robots tetap benar setelah perubahan live
- Tampilan visual akhir di browser
- Homepage & halaman layanan lain tidak berubah (regression check terhadap situs LIVE)

---

## Regression Check

**Terhadap kode/file:** Dijamin oleh scope query (`WHERE url_path = ... AND type = 'service'`, satu baris) dan tidak ada file shared yang disentuh (lihat §Files Modified & §OUT OF SCOPE).

**Terhadap situs live:** Belum bisa diverifikasi lewat fetch karena perubahan belum dideploy. Setelah disetujui untuk push & skrip dijalankan, langkah verifikasi yang direkomendasikan:
1. Fetch `/` (homepage) — pastikan tidak berubah.
2. Fetch 1-2 halaman `/layanan/*` lain (mis. `/layanan/perawatan-pembersihan-rutin/`) — pastikan tidak berubah.
3. Fetch `/layanan/pembuatan-kolam-renang-baru/` — pastikan heading/meta/link/gambar baru tampil sesuai §After.

---

# FINAL STATUS

✅ Implementasi selesai untuk 6 area FIX (heading, kedalaman konten, internal linking, meta description, gambar, FAQ) — seluruhnya sebagai perubahan konten 1 baris database, tanpa menyentuh shared component.
🛑 2 isu (dari audit) dikonfirmasi ulang sebagai **out of scope permanen** untuk implementasi single-page — memerlukan edit `inc/templates/service.php`.
⏳ **Belum di-commit, belum di-push**, sesuai instruksi eksplisit. File baru (`fix-service-pembuatan-kolam-baru.php`, `.htaccess` yang diperbarui) ada di working tree lokal, siap direview.

**STOP — menunggu review sebelum commit/push/deploy.**
