# HOMEPAGE SEO AUDIT — PHASE 1
**Target:** `https://jasakolamrenangbogor.com/` (homepage only)
**Tanggal audit:** 2026-08-10
**Metode:** Pembacaan source code (`index.php`, `inc/render-partials.php`, `schema.sql`, `.htaccess`, `robots.txt`) + fetch langsung HTML/header live homepage. Tidak ada kode, database, URL, route, sitemap, robots.txt, atau halaman lain yang diubah pada fase ini.

---

## 1. Executive Summary

Homepage sudah punya fondasi teknis yang solid: server-rendered penuh (bukan JS-rendered), satu H1, schema LocalBusiness + FAQPage, canonical benar, semua asset (CSS/JS/gambar/sitemap) dapat diakses (200 OK), dan banyak internal link ke layanan/area/portofolio/artikel/kontak.

Namun ada beberapa masalah nyata yang menahan performa SEO dan konversi:

1. **Section Testimonial kosong** ("Testimoni Segera Hadir") — salah satu sinyal trust terpenting di halaman paling penting situs sama sekali tidak terisi.
2. **Trust bar nyaris kosong** — dari 4 slot trust signal yang sudah disiapkan di kode (tahun berdiri, pelanggan aktif, tim profesional, wilayah), cuma 1 yang terisi data.
3. **Title 70 karakter & meta description 178 karakter** — keduanya berpotensi terpotong di hasil pencarian Google.
4. **Section "Layanan Kami" menampilkan 20 kartu sekaligus** tanpa batas, jauh lebih panjang dari section lain yang memang dibatasi (Artikel dibatasi 3), bikin homepage sangat panjang dan kurang scannable.
5. **Ada 1 item portofolio dengan judul rusak** — "Perawatan Kolam Renang di (area belum dipilih)" — tampil apa adanya di homepage dan halaman detailnya (bukti placeholder AI yang belum diisi ulang oleh admin, bukan bug di kode; lihat memory proyek soal ini).
6. **Section artikel homepage menampilkan 3 artikel TERLAMA** (berdasarkan urutan `id`), bukan artikel terbaru/paling relevan — termasuk melewatkan artikel "Biaya Perawatan Kolam Renang" yang punya intent komersial tinggi.
7. Klaim angka "10+ Tahun Pengalaman" dan "350+ Proyek Selesai" **tidak bisa diverifikasi keasliannya** dari source code — dan kebetulan identik dengan angka contoh di file template awal proyek (`assets/js/data.default.json`), sehingga patut dicek ulang ke pemilik bisnis apakah ini angka riil atau masih placeholder lama yang terbawa.
8. Semua `<img>` di homepage **tidak punya atribut `width`/`height`** — berisiko Cumulative Layout Shift (Core Web Vitals).
9. Twitter Card tidak lengkap (cuma `twitter:card`, tanpa `twitter:title`/`twitter:description`/`twitter:image`).

Tidak ada perubahan yang dilakukan. Semua temuan di bawah murni observasi + rekomendasi untuk persetujuan sebelum Phase 2.

---

## 2. Files Involved

| File | Peran |
|---|---|
| `index.php` | Controller **sekaligus** view homepage. Query langsung ke DB (bukan lewat `page-router.php`/`inc/templates/*.php` seperti halaman lain), berisi `<head>`-nya sendiri (tidak memanggil `render_head()`), dan membangun footer-nya sendiri secara manual (tidak memanggil `render_footer()` — lihat catatan di §13). |
| `inc/render-partials.php` | Helper yang **dipakai** index.php: `render_header_nav()`, `render_local_business_ld()`, `render_cta_band()`, `render_contact_block()`, `page_text()`, `get_page_sections()`, `h()`, `short_desc()`, `placeholder_svg()`. Helper yang **tidak** dipakai index.php meski tersedia: `render_head()`, `render_breadcrumbs()` (memang tidak perlu breadcrumb di homepage), `render_footer()`, `render_faq_block()` (FAQ homepage pakai markup+schema manual sendiri di index.php, bukan fungsi ini). |
| `inc/db.php` | Koneksi PDO ke MySQL. |
| `photo.php` | Serving gambar portofolio/artikel dari BLOB database. |
| `assets/css/style.css` | Stylesheet utama (200 OK). |
| `assets/js/main.js` | Nav toggle mobile, inisialisasi peta area (Leaflet + `window.AREA_MAP_DATA`). |
| `assets/js/reveal.js` | Animasi scroll-reveal. |
| CDN: `unpkg.com/leaflet@1.9.4`, `fonts.googleapis.com` (Inter) | Dependensi eksternal, tidak self-hosted. |

**Sumber data (tabel MySQL, dibaca langsung oleh `index.php`, bukan lewat cache):**
`business` (row id=1), `areas`, `faq`, `portfolio`, `pages` (type=`service` dan type=`article`, status=`published`), `testimonials` (status=`published`), `page_sections` (page_key=`home`, untuk field-field yang bisa diedit lewat tab "Halaman Utama").

**Catatan arsitektur penting:** `<title>` homepage **hardcode** di `index.php` (baris 97), dan meta description homepage memakai `business.description` (field yang sama dipakai di footer & OG tag di semua halaman). Berbeda dari semua halaman lain (area/layanan/artikel/portofolio) yang punya field `meta_title`/`meta_description` sendiri-sendiri yang bisa diedit admin per halaman — **homepage tidak punya field SEO title/description terpisah yang bisa diedit lewat panel admin sama sekali.**

---

## 3. Current SEO Head

Diambil langsung dari HTML live (`curl` ke `https://jasakolamrenangbogor.com/`), bukan dari asumsi kode.

