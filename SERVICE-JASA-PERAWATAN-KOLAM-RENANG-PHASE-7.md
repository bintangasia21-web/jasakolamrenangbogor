# PHASE 7 — MIGRASI MONEY PAGE: JASA PERAWATAN KOLAM RENANG
**Tanggal:** 2026-08-11
**Status:** Kode sudah siap & (per instruksi eksplisit Phase 7) akan di-commit dan di-push. **Eksekusi migrasi database itu sendiri BELUM terjadi** — menunggu admin menjalankan `migrate-money-page-perawatan.php` satu kali lewat browser setelah deploy selesai (pola sama seperti semua skrip `fix-*`/`seed-*` sebelumnya di proyek ini; saya tidak punya akses database langsung).

---

## Before

| Item | Nilai |
|---|---|
| URL | `/layanan/perawatan-pembersihan-rutin/` |
| HTTP status | 200 |
| Title | `Perawatan & Pembersihan Rutin \| Jasa Kolam Renang Bogor` |
| Meta description | `Program perawatan berkala — pembersihan, pengecekan kualitas air, dan perawatan sistem filtrasi — agar kolam renang Anda selalu jernih dan siap pakai kapan saja.` |
| H1 | `Perawatan & Pembersihan Rutin` (tidak menyebut "Kolam Renang") |
| Canonical | `https://jasakolamrenangbogor.com/layanan/perawatan-pembersihan-rutin/` (self-referencing) |
| Robots | `index, follow` |
| Sitemap | Baris ini otomatis ada di `sitemap.xml` (dibaca live dari `pages.url_path`) |

**Catatan penting:** Fase sebelumnya (percakapan sebelum Phase 7) sudah menyiapkan `fix-service-perawatan-rutin.php` untuk memperkaya konten halaman INI TANPA mengubah URL-nya. Skrip itu **kemungkinan belum sempat dijalankan admin** sebelum Phase 7 ini diminta. Karena migrasi URL butuh proses yang berbeda (bukan cuma UPDATE konten, tapi UPDATE `url_path` + redirect + perbaikan link lintas-halaman), skrip Phase 7 ini **melakukan segalanya dari awal secara mandiri** (tidak bergantung pada state hasil skrip lama) — jadi aman dijalankan berapa pun kondisi halaman saat ini. Skrip lama (`fix-service-perawatan-rutin.php`) **sudah dihapus dari repo** karena sekarang jadi tidak relevan (lihat §Files).

---

## After

Diverifikasi statis (lint + parsing) — **efek database belum live**, akan berlaku setelah skrip migrasi dijalankan admin.

| Item | Nilai |
|---|---|
| URL baru | `/layanan/jasa-perawatan-kolam-renang/` |
| Title (kolom `title`) | `Jasa Perawatan Kolam Renang` |
| Meta title (`<title>` tag) | `Jasa Perawatan Kolam Renang Bogor \| Jasa Kolam Renang Bogor` — **59 karakter** |
| Meta description | `Jasa perawatan kolam renang rutin di Bogor: pembersihan, cek kualitas air, dan perawatan filter berkala. Untuk rumah, villa, dan hotel. Konsultasi gratis.` — **154 karakter** |
| H1 | `Jasa Perawatan Kolam Renang` — sekarang eksplisit menyebut entitas & intent utama |
| H2 | 7× di konten: "Jasa Perawatan Kolam Renang di Bogor", "Apa Saja yang Dilakukan dalam Perawatan Kolam Renang", "Masalah yang Dapat Dicegah dengan Perawatan Rutin", "Perawatan Kolam untuk Rumah, Villa, Hotel, dan Properti Lain", "Portofolio Perawatan Kolam Renang", "Area Layanan", "Konsultasi Perawatan Kolam Renang" |
| H3 | 2× — judul 2 kartu portofolio |
| Word count | 364 kata |
| Canonical (setelah migrasi) | `https://jasakolamrenangbogor.com/layanan/jasa-perawatan-kolam-renang/` — self-referencing otomatis (dihasilkan dari `url_path` baris yang sama via `render_head()`) |
| Robots | `index, follow` (tidak diubah) |
| Sitemap | Otomatis ikut berubah — query `sitemap-generator.php` membaca `url_path` LIVE dari database, tidak ada cache/file statis yang perlu disinkronkan manual |

---

## Redirect

| Dari | Ke | Tipe |
|---|---|---|
| `/layanan/perawatan-pembersihan-rutin/` | `/layanan/jasa-perawatan-kolam-renang/` | **301** (permanent), ditambahkan ke `.htaccess` |

Rule ditambahkan ke blok `<IfModule mod_alias.c>` yang sudah ada (tempat yang sama dipakai untuk 5 redirect lama lain di proyek ini — `Redirect 301` dari mod_alias diproses SEBELUM `RewriteRule` catch-all mod_rewrite di blok terpisah, jadi tidak akan "ketabrak" oleh routing dinamis `page-router.php`).

