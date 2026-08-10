# PROBLEM PAGE SEO IMPLEMENTATION — PHASE 6
**Tanggal:** 2026-08-11
**Status deploy:** ⏳ **BELUM di-commit/push** — sesuai instruksi eksplisit "Jangan commit. Jangan push. Tunggu review." Semua perubahan ada di working tree lokal.

---

## Selected Target

```
SELECTED TARGET
URL: /layanan/perbaikan-kebocoran-kolam/
TITLE: Perbaikan Kebocoran Kolam | Jasa Kolam Renang Bogor
REASON: Dibandingkan 5 kandidat "problem" page yang dicek langsung (perbaikan-kebocoran-kolam, penyeimbangan-kimia-air-kolam, jasa-darurat-kolam-bocor-mendadak, perbaikan-sistem-sirkulasi-air, penggantian-pasir-filter) -- semuanya sama-sama thin (~88-94 kata, 0 gambar), sehingga kondisi awal tidak jadi pembeda. Yang membedakan: kebocoran adalah masalah struktural bernilai tinggi/mendesak (intent komersial terkuat), dan halaman ini punya dukungan aset PALING lengkap di antara semua kandidat -- bukti kerja (portofolio) yang tag & deskripsinya cocok 100%, artikel yang membahas topik ini secara eksplisit, DAN sudah ditautkan dari halaman /area/bogor-kota/ sejak Phase 5. Mengisi halaman ini menyelesaikan sebuah topical cluster yang sudah separuh terbentuk dari fase-fase sebelumnya, bukan memulai dari nol.
RELATED SERVICE: /layanan/perbaikan-kebocoran-kolam/ (target itu sendiri) + /layanan/waterproofing-kolam-renang/ (disebut eksplisit di Tahapan Pengerjaan langkah 4, terverifikasi 200)
RELATED AREA: /area/bogor-kota/ -- sudah menaut ke halaman ini sejak Phase 5; portofolio buktinya juga bertag "Bogor Kota"
RELATED PORTFOLIO: /portofolio/perbaikan-kebocoran-kolam/ -- "Perbaikan Kebocoran Kolam" (Bogor Kota), deskripsi & foto (photo.php?id=15) sudah ada & terverifikasi (getimagesize: 427x299)
RELATED ARTICLE: /artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/ -- punya section eksplisit "Mengabaikan Kebocoran Kecil", terverifikasi 200
```

**Page ID:** dibaca otomatis oleh skrip saat dijalankan (lihat §Database Changes) — tidak diasumsikan dari luar, query terkunci ke `url_path` + `type='service'`.

---

## Before

Dari fetch live (dilakukan saat riset target):

| Item | Nilai |
|---|---|
| Title (tag `<title>`) | `Perbaikan Kebocoran Kolam \| Jasa Kolam Renang Bogor` (51 char) |
| Meta description | `Deteksi dan perbaikan kebocoran kolam renang secara akurat — dari retak struktur hingga kebocoran pada pipa dan sambungan.` (122 char) — tidak menyebut Bogor, tidak ada ajakan bertindak |
| H1 | `Perbaikan Kebocoran Kolam` (1×) |
| H2 | 2× — "Pertanyaan Seputar Halaman Ini" (FAQ, shared), "Perbaikan Kebocoran Kolam" (CTA band, shared — **duplikat literal H1**, isu template yang sama seperti ditemukan di halaman Pembuatan Kolam Renang Baru sebelum Phase 3) |
| H3 | 0 |
| H4 | 1× — "Tahapan Pengerjaan" (langsung setelah H1, skip H2 & H3) + 2× footer (Navigasi, Area Layanan) |
| Word count | ±91 kata |
| Images | **0** |
| Internal links (di konten) | **0** |
| FAQ | 2 pertanyaan (cara tahu kolam bocor, berapa lama proses) |
| Schema `Service` | `name: "Perbaikan Kebocoran Kolam"`, `areaServed: "Bogor"` (string generik) |