| Elemen | Nilai Aktual | Panjang | Penilaian | Alasan |
|---|---|---|---|---|
| `<title>` | `Jasa Kolam Renang Bogor \| Pembuatan, Perawatan & Renovasi Kolam Renang` | 70 karakter | **NEEDS_IMPROVEMENT** | Google umumnya memotong title di sekitar 555-600px (~55-60 karakter untuk font default); 70 karakter berisiko terpotong di SERP. |
| `meta description` | `Jasa Kolam Renang Bogor melayani pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi sistem air untuk rumah tinggal, villa, dan resort di wilayah Bogor dan sekitarnya.` | 178 karakter | **NEEDS_IMPROVEMENT** | Melebihi batas tampil umum Google (~155-160 karakter), kemungkinan terpotong. |
| `canonical` | `https://jasakolamrenangbogor.com/` | — | **GOOD** | Self-referencing, non-www (konsisten dengan redirect www→non-www di `.htaccess`), trailing slash konsisten. |
| `robots` (meta) | `index, follow` | — | **GOOD** | Homepage memang harus terindeks. |
| `viewport` | `width=device-width, initial-scale=1.0` | — | **GOOD** | Standar responsive. |
| `og:type` | `website` | — | **GOOD** | Tipe tepat untuk homepage. |
| `og:title` / `og:description` / `og:url` | Sama seperti title/description di atas | — | **NEEDS_IMPROVEMENT** | Mewarisi masalah panjang yang sama dari title/description. |
| `og:image` | `https://jasakolamrenangbogor.com/assets/img/og-image.svg` | — | **PROBLEM** | File **ada** (dicek, 200 OK, `image/svg+xml`) — TAPI mayoritas platform (Facebook, WhatsApp, LinkedIn) **tidak merender SVG** untuk preview OG image, hanya raster (PNG/JPG). Preview link kemungkinan tidak muncul gambar sama sekali saat dibagikan. |
| `twitter:card` | `summary_large_image` | — | **PROBLEM** | Tipe ini butuh `twitter:image` untuk berfungsi, tapi `twitter:image`, `twitter:title`, `twitter:description` **tidak ada sama sekali** di `<head>`. Twitter Card ini efektif tidak lengkap/tidak akan render dengan benar. |
| `lang` (di `<html>`) | `id` | — | **GOOD** | Benar untuk konten Bahasa Indonesia. |
| `hreflang` | Tidak ada | — | **INFO** | Wajar untuk situs satu bahasa/satu region, tidak perlu. |
| Font-loading | `preconnect` ke Google Fonts, lalu `<link rel="stylesheet">` render-blocking | — | **NEEDS_IMPROVEMENT** | Tidak pakai `font-display` atau preload font file langsung; render-blocking font CSS bisa memperlambat First Contentful Paint. |
| CSS Leaflet | Dimuat penuh (`leaflet.css` dari CDN) di `<head>` untuk SEMUA pengunjung | — | **NEEDS_IMPROVEMENT** | Peta area cuma dipakai di satu section (`#area-map`); memuat CSS peta di head untuk semua pengunjung (termasuk yang tidak scroll ke situ) menambah render-blocking request yang tidak selalu perlu. |

---

## 4. Current Content Structure

Dikelompokkan sesuai urutan render aktual di `index.php`:

| # | Section (id) | Isi Aktual | Search Intent | Nilai SEO | Masalah | Rekomendasi |
|---|---|---|---|---|---|---|
| 1 | Hero | H1 + lead + 2 CTA (WA, "Lihat Proyek") + 3 angka statistik | Menjawab intent utama "jasa kolam renang Bogor" langsung di atas fold | Tinggi (H1 + lead mengandung entitas utama: kota, jenis layanan) | H1 punya bug **spasi ganda**: `"Perawatan  Rutin"` (dua spasi). Angka statistik lihat §7 (Data Integrity). | Perbaiki spasi ganda di konten (bukan kode — nilainya di `page_sections`). Pertimbangkan verifikasi ulang angka statistik. |
| 2 | Trust bar | Cuma 1 dari 4 slot trust terisi ("Bogor — Fokus Wilayah Layanan") | Trust/kredibilitas | Rendah saat ini (nyaris kosong) | 3 kolom database (`yearFounded`, `activeCustomers`, `employeeCount`) kosong sehingga section hampir tidak berguna | Isi data lewat panel admin (Info Bisnis) — bukan perubahan kode. |
| 3 | Tentang (`#tentang`) | Deskripsi bisnis + 4 alasan memilih ("Berpengalaman", "Garansi", "Harga Transparan", "Respon Cepat") + box "Material & Standar Kerja" + "Proses Kerja Singkat" | Trust + jawaban "kenapa pilih kami" | Baik — konten deskriptif relevan lokal (Puncak, perbukitan Bogor) | Klaim "Garansi Pekerjaan", "Material berkualitas dari merek terpercaya" tidak terverifikasi (lihat §7) | Konten sudah cukup baik strukturnya; hanya perlu backing data untuk klaim (lihat §7). |
| 4 | Layanan (`#layanan`) | **20 kartu layanan**, semuanya ditampilkan (query tanpa LIMIT), tiap kartu anchor "Selengkapnya →" | Commercial intent tinggi (calon pelanggan cari layanan spesifik) | Baik secara cakupan topikal, tapi **section terpanjang di homepage** | Tidak ada batas jumlah (`ORDER BY sort_order, title` tanpa `LIMIT`), beda perlakuan dengan section Artikel yang dibatasi 3. Anchor text "Selengkapnya →" berulang 20x (identik). | Pertimbangkan menampilkan subset kurasi (mis. 6-8 layanan inti) + tombol "Lihat Semua Layanan", konsisten dengan pola section lain. |
| 5 | Area (`#area`) | Peta Leaflet + 10 kartu area | Local SEO — sangat relevan | Tinggi, mencakup 10 wilayah nyata | Tidak ada masalah berarti | — |
| 6 | Masalah (`#masalah`) | 10 kartu "masalah kolam" ditautkan ke layanan terkait | Menjawab intent problem-aware ("kolam bocor", "air keruh", dst) | Tinggi — bagus untuk menangkap long-tail query berbasis masalah | Tidak ada masalah berarti | — |
| 7 | Proyek (`#proyek`) | 7 kartu portofolio, semua ditampilkan (tanpa limit) | Trust/bukti kerja (social proof) | Baik | 1 dari 7 kartu berjudul **"Perawatan Kolam Renang di (area belum dipilih)"** — placeholder yang belum diisi ulang, tampil ke publik. Lihat §7 & §13. | Perbaikan **konten** (lewat panel admin Portofolio), bukan kode. |
| 8 | Jenis Pelanggan | 6 kartu tipe properti (Rumah Tinggal, Villa, Hotel, dst) | Topical relevance/segmentasi audiens | Cukup baik | Item pakai H4, bukan H3 (lihat §5) | Standarisasi heading level. |
| 9 | Panduan/Artikel (`#panduan`) | 3 kartu artikel (dibatasi `LIMIT 3`) | Topical authority + engagement | Baik secara mekanisme (dibatasi, konsisten dengan UX yang baik) | **Menampilkan 3 artikel dengan `id` terkecil** (paling lama dibuat di antara 10 artikel yang ada), bukan artikel terbaru atau paling relevan secara komersial (mis. artikel "Biaya Perawatan" tidak tampil di sini). | Lihat §11 (Content Gap) dan §14. |
| 10 | FAQ (`#faq`) | 5 pertanyaan (global, dari tabel `faq`, sama di semua halaman yang menampilkannya) | Menjawab pertanyaan umum, berpotensi featured snippet | Baik, sudah ada `FAQPage` schema | Tidak ada masalah berarti | — |
| 11 | Testimonial (`#testimonial`) | **KOSONG** — menampilkan empty-state "Testimoni Segera Hadir" | Trust/social proof | **Tidak ada nilai SEO/trust saat ini** | Tabel `testimonials` tidak punya baris berstatus `published` | Lihat §7 & §14 (MUST FIX secara bisnis, tapi ini pengisian konten, bukan kode). |
| 12 | CTA Band | "Butuh Jasa Kolam Renang di Bogor?" + tombol WA | Konversi | Baik, jelas & konsisten dengan pola CTA di semua halaman | Tidak ada masalah berarti | — |
| 13 | Kontak (`#kontak`) | Info kontak (telepon, email, alamat, jam operasional) + peta lokasi + tombol WA | Local SEO + konversi | Baik | Tidak ada masalah berarti | — |
| 14 | Footer | Deskripsi bisnis, navigasi, 4 area + link "Lihat Semua Area" | Internal linking + trust | Baik | Footer ini **duplikat manual** dari `render_footer()`, bukan memanggilnya (lihat §13) | — |