**Verifikasi rantai redirect (setelah deploy, direncanakan):**
```
OLD URL (/layanan/perawatan-pembersihan-rutin/)
   ↓ 301
NEW URL (/layanan/jasa-perawatan-kolam-renang/)
   ↓
HTTP 200
```
Tidak ada redirect chain (cuma 1 hop), tidak ada redirect loop (URL tujuan tidak balik ke URL asal), tidak ada 302, tidak ada canonical yang mengarah ke URL lama (canonical baru self-referencing ke URL baru, dihasilkan otomatis dari `url_path` baris database yang sudah berubah).

---

## Internal Links

**Crawl penuh dilakukan ke SEMUA halaman live** (20 layanan, 10 area, 10 artikel, 7 portofolio, homepage, 3 halaman hub) untuk mencari referensi hardcode ke URL lama. Ditemukan **3 lokasi** (di luar halaman itu sendiri):

| Lokasi | Jenis | Perlu Diperbaiki Manual? |
|---|---|---|
| Homepage — kartu layanan "Perawatan & Pembersihan Rutin" (2 link: judul + "Lihat Layanan Ini →") | **Dinamis** — dibaca langsung dari `$svc['url_path']` hasil query database | ❌ Tidak — otomatis ikut berubah begitu STEP 1 migrasi selesai |
| `/area/bogor-kota/` — kartu layanan "Perawatan & Pembersihan Rutin" (ditautkan sejak Phase 5) | **Hardcode di kolom `content` baris ini** | ✅ Ya — diperbaiki via `str_replace()` di STEP 2 skrip |
| `/layanan/` (hub) — kartu "Perawatan & Pembersihan Rutin" dalam daftar 20 layanan | **Hardcode di kolom `content` baris ini** | ✅ Ya — diperbaiki via `str_replace()` di STEP 3 skrip |
| `index.php` — `$fallbackServices` (array fallback, HANYA dipakai kalau query layanan dari DB kosong — saat ini tidak aktif karena 20 layanan sudah ada) | **Hardcode di source code** | ✅ Ya — sudah diperbaiki langsung di file (lihat §Files) |

**Total link yang diperbarui:** 2 baris database (via `str_replace`, bukan menulis ulang HTML secara manual — menghindari risiko salah ketik pada hub `/layanan/` yang berisi 20 kartu) + 1 baris kode sumber (`index.php`).

**Tidak ditemukan** referensi hardcode lain di 20 halaman layanan, 10 halaman area, 10 artikel, 7 portofolio, atau 3 halaman hub lain (`/area-layanan/`, `/portofolio/`, `/artikel/`) — dikonfirmasi lewat crawl `curl` + `grep` langsung ke tiap halaman, bukan asumsi.

---

## Sitemap

**Status: otomatis, tidak perlu tindakan manual.** `sitemap-generator.php` (dipetakan dari `/sitemap.xml` lewat `.htaccess`) menjalankan `SELECT url_path, updated_at FROM pages WHERE status='published'` secara LIVE setiap kali sitemap diakses — tidak ada file statis atau cache yang bisa "ketinggalan". Begitu STEP 1 migrasi selesai, `sitemap.xml` otomatis:
- **Menghapus** entri `/layanan/perawatan-pembersihan-rutin/` (karena baris itu sekarang punya `url_path` baru)
- **Memuat** entri `/layanan/jasa-perawatan-kolam-renang/` (baris yang sama, `url_path` baru)

Tidak ada risiko dua URL canonical dalam sitemap karena ini SATU baris database yang di-UPDATE (bukan INSERT baris baru) — dijamin oleh guard `WHERE id = :id AND url_path = :old_url AND type = 'service'` di skrip.

---

## Canonical

| URL | Canonical Sebelum | Canonical Sesudah |
|---|---|---|
| `/layanan/perawatan-pembersihan-rutin/` | Self-referencing ke dirinya sendiri | Setelah migrasi: URL ini 301 ke URL baru, tidak lagi men-generate halaman/canonical sendiri |
| `/layanan/jasa-perawatan-kolam-renang/` | (belum ada, 404) | Self-referencing ke dirinya sendiri — dihasilkan otomatis oleh `render_head()`/`page-router.php` dari `url_path` baris yang sama, tidak perlu kode tambahan |

---

## SEO Metadata

| Elemen | Before | After |
|---|---|---|
| Title (`<title>`) | `Perawatan & Pembersihan Rutin \| Jasa Kolam Renang Bogor` | `Jasa Perawatan Kolam Renang Bogor \| Jasa Kolam Renang Bogor` — **59 karakter** |
| Meta description | 165 karakter, tidak menyebut Bogor | `Jasa perawatan kolam renang rutin di Bogor: pembersihan, cek kualitas air, dan perawatan filter berkala. Untuk rumah, villa, dan hotel. Konsultasi gratis.` — **154 karakter** |
| H1 | `Perawatan & Pembersihan Rutin` | `Jasa Perawatan Kolam Renang` |