---

## After

Diverifikasi statis (lint + parsing heading/link/word-count) — **belum live**, menunggu skrip dijalankan setelah deploy disetujui.

| Item | Nilai |
|---|---|
| Title (tag `<title>`, via `meta_title`) | `Perbaikan Kebocoran Kolam di Bogor \| Jasa Kolam Renang Bogor` — **60 karakter** |
| Meta description | `Kolam renang bocor atau air terus berkurang tanpa sebab jelas? Kami deteksi dan perbaiki kebocoran secara akurat di Bogor — survei & estimasi gratis.` — **149 karakter** |
| H1 | Tidak diubah — `Perbaikan Kebocoran Kolam` |
| H2 | **7 baru** di konten: "Mengenali Masalah", "Penyebab yang Perlu Diperiksa", "Solusi dan Penanganan", "Kapan Perlu Ditangani Teknisi", "Layanan yang Terkait", "Proyek yang Pernah Kami Kerjakan", "Konsultasi Perbaikan Kebocoran Kolam" — ditambah 2 H2 shared yang tetap ada (FAQ, CTA band, tidak disentuh) |
| H3 | **1 baru** — judul kartu portofolio ("Perbaikan Kebocoran Kolam") |
| H4 | **0 di konten** (footer Navigasi/Area Layanan tetap H4, shared, tidak disentuh) |
| Word count | **291 kata** (naik dari ±91) |
| Images | **1 baru** — `photo.php?id=15`, `width="427" height="299"`, `alt` deskriptif, `loading="lazy"` |
| Internal links (di konten) | **4 baru** — 1 artikel (kesalahan umum), 1 layanan terkait (waterproofing), 1 portofolio, 1 WhatsApp |
| FAQ | **3 pertanyaan** — 2 lama dipertahankan persis, 1 baru ("Sudah berapa lama menangani perbaikan kebocoran kolam renang?") memakai HANYA fakta terverifikasi (10+ tahun, 350+ proyek) |
| Schema `Service` | **Tidak diubah oleh skrip ini secara struktur** — `name`/`description`/`areaServed` otomatis mengikuti `title`/`intro` yang sebagian besar tidak berubah (title tetap sama nilainya); `areaServed: "Bogor"` (string generik) tetap seperti semula karena logic ini di `inc/templates/service.php` (shared, di luar scope — lihat §SHARED_TEMPLATE) |

---

## Files Modified

| File | Alasan | Ringkasan | Halaman lain terdampak? |
|---|---|---|---|
| `fix-problem-perbaikan-kebocoran.php` (baru) | Implementasi seluruh FIX Phase 6 — konten halaman ini 100% dari 1 baris tabel `pages`, tidak bisa diubah lewat kode | Skrip sekali-jalan yang meng-UPDATE `title`, `meta_title`, `meta_description`, `content`, `faq_json` HANYA untuk baris dengan `id` yang dibaca terlebih dahulu dari `url_path='/layanan/perbaikan-kebocoran-kolam/' AND type='service'` (lihat §Database Changes untuk detail proteksi) | **Tidak** — 1 baris saja |
| `.htaccess` | Melindungi skrip baru dengan Basic Auth yang sama seperti skrip lain | Menambahkan 1 nama file ke `FilesMatch` | Tidak memengaruhi proteksi file lain |

**TIDAK disentuh:** `inc/templates/service.php`, homepage, halaman layanan lain, halaman area, artikel lain, portofolio, skema database, routing, sitemap, robots.txt.

---

## Database Changes