---

## 5. Search Intent Evaluation

Intent utama yang dievaluasi: **"jasa kolam renang Bogor"**.

| Dimensi | Evaluasi |
|---|---|
| **Relevance** | Kuat. H1, lead, title, meta description, dan hampir semua section (Layanan, Area, Masalah) secara eksplisit menyebut kombinasi "kolam renang" + "Bogor"/nama wilayah spesifik secara alami (tidak stuffing — variasi kata cukup natural: pembuatan, perawatan, renovasi, instalasi). |
| **Topical coverage** | Sangat luas untuk satu homepage: 20 layanan, 10 area, 10 masalah umum, portofolio, artikel edukasi, FAQ. Cakupan topik ini justru **lebih dari cukup** — risikonya justru overload (lihat §4 poin Layanan). |
| **Clarity** | H1 jelas menyatakan apa yang ditawarkan bisnis. Namun H1 mengandung bug spasi ganda yang sedikit mengganggu clarity visual. |
| **Local relevance** | Sangat kuat — 10 area riil disebut eksplisit (Sentul, Puncak, Ciawi, Bogor Kota, Cibinong, Yasmin, Cijeruk, Rancamaya, Bogor Raya, Karadenan), alamat lengkap ada di schema LocalBusiness. |
| **Commercial intent** | Kuat di CTA (WhatsApp muncul 5x, telepon 2x), tapi section paling penting untuk meyakinkan keputusan beli (**Testimonial**) kosong — ini melemahkan konversi dari visitor dengan intent tinggi yang butuh validasi sosial sebelum menghubungi. |
| **Trust** | Sebagian kuat (LocalBusiness schema lengkap, garansi disebutkan, proses kerja jelas), sebagian lemah (trust bar nyaris kosong, testimonial kosong, klaim angka tidak terverifikasi — lihat §7). |
| **Conversion** | CTA jelas dan berulang di titik-titik strategis (hero, tiap kartu layanan/masalah, band CTA, kontak, floating WA button) — mekanismenya sudah baik, hanya kurang didukung trust signal yang memadai. |

**Kesimpulan:** Homepage secara struktural sudah menjawab intent "jasa kolam renang Bogor" dengan baik dari sisi cakupan topik dan local relevance. Titik lemah utama bukan pada relevansi topik, melainkan pada **kelengkapan trust signal** (testimonial kosong, trust bar kosong, klaim tak terverifikasi) yang seharusnya mengonversi visitor dengan intent tinggi.

---

## 6. Heading Audit

Struktur aktual (hasil parsing HTML live):

```
H1: Jasa Kolam Renang Bogor untuk Pembangunan, Renovasi & Perawatan  Rutin
├── H2: Tentang Jasa Kolam Renang Bogor
│    └── (H4 × 6 — LANGSUNG dari H2 ke H4, TANPA H3)
├── H2: Solusi Lengkap Kolam Renang Anda
│    └── H3 × 20 (satu per layanan)
├── H2: Melayani Kota Bogor & Kabupaten Bogor serta Sekitarnya
│    └── H3 × 10 (satu per area)
├── H2: Masalah yang Paling Sering Kami Tangani
│    └── H3 × 10 (satu per masalah)
├── H2: Contoh Pekerjaan Kami
│    └── H3 × 7 (satu per proyek portofolio)
├── H2: Melayani Berbagai Jenis Properti
│    └── (H4 × 6 — LANGSUNG dari H2 ke H4, TANPA H3)
├── H2: Tips & Panduan Perawatan Kolam Renang
│    └── H3 × 3 (satu per artikel yang tampil)
├── H2: Pertanyaan yang Sering Diajukan
│    └── (tidak ada heading — pertanyaan pakai <summary>, bukan heading)
├── H2: Kata Pelanggan Kami
│    └── (kosong — empty-state tanpa heading tambahan)
├── H2: Butuh Jasa Kolam Renang di Bogor?  (CTA band)
└── H2: Hubungi Kami
     └── H3: Informasi Kontak
```

**Temuan:**

