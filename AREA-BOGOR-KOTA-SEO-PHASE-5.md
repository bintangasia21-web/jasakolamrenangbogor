# AREA PAGE SEO IMPLEMENTATION — PHASE 5
**Target:** `/area/bogor-kota/` (satu halaman saja)
**Tanggal:** 2026-08-11
**Dasar:** `AREA-BOGOR-KOTA-SEO-AUDIT.md`
**Status deploy:** ⏳ **BELUM di-commit/push** — sesuai instruksi eksplisit "Jangan commit. Jangan push. Tunggu review." Semua perubahan ada di working tree lokal.

---

## Before

Diambil dari fetch live halaman (saat penyusunan audit):

| Item | Nilai |
|---|---|
| Title | `Jasa Kolam Renang Bogor Kota \| Pembuatan, Perawatan & Renovasi` (62 char) — tidak diaudit untuk diubah, tetap sama |
| Meta description | `Jasa kolam renang bogor kota terpercaya...` (150 char) — **"bogor kota" huruf kecil** |
| H1 | `Jasa Kolam Renang Bogor Kota — Terpercaya & Berpengalaman` (1×) |
| H2 | 4× — "Kolam Renang untuk Kawasan Bogor Kota", "Disesuaikan dengan Karakteristik Wilayah", "Pertanyaan Seputar Halaman Ini" (shared), "Konsultasikan Kebutuhan Kolam Renang Anda di Bogor Kota" (shared, CTA) |
| H3 | 4× — 4 judul kartu layanan (TIDAK ditautkan) |
| H4 | 1× — "Cakupan Wilayah Bogor Kota" (skip H3) + 2× footer (Navigasi, Area Layanan) |
| Word count (konten utama) | 302 kata |
| Service links | **0** — 4 nama layanan disebut tapi tidak ditautkan |
| Portfolio | **0 ditampilkan** — 2 item bertag "Bogor Kota" ada di database tapi tidak dimanfaatkan |
| Images | **0** |
| Internal link (konten, bukan nav/footer) | 0 |
| Fakta bisnis terverifikasi disebut? | Tidak |
| Schema | `LocalBusiness`, `BreadcrumbList`, `Service` (`serviceType`: "Perawatan Kolam Renang" — tidak representatif), `FAQPage` |

---

## After

Diverifikasi secara statis (lint PHP + parsing heading/link/word-count dari nilai yang akan disimpan) — **belum live**, menunggu skrip dijalankan setelah deploy disetujui.

| Item | Nilai |
|---|---|
| Title | **Tidak diubah** (di luar scope brief Phase 5 — hanya meta description yang diminta) |
| Meta description | `Jasa kolam renang Bogor Kota terpercaya — pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi filter/pompa. Konsultasi gratis via WhatsApp.` — **152 karakter**, "Bogor Kota" kapital benar |
| H1 | Tidak diubah |
| H2 | 5× — 3 lama (Kolam Renang untuk Kawasan..., Disesuaikan dengan Karakteristik..., FAQ shared, CTA shared) + **1 baru**: "Proyek Kami di Bogor Kota" |
| H3 | **7×** — 4 judul kartu layanan (**sekarang ditautkan**) + "Cakupan Wilayah Bogor Kota" (**naik dari H4**) + **2 baru**: judul 2 kartu portofolio |
| H4 | **0 di konten** (footer Navigasi/Area Layanan tetap H4, shared, tidak disentuh) |
| Word count (konten utama) | **384 kata** (naik dari 302) |
| Service links | **4/4** — semua judul kartu layanan sekarang ditautkan ke halaman `/layanan/*` yang sesuai |
| Portfolio | **2 ditampilkan & ditautkan** — "Perawatan Kolam Rutin" dan "Perbaikan Kebocoran Kolam", keduanya dengan gambar asli |
| Images | **2 baru** — `photo.php?id=16` (738×414) dan `id=15` (427×299), keduanya dengan `alt`/`width`/`height`/`loading="lazy"` |
| Internal link (konten) | **8 baru** — 4 layanan, 2 portofolio, 1 artikel (musim hujan), 1 layanan tambahan (perbaikan kebocoran, disebutkan kontekstual di paragraf) |
| Fakta bisnis terverifikasi disebut? | **Ya** — "10+ tahun pengalaman" dan "350+ proyek" disebut natural sebagai pengantar section portofolio (bukan section besar terpisah) |
| Schema | **Tidak diubah** — `Service.serviceType` tetap "Perawatan Kolam Renang" (di-generate `area.php`, di luar scope, lihat §SHARED_TEMPLATE) |