---

## Portfolio

2 item portofolio (dari total 7 di database) yang genuinely bertema perawatan rutin dipakai — sama seperti fase sebelumnya, tidak ada portofolio baru dibuat:

| Judul | Area | Foto | URL |
|---|---|---|---|
| Perawatan Kolam Rutin | Bogor Kota | `photo.php?id=16` (738×414) | `/portofolio/perawatan-kolam-rutin/` |
| Perawatan Kolam Renang di Cijeruk, Bogor | Cijeruk | `photo.php?id=12` (426×292) | `/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/` |

Pemilik menyebut "lebih dari 20 pekerjaan nyata" — database saat ini hanya berisi 7 portofolio total (2 bertema perawatan). Selisih ini tetap `DATA_REQUIRED` (lihat bagian di bawah), tidak dikarang.

---

## Database

| Tabel | Baris | Kolom yang Berubah |
|---|---|---|
| `pages` | `id` = dibaca dinamis saat skrip jalan (guard: `url_path = '/layanan/perawatan-pembersihan-rutin/' AND type='service'`) | `url_path`, `title`, `h1`, `meta_title`, `meta_description`, `content`, `faq_json` |
| `pages` | Baris `/area/bogor-kota/` (`type='area'`) | `content` (hanya `str_replace` URL lama→baru, sisanya utuh) |
| `pages` | Baris `/layanan/` (hub) | `content` (hanya `str_replace` URL lama→baru, sisanya utuh) |

**Guard keamanan yang diterapkan** (sesuai instruksi §23):
- ID dibaca dulu dari DB SEBELUM UPDATE (bukan diasumsikan).
- Nilai lama (title/h1/meta_title/meta_description) dibaca & dikembalikan dalam response JSON sebagai backup/audit trail.
- UPDATE dikunci ke `id` + `url_path` lama + `type` sekaligus.
- **Guard eksplisit sebelum migrasi**: skrip mengecek dulu apakah `url_path` TARGET sudah dipakai baris LAIN (`id` berbeda) — kalau ya, skrip **berhenti tanpa mengubah apa pun** dan melaporkan STOP (sudah dikonfirmasi manual lewat `curl` bahwa URL target masih 404/belum dipakai, tapi skrip tetap melakukan pengecekan ulang otomatis saat dijalankan sebagai lapis keamanan kedua).
- **Verifikasi pasca-migrasi**: skrip menghitung ulang jumlah baris yang cocok dengan `url_path` lama ATAU baru — harus persis 1 (bukan 0 atau 2+) — dan melaporkan hasilnya di response.
- STEP 2 & 3 (perbaikan link) memakai `str_replace()` terhadap nilai yang SEDANG tersimpan di database saat itu, bukan menimpa dengan HTML yang ditulis ulang manual — meniadakan risiko salah ketik ulang konten yang panjang (khususnya hub `/layanan/` yang berisi 20 kartu layanan).
- Kredensial tidak pernah ditampilkan di skrip maupun laporan ini.

---

## Files

| File | Perubahan | Alasan |
|---|---|---|
| `migrate-money-page-perawatan.php` (baru) | Skrip migrasi lengkap (STEP 1-3 di atas) | Implementasi Phase 7 |
| `.htaccess` | +1 aturan `Redirect 301`, +1 nama file di daftar Basic Auth, −1 nama file (skrip lama yang dihapus) | Redirect URL lama, proteksi skrip baru, bersih-bersih referensi skrip yang sudah tidak ada |
| `index.php` | 1 baris — `$fallbackServices` diperbarui ke URL baru | Konsistensi source code (meski fallback ini saat ini tidak aktif dipakai) |
| `fix-service-perawatan-rutin.php` | **Dihapus** | Sudah sepenuhnya digantikan oleh `migrate-money-page-perawatan.php` (yang melakukan semua yang skrip lama lakukan, DITAMBAH migrasi URL) — mempertahankan keduanya cuma akan membingungkan, dan skrip lama akan jadi no-op begitu URL berubah (mencari baris dengan `url_path` yang sudah tidak ada) |

**Catatan file cleanup lanjutan:** Sesuai instruksi §24 ("setelah berhasil, hapus/nonaktifkan script"), `migrate-money-page-perawatan.php` sendiri **sebaiknya dihapus lewat commit terpisah SETELAH admin mengonfirmasi migrasi berhasil dijalankan** — tidak bisa dihapus SEKARANG karena skrip ini justru baru akan dieksekusi admin setelah deploy ini.

---

## DATA_REQUIRED