| Isu | Prioritas | Detail |
|---|---|---|
| Hanya 1 H1 | INFO (baik) | Sesuai praktik terbaik. |
| Section "Tentang" dan "Jenis Pelanggan" melompat H2 → H4 (skip H3) | **MEDIUM** | Section lain (Layanan, Area, Masalah, Proyek, Panduan) konsisten pakai H2 → H3 untuk kartu-kartu di dalamnya. Dua section ini beda pola, memutus hierarki semantik yang konsisten. |
| Section FAQ tidak pakai heading untuk tiap pertanyaan | **LOW** | `<summary>` valid secara HTML/aksesibilitas untuk `<details>`, tapi kehilangan potensi sinyal heading granular untuk tiap pertanyaan (meski `FAQPage` schema sudah mengompensasi ini di level data terstruktur). |
| Section "Layanan" & "Masalah" & "Area" masing-masing punya 10-20 H3 sejenis | **LOW–MEDIUM** | Bukan salah secara teknis, tapi total 51 H3 di satu halaman membuat outline halaman sangat padat/panjang. |

**Rekomendasi struktur ideal (TIDAK diterapkan sekarang):** Samakan semua section kartu (Tentang, Jenis Pelanggan) untuk pakai H3 pada tiap item, konsisten dengan section lain — supaya tidak ada lompatan level heading di mana pun di halaman.

---

## 7. Data Integrity Audit

Semua angka/klaim bisnis yang ditemukan di homepage:

| Klaim | Lokasi | Sumber | Status |
|---|---|---|---|
| "10+ Tahun Pengalaman" | Hero stats | `business.yearsExperience` (DB) | **NOT_VERIFIABLE** — nilai memang tersimpan di database dan konsisten dirender, tapi tidak ada dokumen/bukti di source code yang membuktikan keakuratannya. **Catatan tambahan:** angka ini identik dengan nilai contoh (`"yearsExperience": 10`) di `assets/js/data.default.json` — file template/seed awal proyek. Perlu konfirmasi ke pemilik bisnis apakah ini angka riil yang sudah diperbarui atau masih nilai contoh yang terbawa. |
| "350+ Proyek Selesai" | Hero stats | `business.projectsDone` (DB) | **NOT_VERIFIABLE** — sama seperti di atas; juga identik dengan nilai contoh (`"projectsDone": 350`) di `data.default.json`. |
| "10+ Area Layanan Utama" | Hero stats | `COUNT()` otomatis dari tabel `areas` | **VERIFIED** — ini bukan angka manual, dihitung langsung dari jumlah baris nyata di database (saat ini benar ada 10 baris di tabel `areas`). |
| Trust bar: "Sejak [tahun]" | Trust bar | `business.yearFounded` | **DATA_REQUIRED** — kolom kosong/NULL, item ini tidak tampil sama sekali di halaman live saat ini. |
| Trust bar: "[N] Pelanggan Aktif" | Trust bar | `business.activeCustomers` | **DATA_REQUIRED** — kolom kosong. |
| Trust bar: "±[N] Tim Profesional" | Trust bar | `business.employeeCount` | **DATA_REQUIRED** — kolom kosong. |
| "Setiap pengerjaan renovasi dan pembuatan kolam disertai garansi tertulis" | Section Tentang | Teks statis hardcode di `index.php`, bukan dari kolom data terstruktur | **NOT_VERIFIABLE** — tidak ada dokumen kebijakan garansi yang bisa dicek dari source code. |
| "Puluhan tahun pengalaman menangani kolam renang berbagai skala" | Section Tentang | Teks statis hardcode | **DATA_CONFLICT** — kalimat ini menyiratkan "puluhan tahun" (>20), sementara hero stats di atasnya menyatakan **"10+ Tahun Pengalaman"**. Dua klaim yang tampil di halaman yang sama saling tidak konsisten. |
| "Waterproofing Teruji" / "Filtrasi Berkualitas" / "Tim Berpengalaman" (badge) | Section Tentang | Teks statis hardcode | **NOT_VERIFIABLE** — klaim kualitas generik tanpa sertifikasi/data pendukung yang bisa diverifikasi. |
| `priceRange: "$$"` | JSON-LD `LocalBusiness` | `business.priceRange` (DB) | **NOT_VERIFIABLE** — tidak ada halaman/daftar harga di situs yang mendukung klasifikasi tingkat harga ini. |
| Testimoni pelanggan (nama, isi, area) | Section Testimonial | Tabel `testimonials` | **DATA_REQUIRED** — tidak ada satu pun baris berstatus `published` saat ini; section tampil kosong. |
| Rating/ulasan bintang | — | — | **NOT_VERIFIABLE / tidak ada** — tidak ditemukan elemen rating di mana pun di homepage maupun schema (`aggregateRating` memang **tidak** digunakan — ini justru **benar**, karena tidak ada data rating asli yang bisa dijadikan dasar; menambahkannya tanpa data asli akan melanggar pedoman schema Google). |

**Tidak ada angka/klaim baru yang diusulkan sebagai pengganti** — sesuai aturan DATA_REQUIRED, bagian ini murni identifikasi status, bukan penyediaan nilai baru.

---

## 8. Internal Linking Audit

**Dari homepage menuju:**

| Tujuan | Jumlah Link | Anchor Text | Catatan |
|---|---|---|---|
| Layanan (`/layanan/*`) | 20 link individual + 1 link ke `/layanan/` ("Lihat Semua Layanan Kami →") | Nama layanan (H3, link) + "Selengkapnya →" (×20, identik) | Anchor "Selengkapnya →" berulang 20× tanpa variasi — secara teknis tidak salah (karena teks di sekitarnya, yaitu H3 & deskripsi, sudah memberi konteks), tapi anchor text yang lebih deskriptif per item akan lebih kuat secara SEO. |
| Area (`/area/*`) | 10 link individual + 1 link ke `/area-layanan/` | Nama area | Baik, deskriptif. |
| Masalah → Layanan terkait | 10 link (masing-masing ke layanan spesifik yang relevan) | "Lihat Solusinya →" (×10, identik) | Sama seperti di atas — anchor generik berulang. |
| Portofolio (`/portofolio/*`) | 7 link individual + 2 link ke `/portofolio/` | Judul proyek + "Lihat Detail →" | 1 dari 7 anchor mengandung teks rusak: "Perawatan Kolam Renang di (area belum dipilih)" — lihat §7/§13. |
| Artikel (`/artikel/*`) | 3 link individual + 3 link ke `/artikel/` | Judul artikel | Hanya 3 dari 10 artikel yang ada ditautkan (lihat §11). |
| FAQ (`/faq/`) | 2 link | "FAQ" (nav & footer) | Section FAQ di homepage sendiri tidak link keluar per pertanyaan (wajar, karena sudah dijawab on-page). |
| Kontak (`/kontak/`) | 2 link | "Kontak" | Baik. |
| WhatsApp (`wa.me/...`) | 5 link | "Konsultasi Sekarang", "Tanya via WhatsApp", "Chat WhatsApp Sekarang", tombol mengambang, dll | Baik — banyak titik akses, wajar untuk CTA utama. |
| Telepon (`tel:`) | 2 link | Nomor telepon | Baik. |