---

## Files Modified

| File | Alasan | Ringkasan | Halaman lain terdampak? |
|---|---|---|---|
| `fix-area-bogor-kota.php` (baru) | Implementasi FIX 1-9 dari brief — konten halaman ini 100% dari 1 baris tabel `pages`, tidak bisa diubah lewat kode | Skrip sekali-jalan yang meng-UPDATE kolom `meta_description` dan `content` HANYA untuk baris `url_path='/area/bogor-kota/' AND type='area'` | **Tidak** — query `WHERE` eksplisit ke 1 baris |
| `.htaccess` | Melindungi skrip baru dengan Basic Auth yang sama seperti skrip lain | Menambahkan 1 nama file ke `FilesMatch` | Tidak memengaruhi proteksi file lain |

**TIDAK disentuh** (sesuai instruksi): `inc/templates/area.php`, `/area/sentul/`, `/area/cibinong/`, area lain, homepage, halaman layanan, artikel, skema database, routing, sitemap, robots.txt.

---

## Verified Business Facts Used

- **10+ Tahun Pengalaman** dan **350+ Proyek Selesai** — dipakai apa adanya dalam 1 kalimat pengantar section portofolio: *"Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut beberapa contoh pekerjaan kami khusus di kawasan Bogor Kota."* — ditempatkan di titik yang semantik relevan (pengantar bukti kerja), bukan section terpisah yang berdiri sendiri.

Tidak ada angka baru dibuat atau diubah.

---

## DATA_REQUIRED

Tidak ada item baru yang menjadi blocker implementasi Phase 5 ini — audit sebelumnya sudah mengonfirmasi portofolio & fakta bisnis yang dibutuhkan SUDAH tersedia. Sisa `DATA_REQUIRED` dari audit (testimoni khusus Bogor Kota, detail garansi) **tetap belum tersedia** dan **tidak ditambahkan** ke halaman (sesuai instruksi, tidak mengarang):

| Item | Status |
|---|---|
| Testimoni pelanggan khusus Bogor Kota | Tetap `DATA_REQUIRED` — tabel `testimonials` tidak punya baris `published`, tidak ditambahkan ke halaman. |
| Detail garansi (durasi/cakupan) | Tetap `DATA_REQUIRED` — tidak disebut di halaman ini (sama seperti sebelumnya, tidak diubah). |

---

## SHARED_TEMPLATE — OUT OF SCOPE

| Nama File | Fungsi | Masalah | Kenapa Tetap Di Luar Scope |
|---|---|---|---|
| `inc/templates/area.php` | Merender semua 10 halaman `type='area'`, termasuk schema `Service` (baris 66-68) yang selalu hardcode `'serviceType' => 'Perawatan Kolam Renang'` | Schema `Service` halaman ini menyatakan diri hanya "Perawatan Kolam Renang", padahal konten halaman (setelah Phase 5) membahas 4 layanan setara (Pembuatan, Perawatan, Renovasi, Instalasi) — `DATA_CONFLICT` yang sudah tercatat di audit | Perbaikannya butuh edit `area.php`, memengaruhi 10 halaman sekaligus. Sesuai instruksi eksplisit brief Phase 5: **"Jangan memperbaikinya pada Phase 5"** — dicatat ulang, tidak dieksekusi. |

Tidak ditemukan isu BARU yang memerlukan shared template selama implementasi ini — 9 dari 9 area FIX yang diminta selesai murni lewat konten 1 baris database.

