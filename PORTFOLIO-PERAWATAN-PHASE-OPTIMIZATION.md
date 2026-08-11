# PHASE — OPTIMASI 11 PORTFOLIO PERAWATAN KOLAM RENANG

Status: **implementasi lokal selesai, BELUM di-deploy** (script belum dijalankan di server, belum commit, belum push — sesuai instruksi).

Referensi: `PORTFOLIO-PERAWATAN-LIVE-AUDIT.md` (audit sebelumnya).
Script implementasi: `optimize-portfolio-perawatan.php`.

---

## 1. Daftar 11 Portofolio Target

| # | URL | Status di audit |
|---|-----|------------------|
| 1 | `/portofolio/perawatan-kolam-rutin/` | Lama, kualitas sudah baik |
| 2 | `/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/` | Lama, kualitas cukup baik |
| 3 | `/portofolio/perawatan-kolam-renang-di-sentul-bogor/` | Baru, konten tipis |
| 4 | `/portofolio/perawatan-kolam-renang-di-ciawi-bogor/` | Baru, konten tipis |
| 5 | `/portofolio/perawatan-kolam-renang-di-bogor-kota/` | Baru, konten tipis |
| 6 | `/portofolio/perawatan-kolam-renang-di-cibinong-bogor/` | Baru, konten tipis |
| 7 | `/portofolio/perawatan-kolam-renang-di-yasmin-bogor/` | Baru, konten tipis |
| 8 | `/portofolio/perawatan-kolam-renang-di-cijeruk-bogor-13/` | Baru, konten tipis, "mirip" #2 (lihat §4) |
| 9 | `/portofolio/jasa-perawawatan-kolam-renang-rutin-di-rancamaya/` | Baru, **placeholder bocor** (`JUDUL:`/`DESKRIPSI:`, typo "Perawawatan") |
| 10 | `/portofolio/perawatan-kolam-renang-di-bogor-raya/` | Baru, konten tipis |
| 11 | `/portofolio/perawatan-kolam-renang-di-karadenan-bogor/` | Baru, konten tipis |

Catatan: URL Rancamaya (`jasa-perawawatan-...`) **tetap mengandung typo "perawawatan"** — ini bagian dari slug URL, dan brief eksplisit melarang perubahan URL. Typo hanya diperbaiki di `title`/`h1`/konten, bukan di URL.

---

## 2. Perubahan per Portofolio

**#1 Perawatan Kolam Rutin (Bogor Kota, hotel)** — di luar cakupan "8 baru" Phase 4, konten sudah baik. Perubahan: `meta_description` baru (158 char), tambah section "Layanan Terkait" di akhir `content` (link ke money page + area Bogor Kota). Judul/H1/intro/isi utama **tidak diubah**.

**#2 Cijeruk (original, id foto 12)** — sama, di luar cakupan "8 baru". Perubahan: `meta_description` baru (149 char), tambah section "Layanan Terkait" (link ke money page, area Cijeruk, proyek Cijeruk #8, artikel air hijau).

**#3 Sentul** — `meta_description` baru (156 char). `intro` dipersingkat. `content` direstrukturisasi: H2 "Lingkup Pekerjaan di Sentul" (daftar 5 tugas — semua sudah disebut di teks asli), H2 "Hasil Pekerjaan", H2 "Layanan Terkait" (link money page, area Sentul, artikel vila jarang dipakai — relevan karena karakter Sentul sebagai area vila musiman sudah mapan di situs).

**#4 Ciawi** — sama pola dengan #3. `meta_description` 153 char. Link artikel vila jarang dipakai (Ciawi = jalur Puncak, banyak vila musiman).

**#5 Bogor Kota (baru)** — `meta_description` 153 char. Link tambahan ke portofolio "Perbaikan Kebocoran Kolam" (area sama, genuinely relevan sebagai bukti pengalaman multi-layanan di Bogor Kota).

**#6 Cibinong** — `meta_description` 154 char. Tidak ada link artikel tambahan (tidak ada karakter area yang cukup kuat untuk dikaitkan tanpa memaksakan).

**#7 Yasmin** — `meta_description` 141 char. Link artikel pH air (Yasmin = area hunian harian, relevan dengan tema jaga kualitas air rutin).

**#8 Cijeruk #13** — `meta_description` 150 char. Konten menekankan detail yang memang berbeda dari #2 (area endapan lebih banyak, pengecekan ulang filtrasi pasca-pembersihan — fakta yang sudah ada di teks asli, bukan karangan). Cross-link ke #2 dan artikel air hijau.

**#9 Rancamaya** — **FIX placeholder (Phase 1)**:
- `title`/`h1` lama: `"Jasa Perawawatan kolam renang rutin di rancamaya"` (typo + huruf kecil) → diganti `"Perawatan Kolam Renang Rutin di Rancamaya, Bogor"` — diambil **langsung** dari teks `"JUDUL: Perawatan Kolam Renang Rutin di Rancamaya, Bogor"` yang sudah tersimpan di field `intro` lama, hanya membuang prefix `JUDUL: `. Tidak ada kata baru yang dikarang.
- `content` lama diawali `"DESKRIPSI: "` → prefix dibuang, isi direstrukturisasi H2 tanpa mengubah fakta.
- `meta_description` baru 145 char.