**Duplicate links:** Tidak ditemukan duplikasi bermasalah (link berulang ke tujuan sama seperti `/layanan/` dari nav + footer + tombol "Lihat Semua" adalah pola wajar, bukan duplikasi negatif).

**Missing/kurang optimal:**
- Section "Kata Pelanggan Kami" (kosong) tidak punya link internal apa pun karena memang tidak ada konten untuk ditautkan.
- Tidak ada link eksplisit dari homepage ke artikel-artikel di luar 3 yang ditampilkan (7 artikel lain hanya bisa dijangkau lewat `/artikel/`, tidak individual dari homepage).
- Anchor text generik berulang ("Selengkapnya →", "Lihat Solusinya →") — rekomendasi jangka panjang: variasikan per item (mis. sertakan nama layanan di anchor), tapi ini **bukan** blocker SEO kritis.

---

## 9. Image SEO

Semua `<img>` yang ditemukan di homepage live:

| src | alt | width/height | loading | Catatan |
|---|---|---|---|---|
| `/photo.php?id=2` (hero) | `Jasa Kolam Renang Bogor` | Tidak ada | Tidak ada `loading` attr (benar — gambar above-the-fold sebaiknya tidak lazy) | Alt generik (nama bisnis), tidak mendeskripsikan isi visual gambar. |
| `photo.php?id=13` (portofolio) | `Renovasi Kolam Renang di Puncak Bogor` | Tidak ada | `lazy` | Alt = judul proyek, deskriptif — baik. |
| `photo.php?id=11` (portofolio) | `Kolam Renang Villa Modern di Sentul` | Tidak ada | `lazy` | Baik. |
| `photo.php?id=14` (portofolio) | `Kolam Renang Keluarga` | Tidak ada | `lazy` | Baik. |
| `photo.php?id=16` (portofolio) | `Perawatan Kolam Rutin` | Tidak ada | `lazy` | Baik. |
| `photo.php?id=17` (portofolio) | `Instalasi Sistem Filtrasi` | Tidak ada | `lazy` | Baik. |
| `photo.php?id=15` (portofolio) | `Perbaikan Kebocoran Kolam` | Tidak ada | `lazy` | Baik. |
| `photo.php?id=12` (portofolio) | `Perawatan Kolam Renang di (area belum dipilih)` | Tidak ada | `lazy` | **PROBLEM** — alt text mengandung teks placeholder yang bocor ke publik (lihat §13). |
| `photo.php?id=13`, `id=17`, `id=15` (artikel) | Judul artikel masing-masing | Tidak ada | `lazy` | Baik, deskriptif. Catatan: foto `id=13`, `id=15`, `id=17` dipakai **dua kali** di halaman ini (sekali untuk portofolio, sekali untuk artikel) — bukan technically salah, tapi berarti belum ada foto unik per artikel. |

**Temuan agregat:**

| Isu | Prioritas | Detail |
|---|---|---|
| Tidak ada `width`/`height` di SEMUA `<img>` | **MEDIUM–HIGH** | Berisiko Cumulative Layout Shift (komponen Core Web Vitals) karena browser tidak tahu ukuran gambar sebelum dimuat, terutama untuk gambar yang di-lazy-load saat scroll. |
| Alt text hero generik | **LOW** | "Jasa Kolam Renang Bogor" (nama bisnis) valid tapi tidak deskriptif terhadap isi visual — bukan masalah besar karena bukan gambar konten utama (dekoratif/brand). |
| 1 alt text mengandung placeholder rusak | **HIGH** | Lihat §13 — ini masalah konten (bukan kode), tapi berdampak langsung ke kualitas alt text yang terindeks. |
| Path gambar tidak konsisten (`/photo.php?id=2` vs `photo.php?id=13`, dengan/tanpa leading slash) | **LOW** | Tidak menyebabkan gambar rusak di homepage (karena homepage ada di root `/`), tapi inkonsistensi ini berasal dari cara data disimpan (sebagian dengan slash, sebagian tanpa) — bisa jadi rapuh kalau pola URL situs berubah di masa depan. |
| Tidak ada gambar broken (404) | **INFO (baik)** | Semua src yang ditemukan mengarah ke resource valid. |
| Semua gambar non-hero pakai `loading="lazy"`, hero tidak | **INFO (baik)** | Praktik yang benar — gambar above-the-fold sebaiknya tidak lazy-load supaya tidak memperlambat LCP. |

---

## 10. Schema Audit

Homepage live hanya mengeluarkan **2 blok JSON-LD**:

### 10.1 `LocalBusiness`
```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Jasa Kolam Renang Bogor",
  "description": "...",
  "url": "https://jasakolamrenangbogor.com/",
  "telephone": "+6282216623388",
  "email": "info@jasakolamrenangbogor.com",
  "priceRange": "$$",
  "address": { "@type": "PostalAddress", "streetAddress": "Gedung Dewata, Cimahpar Kav. 10, Cimahpar, Bogor", "addressLocality": "Bogor", "addressRegion": "Jawa Barat", "postalCode": "16110", "addressCountry": "ID" },
  "areaServed": ["Sentul","Puncak","Ciawi","Bogor Kota","Cibinong","Yasmin","Cijeruk","Rancamaya","Bogor Raya","Karadenan"]
}
```
**Evaluasi:**
- Struktur valid (`@context`/`@type` benar, field-field sesuai spesifikasi schema.org `LocalBusiness`).
- Field `image`/`logo` **tidak ada** — Google merekomendasikan properti `image` untuk `LocalBusiness`.
- Field `geo` (koordinat) **tidak ada**, padahal `business` table punya kolom `mapsQuery`/`mapsUrl` — koordinat lat/lng untuk bisnis sendiri belum dimanfaatkan di schema ini (beda dengan tabel `areas` yang punya lat/lng).
- Field `openingHoursSpecification` **tidak ada**, padahal `business.hoursWeekday`/`hoursWeekend` sudah tersedia di database dan ditampilkan di halaman kontak.
- **Tidak ada `aggregateRating`/`review`** — ini justru **benar dan sesuai kaidah** mengingat tidak ada data ulasan asli (lihat §7). Jangan ditambahkan tanpa data asli.
- `priceRange: "$$"` — lihat catatan NOT_VERIFIABLE di §7.

### 10.2 `FAQPage`
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [ /* 5 Q&A dari tabel faq */ ]
}
```
**Evaluasi:** Valid, isinya cocok 1:1 dengan konten yang benar-benar tampil di halaman (persyaratan penting Google — schema harus merepresentasikan konten yang benar-benar terlihat, dan ini terpenuhi).

**Tidak ditemukan** schema `Organization`, `WebSite` (dengan `SearchAction`), `BreadcrumbList` (wajar, homepage adalah root), atau `ItemList`/`Product` untuk section layanan/portofolio.

**Risiko klaim palsu di schema:** Tidak ditemukan — schema yang ada murni mengambil dari field database yang sudah ada, tidak ada nilai yang di-hardcode secara terpisah dari apa yang tampil di halaman.

---

## 11. Conversion Audit

| Elemen CTA | Lokasi | Visibility | Clarity | Relevance | Friction | Mobile |
|---|---|---|---|---|---|---|
| Tombol WhatsApp "Konsultasi Sekarang" | Hero | Tinggi (above fold) | Jelas | Tinggi | Rendah (langsung buka WA dengan pesan pre-filled) | Baik |
| Tombol "Lihat Proyek" (`#proyek` anchor) | Hero | Tinggi | Jelas | Sedang (mengarahkan ke bukti kerja, bukan langsung konversi) | Rendah | Baik |
| Floating WhatsApp button | Fixed di semua scroll position | Sangat tinggi (selalu terlihat) | Jelas (ikon WA umum dikenali) | Tinggi | Sangat rendah | Baik |
| CTA per kartu Layanan/Masalah ("Selengkapnya →" / "Lihat Solusinya →") | 20+10 kartu | Sedang (banyak, bisa jadi "CTA fatigue" karena diulang puluhan kali) | Jelas | Tinggi | Rendah | Baik |
| CTA Band tengah halaman | Sebelum footer | Tinggi | Jelas | Tinggi | Rendah | Baik |
| Form kontak | — | **Tidak ada form** — hanya link WA/telepon/email | — | — | — | — |

**Temuan:**

| Isu | Prioritas | Detail |
|---|---|---|
| Tidak ada form kontak alternatif | **LOW** | Situs sepenuhnya mengandalkan WhatsApp/telepon untuk konversi. Ini bukan cacat — untuk bisnis lokal berbasis WhatsApp, ini justru pola yang wajar dan sering kali friction-nya lebih rendah dibanding form. Dicatat sebagai INFO, bukan rekomendasi wajib menambah form. |
| Testimonial kosong melemahkan CTA di section bawahnya | **HIGH** | Urutan halaman: ... → Testimonial (kosong) → CTA Band → Kontak. Visitor yang sampai ke CTA band tanpa melihat bukti sosial yang meyakinkan berpotensi konversi lebih rendah dibanding jika testimonial terisi. |
| CTA repetitif dengan teks identik di banyak kartu | **LOW** | Tidak menghambat fungsi, tapi variasi teks CTA per konteks (mis. "Konsultasikan Kebocoran Ini →") berpotensi meningkatkan relevansi psikologis dibanding teks generik berulang. |
| Semua CTA WhatsApp membawa pesan pre-filled yang SAMA (`business.whatsappMessage`, generik) | **LOW–MEDIUM** | Klik dari kartu "Kolam Bocor" dan klik dari Hero mengarah ke pesan WA yang sama persis, tidak context-aware — peluang untuk pesan pre-filled yang disesuaikan konteks (mis. otomatis menyebut "kolam bocor") saat ini belum dimanfaatkan. |

---

## 12. Content Gap

**Konteks:** Ini BUKAN rekomendasi membuat halaman baru — murni evaluasi apakah homepage sendiri sudah cukup memuat topik-topik penting.

| Topik | Sudah Ada di Homepage? | Catatan |
|---|---|---|
| Proses kerja | **Ya** | "Proses Kerja Singkat" (4 langkah) di section Tentang. |
| Jenis layanan | **Ya, sangat lengkap** | 20 layanan ditampilkan (lihat §4 soal panjangnya). |
| Masalah kolam renang | **Ya** | 10 masalah umum + solusi. |
| Area layanan | **Ya** | 10 area + peta. |
| FAQ | **Ya** | 5 pertanyaan + schema. |
| Portofolio/bukti kerja | **Ya**, tapi 1 item bermasalah (§13) | 7 proyek. |
| Faktor biaya perawatan | **Tidak ada di homepage** | Sudah dibahas mendalam di artikel terpisah ("Berapa Biaya Perawatan Kolam Renang di Bogor?") yang sudah live — tapi artikel ini **tidak termasuk** dalam 3 artikel yang ditampilkan di homepage (lihat §4/§14). Homepage sendiri tidak menyinggung faktor biaya sama sekali. |
| Testimoni/ulasan | **Section ada, tapi kosong** | Lihat §7. |
| Sertifikasi/legalitas usaha | **Tidak disebutkan** | Tidak ada DATA_REQUIRED baru diusulkan di sini — sekadar dicatat sebagai topik yang bisa memperkuat trust JIKA datanya tersedia dan terverifikasi. |

---

## 13. Issues

Diurutkan berdasarkan prioritas.

### CRITICAL
- *Tidak ada temuan level CRITICAL* (tidak ada broken page, tidak ada kegagalan render, tidak ada kebocoran data sensitif).