---

## Tests Performed

Karena perubahan **belum di-deploy**, pengujian berikut adalah verifikasi statis/lokal terhadap nilai yang sudah disiapkan:

| # | Test | Metode | Hasil |
|---|---|---|---|
| 1 | Skrip PHP valid | `php -l` | ✅ Lolos |
| 2 | Meta description sesuai target ("Bogor Kota" kapital) | Baca nilai `$metaDescription` | ✅ 152 karakter, kapitalisasi benar |
| 3 | Heading hierarchy valid (tidak ada skip level) | Parsing regex terhadap `$content` | ✅ H2→H3 konsisten di semua section, 0 skip |
| 4 | 4 link layanan target valid | Sudah dicek `200 OK` saat audit (§21 audit) | ✅ Semua 4 URL sudah diverifikasi sebelumnya |
| 5 | 2 link portofolio target valid | Sudah dicek saat audit | ✅ `/portofolio/perawatan-kolam-rutin/`, `/portofolio/perbaikan-kebocoran-kolam/` — 200 OK |
| 6 | Link artikel target valid | Sudah dicek saat audit | ✅ `/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/` — 200 OK |
| 7 | Gambar bukan gambar rusak, dimensi akurat | `getimagesize()` langsung terhadap `photo.php?id=16` & `id=15` (dicek saat audit fase sebelumnya) | ✅ 738×414 dan 427×299, sesuai atribut yang ditulis |
| 8 | Deskripsi kartu portofolio tidak dikarang | Dicocokkan ke kalimat pertama deskripsi asli proyek (fetch langsung dari halaman detail portofolio) | ✅ Identik kata-per-kata dengan sumber asli |
| 9 | Query UPDATE hanya menyasar 1 baris | Review `WHERE url_path = ... AND type = 'area'` | ✅ Spesifik |
| 10 | Idempoten | Review logic `rowCount()` + fallback pengecekan | ✅ Sesuai pola skrip-skrip sebelumnya |
| 11 | Tidak menyentuh `area.php`/shared schema | Review isi commit/diff | ✅ Hanya 2 file: skrip baru + `.htaccess` |

**Belum bisa diuji sampai deploy disetujui:** HTTP 200 live, canonical/robots live, tampilan visual di browser, CTA live, homepage & halaman lain live (regression terhadap situs live).

---

## Regression Check

**Terhadap kode/file:** Dijamin oleh scope query (`WHERE url_path = '/area/bogor-kota/' AND type = 'area'`, satu baris) dan `inc/templates/area.php` tidak disentuh sama sekali — 9 halaman area lain (Sentul, Puncak, Ciawi, Cibinong, Yasmin, Cijeruk, Rancamaya, Bogor Raya, Karadenan) dijamin tidak berubah karena tidak ada baris/file mereka yang tersentuh oleh query maupun oleh perubahan `.htaccess`.

**Terhadap situs live:** Belum bisa diverifikasi karena belum dideploy. Setelah disetujui untuk push & skrip dijalankan, langkah verifikasi yang direkomendasikan:
1. Fetch `/` (homepage) — pastikan tidak berubah.
2. Fetch `/area/sentul/` dan `/area/cibinong/` — pastikan tidak berubah (sanity check shared-template tidak tersentuh).
3. Fetch `/area/bogor-kota/` — pastikan heading/meta/link/gambar baru tampil sesuai §After.

---

# FINAL STATUS

✅ Implementasi selesai untuk 9 area FIX (meta description, heading, fakta bisnis, internal link layanan, portofolio, gambar, related content) — seluruhnya perubahan konten 1 baris database.
🛑 1 isu (`Service.serviceType` di schema) dikonfirmasi **tetap out-of-scope** — `inc/templates/area.php`, berdampak ke 10 halaman.
⏳ **Belum di-commit, belum di-push**, sesuai instruksi eksplisit. File ada di working tree lokal, siap direview.

**STOP — menunggu review sebelum commit/push/deploy.**