**#10 Bogor Raya** — `meta_description` 151 char, pola sama dengan #3.

**#11 Karadenan** — `meta_description` 145 char, pola sama dengan #3.

---

## 3. DATA_REQUIRED per Portofolio

Tidak ada satupun dari 11 item yang mendapat tambahan: tanggal pengerjaan, durasi, nama klien, harga, garansi, atau material spesifik — karena data ini **tidak tersedia** di sumber manapun yang bisa saya akses (tidak ada akses DB langsung, hanya lewat script terkontrol). Ditandai `DATA_REQUIRED` untuk seluruh 11 item:

- Tanggal/periode pengerjaan aktual
- Durasi pengerjaan
- Nama klien / jenis properti spesifik (kecuali #1 yang sudah menyebut "hotel" di teks asli)
- Harga/biaya proyek
- Garansi yang diberikan untuk proyek spesifik ini
- Material/produk kimia spesifik yang digunakan (teks asli hanya menyebut proses, bukan merek/jenis produk)
- Foto before/after dengan caption penjelas (foto ada, tapi tanpa keterangan tanggal/tahap)

Tidak ada dari atribut ini yang saya isi dengan asumsi atau karangan.

---

## 4. Audit Dual-Portfolio Cijeruk (#2 vs #8)

Sesuai instruksi Phase 2, kedua halaman diaudit **terpisah**, bukan diasumsikan duplikat:

| Aspek | #2 (original) | #8 (`-13`) |
|---|---|---|
| Foto | `photo.php?id=12` | `photo.php?id=25` |
| Teks deskripsi | Generic: pemeriksaan air, kebersihan, sirkulasi, pompa, filter | Menyebut detail berbeda: "penumpukan kotoran/endapan lebih banyak", "sistem filtrasi diperiksa **setelah** pembersihan selesai" |
| URL | Berbeda | Berbeda |

**Kesimpulan**: foto berbeda + teks tidak identik kata-per-kata → **tidak ada bukti kuat** ini proyek yang sama. Sesuai aturan eksplisit brief ("judul/lokasi/jenis pekerjaan sama BUKAN otomatis duplikat"), kedua halaman **dipertahankan sebagai 2 proyek terpisah**. Tidak ada penghapusan, redirect, canonical silang, atau penggabungan. Sebagai gantinya, kedua halaman saling cross-link sebagai "proyek perawatan lain di Cijeruk" — memperkuat argumen brief bahwa banyaknya proyek di lokasi sama adalah bukti pengalaman lokal, bukan masalah.

---

## 5. Konfirmasi: Tidak Ada Portofolio yang Diperlakukan sebagai Duplikat

Dikonfirmasi eksplisit: **tidak ada** dari 11 portofolio ini — termasuk pasangan Cijeruk — yang di-delete, di-redirect, di-canonical-kan ke halaman lain, atau digabung hanya karena kesamaan judul/lokasi/jenis pekerjaan. Semua 11 URL tetap hidup independen dengan `type='portfolio'` masing-masing.

---

## 6. Internal Link yang Ditambahkan

| Prioritas | Target | Dipakai di |
|---|---|---|
| 1. Money page | `/layanan/jasa-perawatan-kolam-renang/` | Semua 11 item |
| 2. Area page | `/area/[lokasi]/` sesuai lokasi masing-masing | Semua 11 item |
| 3. Artikel relevan | `/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/` | Sentul, Ciawi (karakter area vila musiman) |
| | `/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/` | Yasmin (area hunian harian) |
| | `/artikel/cara-mengatasi-air-kolam-berwarna-hijau/` | Cijeruk #2, Cijeruk #8 (area lembab) |
| 4. Portofolio terkait | `/portofolio/perbaikan-kebocoran-kolam/` | Bogor Kota (baru) |
| | Cijeruk #2 ↔ Cijeruk #8 (cross-link) | Keduanya |

Anchor text bervariasi per item (bukan "Selengkapnya" berulang) — mis. "jasa perawatan kolam renang", "proyek perawatan lain yang pernah kami kerjakan di Cijeruk", "Cara Menjaga pH Air Kolam Tetap Ideal", dst. Cibinong, Bogor Raya, Karadenan, dan Perawatan Kolam Rutin **tidak** diberi link artikel tambahan — tidak ada karakter area yang cukup kuat untuk dikaitkan tanpa memaksakan relevansi.

---

## 7. Verifikasi URL (HTTP 200)

Semua 11 URL target + seluruh URL tujuan link (10 area page, 1 money page, 3 artikel, 2 portofolio silang) di-`curl` langsung ke situs live sebelum dipakai — **seluruhnya 200 OK**. Tidak ada satupun link yang dipasang tanpa verifikasi live terlebih dahulu.

---

## 8. Perubahan Database (Rencana Eksekusi Script)

Script `optimize-portfolio-perawatan.php` akan melakukan, untuk **masing-masing dari 11 baris** di tabel `pages` (`type='portfolio'`):

1. `SELECT id, title, h1, intro, content, meta_description` dulu untuk mengunci baris berdasarkan `url_path` + `type='portfolio'`.
2. `UPDATE ... WHERE id = :id AND url_path = :url_path AND type = 'portfolio'` — guard ganda id + url_path + type, sesuai Phase 7. **Tidak ada UPDATE berdasarkan title saja.**
3. Field yang diubah: `meta_description` (semua 11), `content` (semua 11 — 2 lama di-*append*, 9 baru ditulis ulang penuh), `intro` (9 baru saja), `title`+`h1` (Rancamaya saja).

Script **tidak** menyentuh tabel `portfolio` (sumber data admin) — perubahan ini murni pada representasi SEO (`pages`). Ini berarti jika admin membuka `edit-portfolio.php` dan menyimpan ulang salah satu dari 9 item baru, `portfolio_sync_seo_page()` akan **menimpa** `intro`/`content`/`title`/`h1` hasil optimasi ini kembali ke teks lama (karena field sumbernya di tabel `portfolio` tidak diubah). **Risiko durabilitas ini perlu diketahui admin** — lihat §11.

Script belum dijalankan — menunggu instruksi lebih lanjut sebelum admin mengeksekusinya via browser.

---

## 9. File yang Diubah/Ditambahkan

- **Baru**: `optimize-portfolio-perawatan.php` — script implementasi (di-lint via `php -l`, tanpa error).
- **Baru**: `PORTFOLIO-PERAWATAN-PHASE-OPTIMIZATION.md` — laporan ini.
- **Diubah**: `.htaccess` — menambahkan `optimize-portfolio-perawatan\.php` ke daftar `FilesMatch` yang diproteksi Basic Auth (baris 11).
- Tidak ada file lain yang disentuh. Tidak ada perubahan pada `inc/templates/portfolio.php`, `inc/portfolio-helpers.php`, homepage, halaman layanan lain, atau halaman area lain.

---

## 10. Regression Check

- `php -l optimize-portfolio-perawatan.php` → **no syntax errors**.
- Dry-run ekstraksi seluruh array `$items` (tanpa eksekusi DB) → 11 `meta_description` terverifikasi panjang 140–160 karakter, seluruhnya kata-kata berbeda (bukan template identik).
- Semua 27 URL (11 target + 16 tujuan link) di-curl live → seluruhnya HTTP 200.
- Script hanya menyentuh 11 baris `pages` yang dikunci via `id`+`url_path`+`type='portfolio'` — tidak ada query yang menyentuh baris lain, tabel `portfolio`, atau halaman non-portofolio.
- Belum dijalankan di server produksi (menunggu instruksi), sehingga belum ada regression live untuk diverifikasi lebih lanjut — akan dicek ulang live setelah dijalankan (Phase 8), sesuai pola yang sudah berjalan di fase-fase sebelumnya.

---

## 11. Hal yang Sengaja TIDAK Diubah

- **URL ke-11 item** — tidak ada satupun yang diubah, termasuk slug Rancamaya yang masih mengandung typo "perawawatan".
- **Tidak ada portofolio baru dibuat, dihapus, di-redirect, atau digabung** — termasuk pasangan Cijeruk.
- **Tabel `portfolio`** (sumber admin) tidak disentuh — hanya tabel `pages` (representasi SEO) yang diubah. Konsekuensinya: jika admin menyimpan ulang salah satu dari 9 portofolio baru lewat `edit-portfolio.php` tanpa memperbarui teks di form admin, hasil optimasi (intro/content/title baru) akan tertimpa balik ke versi lama. **Rekomendasi untuk fase berikutnya** (belum dieksekusi, hanya dicatat): pertimbangkan juga memperbarui `portfolio.title`/`portfolio.description` agar sinkron dan tahan terhadap re-save admin — tapi ini di luar cakupan brief kali ini sehingga sengaja tidak dilakukan sekarang.
- **Shared template** (`inc/templates/portfolio.php`) tidak diubah — tidak diperlukan untuk mencapai tujuan brief.
- **Homepage, halaman layanan lain, halaman area lain** — tidak disentuh sama sekali.
- **Schema/structured data** — tidak diubah (masih `Service` schema seperti sebelumnya, sesuai instruksi Phase 6 untuk tidak mengubah schema yang sudah valid, dan tidak membuat `aggregateRating`/testimonial palsu).
- **Section "Proyek Lainnya"** (3 item statis di tiap halaman portofolio) — tidak diubah, di luar cakupan.
- **Commit dan push** — belum dilakukan, sesuai instruksi eksplisit.

---

## Status & Langkah Selanjutnya

Implementasi lokal selesai. Menunggu instruksi Anda:
1. Jalankan `optimize-portfolio-perawatan.php` di server (via browser, Basic Auth) untuk menerapkan perubahan ke database.
2. Setelah itu saya akan memverifikasi ulang secara live (Phase 8) sebelum dianggap selesai.
3. Commit + push kode (`optimize-portfolio-perawatan.php`, `.htaccess`, laporan ini) — menunggu konfirmasi Anda, belum dilakukan.