### HIGH
1. **Section Testimonial kosong** — section trust utama di homepage tidak menampilkan bukti sosial sama sekali (§4, §7, §11).
2. **1 item portofolio dengan judul/alt text rusak** — "Perawatan Kolam Renang di (area belum dipilih)" tampil ke publik di kartu homepage DAN alt text gambarnya (§9, §4). Ini murni masalah data/konten (entri portofolio yang belum diisi lengkap oleh admin), bukan bug kode.
3. **Klaim "10+ Tahun Pengalaman" vs "Puluhan tahun pengalaman"** saling bertentangan di halaman yang sama (DATA_CONFLICT, §7).
4. **Tidak ada `width`/`height` di semua gambar** — risiko Cumulative Layout Shift (§9).

### MEDIUM
5. Title (70 char) & meta description (178 char) berpotensi terpotong di SERP (§3).
6. `og:image` berformat SVG — kemungkinan besar tidak tampil sebagai preview di platform share sosial (§3).
7. Twitter Card tidak lengkap (`twitter:title`/`description`/`image` hilang) (§3).
8. Section "Layanan Kami" menampilkan 20 kartu tanpa batas, membuat homepage sangat panjang (§4, §6).
9. Trust bar nyaris kosong (3 dari 4 slot data kosong) (§4, §7).
10. Section H2 "Tentang" dan "Jenis Pelanggan" melompat langsung ke H4 (skip H3), tidak konsisten dengan section lain (§6).
11. Homepage menampilkan 3 artikel **terlama** (by `id`), bukan yang terbaru/paling relevan komersial — melewatkan artikel "Biaya Perawatan" (§4, §12).
12. Schema `LocalBusiness` belum memanfaatkan `geo`, `openingHoursSpecification`, `image` yang datanya sebenarnya sudah tersedia di database (§10).

### LOW
13. Anchor text "Selengkapnya →" / "Lihat Solusinya →" berulang identik puluhan kali (§8, §11).
14. CSS Leaflet dimuat penuh di `<head>` untuk semua pengunjung meski peta hanya di satu section (§3).
15. Alt text hero generik (nama bisnis, bukan deskripsi visual) (§9).
16. Path gambar tidak konsisten (dengan/tanpa leading slash) (§9).
17. Footer homepage adalah duplikasi manual dari `render_footer()`, berisiko drift konten (daftar area yang ditampilkan berbeda logika: homepage pakai 4 area pertama dari database, sedangkan `render_footer()` di halaman lain hardcode 4 nama area tetap) — lihat §2.
18. `robots.txt` mengarahkan `Sitemap:` ke domain **www** (`https://www.jasakolamrenangbogor.com/sitemap.xml`), sementara domain kanonik situs adalah **non-www** (ada redirect 301 www→non-www di `.htaccess`) — crawler perlu mengikuti 1 hop redirect tambahan untuk mengakses sitemap. *(Dicatat sebagai info teknis; sesuai instruksi, `robots.txt` TIDAK diubah pada fase ini.)*

### INFO
19. Satu H1, canonical benar, robots meta benar, schema FAQPage valid & sesuai isi halaman — semua ini sudah sesuai praktik baik dan tidak perlu diubah.
20. Tidak ditemukan gambar broken/404 di homepage.

---

## 14. Recommended Changes

**Catatan:** "Fix" di bawah ini SEBAGIAN BESAR adalah perubahan **konten/data** (lewat panel admin, bukan kode) dan sebagian kecil perubahan **kode/markup**. Pemisahannya ditandai di tiap poin. Tidak ada yang dieksekusi pada fase ini.

### MUST FIX
- [KONTEN] Isi minimal 1-3 testimoni asli & published di tabel `testimonials`, atau — jika belum ada testimoni asli — pertimbangkan menyembunyikan/mengganti section ini sementara dengan trust signal lain yang datanya sudah tersedia (garansi, proses kerja) sampai testimoni asli tersedia.
- [KONTEN] Perbaiki judul & deskripsi item portofolio "Perawatan Kolam Renang di (area belum dipilih)" lewat panel admin Portofolio.
- [KONTEN] Selaraskan klaim "10+ Tahun Pengalaman" (hero) dengan "Puluhan tahun pengalaman" (section Tentang) — pastikan keduanya konsisten dan mencerminkan angka yang benar-benar terverifikasi.
- [KONTEN] Perbaiki spasi ganda di H1 (`"Perawatan  Rutin"`) lewat tab "Halaman Utama" di admin.
- [KODE — Phase 2] Tambahkan atribut `width`/`height` eksplisit ke semua tag `<img>` di homepage untuk mencegah CLS.

### SHOULD FIX
- [KONTEN] Lengkapi field `yearFounded`, `activeCustomers`, `employeeCount` di data bisnis supaya trust bar tidak nyaris kosong (jika angkanya memang tersedia & akurat).
- [KODE — Phase 2] Persingkat `<title>` ke ~55-60 karakter dan meta description ke ~150-155 karakter.
- [KODE — Phase 2] Ganti `og:image` ke format raster (PNG/JPG) berukuran 1200×630px, atau tambahkan versi raster sebagai alternatif SVG.
- [KODE — Phase 2] Lengkapi Twitter Card dengan `twitter:title`, `twitter:description`, `twitter:image`.
- [KODE — Phase 2] Pertimbangkan membatasi section "Layanan Kami" ke subset kurasi (mis. 6-8 layanan) + tombol "Lihat Semua", konsisten dengan pola section Artikel.
- [KODE — Phase 2] Ubah query artikel homepage dari `ORDER BY sort_order, id LIMIT 3` menjadi urutan yang menampilkan artikel terbaru (mis. `id DESC`) atau kurasi manual lewat `sort_order`.
- [KODE — Phase 2] Standarisasi heading: ganti H4 di section "Tentang" dan "Jenis Pelanggan" jadi H3, konsisten dengan section kartu lain.