| Item | Detail |
|---|---|
| Tabel | `pages` |
| Baris target | `url_path = '/layanan/perbaikan-kebocoran-kolam/'` DAN `type = 'service'` — `id` baris **dibaca dulu** oleh skrip sebelum UPDATE, lalu UPDATE dikunci ke `id` yang sama + `url_path` + `type` sekaligus (bukan cuma `url_path`/slug yang berpotensi ambigu jika ada baris ganda tak terduga) |
| Kolom yang diubah | `title` (nilai sama seperti sebelumnya, ditulis eksplisit — bukan perubahan nyata), `meta_title`, `meta_description`, `content`, `faq_json` |
| Kolom yang **tidak** disentuh | `h1`, `intro`, `url_path`, `type`, `area_ref`, `service_ref`, `status`, `sort_order`, `tier`, `target_keyword`, `cover_image` |
| Proteksi endpoint | Basic Auth via `.htaccess` (sama seperti semua skrip `fix-*.php`/`seed-*.php` lain di proyek ini) — **tidak** pernah publik tanpa proteksi |
| Idempotensi | Aman dijalankan berulang — nilai yang ditulis selalu sama persis, tidak ada duplikasi baris (UPDATE, bukan INSERT) |
| Kredensial | Tidak ditampilkan di skrip maupun laporan ini (memakai `inc/db.php` yang membaca `db-config.php` di server, sesuai pola proyek) |

Skrip **tidak dijalankan** oleh saya — hanya disiapkan. Nilai `before` (title/meta_title/meta_description lama) akan otomatis dibaca & dilaporkan dalam response JSON skrip pada saat admin menjalankannya, sebagai catatan audit tambahan di luar dokumen ini.

---

## Verified Business Facts Used

- **10+ Tahun Pengalaman** dan **350+ Proyek Selesai** — dipakai di 2 tempat: kalimat pengantar section "Proyek yang Pernah Kami Kerjakan", dan FAQ baru. Tidak ada angka baru dibuat.

---

## DATA_REQUIRED

| Item | Status |
|---|---|
| Testimoni khusus perbaikan kebocoran | Tetap `DATA_REQUIRED` — tabel `testimonials` tidak punya baris `published` (temuan berulang dari semua audit sebelumnya). Tidak ditambahkan. |
| Detail garansi perbaikan kebocoran (durasi/cakupan) | Tetap `DATA_REQUIRED` — tidak ada data terverifikasi, tidak disebut di halaman ini. |
| Rating/review | Tidak ada data asli — sengaja tidak ditambahkan. |

Portofolio, gambar, dan artikel relevan **TIDAK** termasuk `DATA_REQUIRED` — semuanya sudah tersedia & terverifikasi (lihat Selected Target).

---

## SHARED_TEMPLATE — OUT OF SCOPE

| Nama File | Masalah | Kenapa Tetap Di Luar Scope |
|---|---|---|
| `inc/templates/service.php` | H2 CTA band duplikat literal H1 (`render_cta_band($page['title'], ...)`) — sama seperti temuan pada halaman Pembuatan Kolam Renang Baru sebelum Phase 3 | Perbaikannya butuh edit template ini, memengaruhi **20 halaman layanan sekaligus**. Tidak disentuh. |
| `inc/templates/service.php` | Schema `Service.areaServed` selalu string generik `'Bogor'` (baris 35), bukan array 10 area spesifik seperti di `LocalBusiness` | Sama — perbaikannya di level template, memengaruhi 20 halaman. Tidak disentuh. |

Tidak ditemukan isu BARU yang memerlukan shared template selama implementasi Phase 6 ini — semua FIX yang diminta selesai murni lewat konten 1 baris database.

---

## Tests Performed

Karena perubahan **belum di-deploy**, pengujian berikut adalah verifikasi statis/lokal terhadap nilai yang sudah disiapkan:

| # | Test | Metode | Hasil |
|---|---|---|---|
| 1 | Skrip PHP valid | `php -l` | ✅ Lolos |
| 2 | Heading hierarchy valid (H1→H2→H3, tidak ada skip) | Parsing regex terhadap `$content` | ✅ 7× H2, 1× H3, 0× H4 — tidak ada skip level |
| 3 | Tidak ada heading duplikat baru | Review daftar heading | ✅ Semua 7 H2 baru unik, tidak ada yang duplikat H1 |
| 4 | 4 URL tujuan internal link valid | `curl` HTTP status **sebelum** ditautkan | ✅ Semua `200 OK`: artikel, layanan waterproofing, portofolio, area bogor-kota (untuk konteks Selected Target) |
| 5 | Gambar bukan gambar rusak, dimensi akurat | `getimagesize()` langsung terhadap `photo.php?id=15` (dicek di fase-fase sebelumnya) | ✅ 427×299, sesuai atribut yang ditulis |
| 6 | Deskripsi kartu portofolio tidak dikarang | Dicocokkan ke kalimat pertama deskripsi asli (fetch langsung halaman detail portofolio) | ✅ Identik kata-per-kata |
| 7 | Konten "Tahapan Pengerjaan" tidak diubah isinya | Bandingkan 6 langkah lama vs baru | ✅ Identik, hanya dipindah dari dalam `.info-box`/H4 ke `<ol>` di bawah H2 |
| 8 | FAQ lama dipertahankan persis | Bandingkan teks Q&A #1 dan #2 lama vs baru | ✅ Identik kata-per-kata |
| 9 | FAQ baru hanya memakai fakta terverifikasi | Review isi jawaban FAQ #3 | ✅ Hanya "10+ tahun"/"350+ proyek" |
| 10 | Meta title/description panjang wajar | Hitung karakter | ✅ 60 & 149 karakter |
| 11 | Query UPDATE dikunci ke 1 baris (id + url_path + type) | Review kode | ✅ Tidak ada risiko UPDATE massal/ambigu |
| 12 | Idempoten | Review logic (UPDATE murni, tidak ada INSERT) | ✅ Aman dijalankan berulang |

**Belum bisa diuji sampai deploy disetujui:** HTTP 200 live, canonical/robots live, tampilan visual di browser, CTA live, homepage & halaman lain live (regression terhadap situs live).

---

## Regression

**Terhadap kode/file:** Dijamin oleh scope query (1 baris, dikunci `id`+`url_path`+`type`) dan `inc/templates/service.php` tidak disentuh — 19 halaman layanan lain, homepage, 10 halaman area, semua artikel, dan semua item portofolio dijamin tidak berubah karena tidak ada baris/file mereka yang tersentuh.

**Terhadap situs live:** Belum bisa diverifikasi karena belum dideploy. Setelah disetujui untuk push & skrip dijalankan, langkah verifikasi yang direkomendasikan:
1. Fetch `/` (homepage) — pastikan tidak berubah.
2. Fetch `/layanan/pembuatan-kolam-renang-baru/` (hasil Phase 3) dan 1 halaman layanan lain — pastikan tidak berubah.
3. Fetch `/area/bogor-kota/` (hasil Phase 5) — pastikan tidak berubah (khususnya link yang sudah dibuat ke halaman ini masih valid).
4. Fetch `/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/` dan `/portofolio/perbaikan-kebocoran-kolam/` — pastikan tidak berubah.
5. Fetch `/layanan/perbaikan-kebocoran-kolam/` — pastikan heading/meta/link/gambar/FAQ baru tampil sesuai §After.

---

# FINAL STATUS

✅ Target dipilih berdasarkan data aktual (bukan asumsi) dari 5 kandidat yang dicek langsung.
✅ Implementasi selesai untuk seluruh FIX yang diminta — perubahan konten 1 baris database, tanpa menyentuh shared template.
🛑 2 isu (dari template `service.php`: duplikasi H2 CTA, `areaServed` generik) dikonfirmasi **tetap out-of-scope**.
⏳ **Belum di-commit, belum di-push**, sesuai instruksi eksplisit. File ada di working tree lokal, siap direview.

**STOP — menunggu review sebelum commit/push/deploy.**