| Item | Status |
|---|---|
| Portofolio perawatan tambahan (pemilik menyebut 20+, database baru punya 2 bertema perawatan dari 7 total) | `DATA_REQUIRED` — perlu diinput lewat panel admin (foto + deskripsi tiap proyek) sebelum bisa ditampilkan; tidak dikarang di sini |
| Testimoni khusus perawatan | `DATA_REQUIRED` — tabel `testimonials` tidak punya baris `published` |
| Harga/durasi/garansi/jumlah teknisi/sertifikasi | `DATA_REQUIRED` — tidak ada data terverifikasi, tidak ditambahkan ke konten atau FAQ |

---

## SHARED_TEMPLATE — OUT OF SCOPE

| Nama File | Masalah | Kenapa Tetap Di Luar Scope |
|---|---|---|
| `inc/templates/service.php` | H2 CTA band duplikat H1 (sekarang H1="Jasa Perawatan Kolam Renang" tapi CTA band masih pakai `title` lama field, berpotensi tidak 100% sinkron) | Level shared template, 20 halaman layanan. Sudah tercatat di fase-fase sebelumnya, tidak diubah. |
| `inc/templates/service.php` | Schema `Service.areaServed` string generik `'Bogor'` | Sama — level template. Tidak diubah. |
| Judul kartu "Perawatan & Pembersihan Rutin" di halaman `/area/sentul/` dan `/area/cibinong/` | Kedua halaman ini JUGA menyebut nama layanan ini di kartu 4-layanan mereka (pola sama seperti Bogor Kota sebelum Phase 5), tapi **TIDAK berupa link** (`<h3>` polos, bukan `<a>`) — jadi tidak ada href yang perlu diperbaiki di sana. Konten teksnya sendiri (nama layanan disebut) tidak diubah karena Sentul/Cibinong eksplisit di luar scope Phase 7. | Bukan masalah URL (tidak ada link rusak), murni catatan konsistensi penamaan untuk fase optimasi area lain di masa depan. |

---

## Live Verification

⏳ **Belum bisa dilakukan** — migrasi database belum dieksekusi (menunggu admin menjalankan skrip). Setelah admin menjalankan `migrate-money-page-perawatan.php`, verifikasi yang direkomendasikan:

```bash
curl -I https://jasakolamrenangbogor.com/layanan/perawatan-pembersihan-rutin/
# harapan: HTTP/2 301, Location: .../layanan/jasa-perawatan-kolam-renang/

curl -I https://jasakolamrenangbogor.com/layanan/jasa-perawatan-kolam-renang/
# harapan: HTTP/2 200
```

Ditambah pengecekan: title/meta/H1/H2/canonical/robots/schema di halaman baru, serta regression check ke homepage, `/area/bogor-kota/`, `/layanan/`, dan 1-2 halaman layanan lain untuk memastikan tidak ada perubahan tak terduga.

---

## Regression (rencana verifikasi pasca-eksekusi)

| Halaman | Ekspektasi |
|---|---|
| `/` (homepage) | Tidak berubah secara kode; kartu "Perawatan Rutin" otomatis menaut ke URL baru (dinamis) |
| `/layanan/pembuatan-kolam-renang-baru/` (Phase 3) | Tidak berubah |
| `/layanan/perbaikan-kebocoran-kolam/` (Phase 6) | Tidak berubah |
| `/area/bogor-kota/` (Phase 5) | Konten sama persis KECUALI 1 href yang diperbarui (str_replace presisi) |
| `/layanan/` (hub) | Konten sama persis KECUALI 1 href yang diperbarui |
| 10 halaman area lain, artikel, portofolio | Tidak disentuh sama sekali (dikonfirmasi lewat crawl — tidak ada referensi URL lama di sana) |

---

# FINAL STATUS

✅ Kondisi awal & target diverifikasi lewat fetch live sebelum perubahan (URL lama 200, URL target 404/belum dipakai).
✅ Crawl penuh 50+ halaman dilakukan untuk memetakan SEMUA referensi hardcode ke URL lama (ditemukan 3, semua ditangani).
✅ Migrasi dirancang sebagai 1 UPDATE baris (bukan INSERT+DELETE) dengan guard ganda (cek sebelum & verifikasi sesudah) mencegah duplikasi/tabrakan URL.
✅ 301 redirect ditambahkan, tanpa chain/loop.
✅ Sitemap & canonical akan otomatis benar (arsitektur dinamis, tidak perlu sentuhan manual).
✅ Skrip lama yang sekarang terduplikasi fungsinya sudah dihapus.
✅ Tidak ada rahasia/kredensial/file debug dalam commit.

**Kode sudah siap untuk commit & push (sesuai instruksi Phase 7). Eksekusi migrasi database itu sendiri menunggu admin menjalankan skrip pasca-deploy.**