### NICE TO HAVE
- [KODE — Phase 2] Tambahkan properti `geo`, `openingHoursSpecification`, `image` ke schema `LocalBusiness` (datanya sudah tersedia di database).
- [KODE — Phase 2] Variasikan anchor text CTA per kartu (mis. sertakan nama layanan/masalah di teks link) alih-alih teks generik berulang.
- [KODE — Phase 2] Muat CSS Leaflet secara lazy/deferred, bukan di `<head>` untuk semua pengunjung.
- [KODE — Phase 2] Satukan implementasi footer homepage dengan `render_footer()` supaya tidak ada dua sumber kebenaran untuk markup yang sama.
- [KONTEN] Sesuaikan pesan WhatsApp pre-filled per konteks CTA (mis. dari kartu "Kolam Bocor" otomatis menyebut kebocoran).

---

## 15. Proposed Homepage Structure

Struktur section **saat ini sudah cukup baik urutannya** secara search-intent (Hero → Trust → Tentang → Layanan → Area → Masalah → Proyek → Segmentasi Pelanggan → Artikel → FAQ → Testimonial → CTA → Kontak). Rekomendasi di sini murni penyesuaian **isi tiap section**, bukan urutan baru:

1. Hero (tetap)
2. Trust bar — **lengkapi datanya** dulu sebelum dianggap section yang berfungsi penuh
3. Tentang (tetap, standarisasi heading H3)
4. Layanan — **pertimbangkan kurasi 6-8 item** + link "Lihat Semua"
5. Area (tetap)
6. Masalah (tetap)
7. Proyek/Portofolio (tetap, setelah data placeholder diperbaiki)
8. Jenis Pelanggan (tetap, standarisasi heading H3)
9. Panduan/Artikel — **ubah kriteria pemilihan 3 artikel** yang tampil (terbaru/paling relevan, bukan `id` terkecil)
10. FAQ (tetap)
11. Testimonial — **prioritas tertinggi untuk diisi**, letaknya sudah tepat (persis sebelum CTA band, posisi ideal untuk reinforcement sebelum ajakan bertindak)
12. CTA Band (tetap)
13. Kontak (tetap)
14. Footer — satukan dengan `render_footer()`

---

## 16. Proposed Copy Changes

Sesuai aturan DATA_REQUIRED — tidak ada angka atau klaim bisnis baru yang ditulis di sini. Hanya perbaikan yang murni tata bahasa/konsistensi dari apa yang SUDAH ada.

### Copy #1 — H1 (perbaikan spasi ganda)
**CURRENT:**
```
Jasa Kolam Renang Bogor untuk Pembangunan, Renovasi & Perawatan  Rutin
```
**PROPOSED:**
```
Jasa Kolam Renang Bogor untuk Pembangunan, Renovasi & Perawatan Rutin
```
**REASON:** Spasi ganda antara "Perawatan" dan "Rutin" adalah artefak/typo, bukan gaya bahasa yang disengaja.

### Copy #2 — Konsistensi klaim pengalaman
**CURRENT:**
- Hero: "10+ Tahun Pengalaman"
- Section Tentang: "Puluhan tahun pengalaman menangani kolam renang berbagai skala di wilayah Bogor."

**PROPOSED:** DATA_REQUIRED — dua klaim ini perlu diselaraskan ke satu angka yang sama dan terverifikasi. Tidak diusulkan angka pengganti karena tidak dapat diverifikasi dari source code mana yang benar.

**REASON:** Dua klaim usia pengalaman yang berbeda di halaman yang sama menurunkan kredibilitas dan berisiko dianggap tidak akurat oleh pengunjung yang teliti.

### Copy #3 — `<title>` & meta description (opsional, Phase 2)
**CURRENT:**
```
Title: Jasa Kolam Renang Bogor | Pembuatan, Perawatan & Renovasi Kolam Renang (70 char)
Desc: Jasa Kolam Renang Bogor melayani pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi sistem air untuk rumah tinggal, villa, dan resort di wilayah Bogor dan sekitarnya. (178 char)
```
**PROPOSED:** DATA_REQUIRED untuk versi final — pemendekan title/description perlu persetujuan pemilik bisnis karena menyangkut pesan pemasaran utama situs, bukan sekadar penyesuaian teknis. Draf akan disiapkan di Phase 2 setelah disetujui.

**REASON:** Panjang saat ini melebihi batas tampil optimal SERP Google.

---

## 17. Files That Would Be Modified (Phase 2, BELUM dilakukan)

Jika rekomendasi kode (bukan konten) di atas disetujui, file yang kemungkinan tersentuh di Phase 2:

| File | Kemungkinan Perubahan |
|---|---|
| `index.php` | Title/meta description (jika disetujui), batasi jumlah kartu Layanan yang tampil, ubah kriteria query artikel (`ORDER BY`), tambah `width`/`height` ke `<img>`, standarisasi H4→H3, satukan footer dengan `render_footer()`, lengkapi schema `LocalBusiness` (`geo`/`openingHoursSpecification`/`image`), lazy-load CSS Leaflet. |
| `inc/render-partials.php` | Kemungkinan penyesuaian kecil jika `render_footer()` perlu diadaptasi supaya bisa dipakai ulang oleh `index.php` tanpa menghilangkan kebutuhan spesifik homepage. |
| `assets/img/og-image.svg` (atau file baru) | Jika og:image diganti ke format raster, perlu file gambar baru (di luar kemampuan saya membuat aset visual — perlu disediakan atau di-generate terpisah). |

**Perubahan KONTEN (bukan file kode) yang perlu dilakukan terpisah lewat panel admin:**
- Tabel `testimonials` (isi testimoni asli)
- Tabel `portfolio` (perbaiki 1 item placeholder)
- Tabel `business` (selaraskan klaim pengalaman, lengkapi `yearFounded`/`activeCustomers`/`employeeCount` jika tersedia)
- `page_sections` (page_key=`home`, field `hero_h1` — perbaiki spasi ganda)

---

# FINAL RULE — Status Fase Ini

✅ Audit selesai.
✅ Rekomendasi diberikan.
✅ Proposed copy diberikan (terbatas pada perbaikan konsistensi, tanpa klaim baru).
✅ Proposed structure diberikan.
❌ **Tidak ada perubahan website yang dilakukan** — kode, database, URL, route, sitemap, robots.txt, dan halaman lain semuanya tidak tersentuh.

**Menunggu persetujuan sebelum melanjutkan ke Phase 2.**
