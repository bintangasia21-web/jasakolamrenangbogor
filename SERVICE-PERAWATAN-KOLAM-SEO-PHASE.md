# SERVICE SEO OPTIMIZATION — JASA PERAWATAN KOLAM RENANG
**Tanggal:** 2026-08-11
**Status deploy:** ⏳ **BELUM di-commit/push** — sesuai instruksi eksplisit "JANGAN commit. JANGAN push. Berhenti dan tunggu review." Semua perubahan ada di working tree lokal.

---

## Target

```
TARGET PAGE
URL: /layanan/perawatan-pembersihan-rutin/
PAGE ID: dibaca otomatis oleh skrip saat dijalankan (dikunci via WHERE url_path + type, bukan diasumsikan)
TITLE: Perawatan & Pembersihan Rutin | Jasa Kolam Renang Bogor
HTTP STATUS: 200 (dikonfirmasi live)
TEMPLATE: inc/templates/service.php (shared, dipakai 20 halaman layanan — TIDAK disentuh)
```

**Alasan pemilihan:** Ini satu-satunya halaman layanan yang secara jelas = "Jasa Perawatan Kolam Renang" dasar. Dibedakan dari "Kontrak Perawatan Bulanan" (`/layanan/kontrak-perawatan-bulanan/`), yang merupakan varian PAKET KONTRAK dari layanan ini, bukan layanan perawatan itu sendiri — dikonfirmasi lewat FAQ #2 halaman ini sendiri yang menyebut "silakan lihat halaman Kontrak Perawatan Bulanan kami" sebagai opsi terpisah.

---

## Before

Dari fetch live sebelum perubahan:

| Item | Nilai |
|---|---|
| Title | `Perawatan & Pembersihan Rutin \| Jasa Kolam Renang Bogor` |
| Meta description | `Program perawatan berkala — pembersihan, pengecekan kualitas air, dan perawatan sistem filtrasi — agar kolam renang Anda selalu jernih dan siap pakai kapan saja.` |
| H1 | `Perawatan & Pembersihan Rutin` — **tidak menyebut "Kolam Renang" secara eksplisit**, padahal itu entitas utama halaman |
| H2 | 2× — "Pertanyaan Seputar Halaman Ini" (FAQ, shared), "Perawatan & Pembersihan Rutin" (CTA band, shared — **duplikat literal H1**, pola isu yang sama seperti 2 halaman layanan sebelumnya) |
| H3 | 0 |
| H4 | 1× — "Tahapan Pengerjaan" (skip H2 & H3) + 2× footer (shared) |
| Word count | ±91 kata |
| Images | **0** |
| Internal links (di konten) | **0** — bahkan "Kontrak Perawatan Bulanan" disebutkan namanya di FAQ tapi tidak ditautkan |
| FAQ | 2 pertanyaan (frekuensi perawatan, paket kontrak bulanan) |
| Schema `Service` | `name: "Perawatan & Pembersihan Rutin"`, `areaServed: "Bogor"` (string generik, isu shared-template yang sama seperti halaman layanan lain) |
| Portofolio ditampilkan | **0** |
| Area disebutkan | **0** di konten (hanya "Bogor" umum di intro) |
| Fakta bisnis terverifikasi disebut | Tidak |

---

## After

Diverifikasi statis (lint + parsing heading/link/word-count) — **belum live**, menunggu skrip dijalankan setelah deploy disetujui.

| Item | Nilai |
|---|---|
| Title | Tidak diubah (`title` kolom tetap "Perawatan & Pembersihan Rutin") |
| Meta title (`<title>` tag via `meta_title`) | `Jasa Perawatan Kolam Renang di Bogor \| Jasa Kolam Renang Bogor` — **62 karakter** |
| Meta description | `Jasa perawatan kolam renang rutin di Bogor: pembersihan, cek kualitas air, dan perawatan filter berkala. Untuk rumah, villa, dan hotel. Konsultasi gratis.` — **154 karakter** |
| H1 | **Diperkuat** → `Jasa Perawatan Kolam Renang` — sekarang eksplisit menyebut entitas utama, langsung menargetkan intent "jasa perawatan kolam renang" |
| H2 | **7 baru** di konten: "Perawatan Kolam Renang untuk Menjaga Kondisi Tetap Optimal", "Apa Saja yang Dilakukan dalam Perawatan Kolam Renang", "Masalah yang Sering Terjadi Tanpa Perawatan Rutin", "Perawatan untuk Rumah, Villa, Hotel, dan Properti Lain", "Area Layanan", "Portofolio Perawatan Kolam Renang", "Konsultasi Perawatan Kolam Renang" — ditambah 2 H2 shared (FAQ, CTA band, tidak disentuh) |
| H3 | **2 baru** — judul 2 kartu portofolio |
| H4 | **0 di konten** (footer shared tetap H4, tidak disentuh) |
| Word count | **354 kata** (naik dari ±91) |
| Images | **2 baru** — `photo.php?id=16` (738×414) dan `id=12` (426×292), keduanya `alt`/`width`/`height`/`loading="lazy"` |
| Internal links (di konten) | **11 baru**: 2 problem terkait (air hijau, kebocoran), 1 layanan kimia air, 1 layanan kontrak bulanan (sebelumnya disebut tanpa link), 1 artikel kesalahan umum, 2 area (Bogor Kota, Cijeruk) + 1 hub area, 2 portofolio, 1 WhatsApp |
| FAQ | **3 pertanyaan** — 2 lama dipertahankan persis, 1 baru (fakta terverifikasi) |
| Schema `Service` | Nilai `name`/`description` otomatis mengikuti `title`/`intro` (sebagian besar tidak berubah); `areaServed: "Bogor"` tetap sama — logic ini di `service.php` (shared, di luar scope) |
| Portofolio ditampilkan | **2** — "Perawatan Kolam Rutin" (Bogor Kota) & "Perawatan Kolam Renang di Cijeruk, Bogor" |
| Area disebutkan | **2 ditautkan** (Bogor Kota, Cijeruk) + 1 link ke hub area — bukan daftar massal |
| Fakta bisnis terverifikasi disebut | **Ya** — "10+ tahun pengalaman, 350+ proyek" di pengantar section portofolio + FAQ baru |

---

## Portfolio Inventory

Inventarisasi LANGSUNG dari `/portofolio/` (7 item total di database), dicari yang bertema/relevan "Perawatan":

| Judul | Area | Foto | Deskripsi Tersedia? | Dipakai di Halaman Ini? |
|---|---|---|---|---|
| Perawatan Kolam Rutin | Bogor Kota | `photo.php?id=16` (738×414, jpeg) | ✅ Lengkap (perawatan bulanan hotel) | ✅ Ya |
| Perawatan Kolam Renang di Cijeruk, Bogor | Cijeruk | `photo.php?id=12` (426×292, png) | ✅ Lengkap (pengecekan air, sirkulasi, pompa, filter) | ✅ Ya |
| Renovasi Kolam Renang di Puncak Bogor | Puncak | `photo.php?id=13` | Tema renovasi, bukan perawatan rutin | ❌ Tidak relevan untuk halaman ini |
| Kolam Renang Villa Modern di Sentul | Sentul | `photo.php?id=11` | Tema pembuatan/konstruksi | ❌ Tidak relevan |
| Kolam Renang Keluarga | Cibinong | `photo.php?id=14` | Tema pembuatan/konstruksi | ❌ Tidak relevan |
| Instalasi Sistem Filtrasi | Sentul | `photo.php?id=17` | Tema instalasi, bukan perawatan rutin | ❌ Tidak relevan |
| Perbaikan Kebocoran Kolam | Bogor Kota | `photo.php?id=15` | Tema perbaikan (sudah dipakai di halaman Problem SEO — ditautkan dari halaman ini sebagai "masalah terkait", bukan portofolio duplikat) | ⚠️ Ditautkan sebagai referensi masalah, bukan ditampilkan sebagai kartu portofolio |

**Kesimpulan inventarisasi:** Dari 7 total portofolio, **2 item genuinely bertema perawatan rutin** (bukan konstruksi/renovasi/instalasi/perbaikan struktural) — keduanya dipakai. Catatan: pemilik menyebut "lebih dari 20 pekerjaan nyata untuk setiap jenis pekerjaan", tapi database saat ini hanya berisi 7 item portofolio total (bukan 20+) — **`DATA_REQUIRED`** kalau memang ada dokumentasi proyek tambahan yang belum diinput ke sistem (lihat §DATA_REQUIRED).

---

## Database Changes

| Item | Detail |
|---|---|
| Tabel | `pages` |
| Baris target | `url_path = '/layanan/perawatan-pembersihan-rutin/'` DAN `type = 'service'` — `id` dibaca dulu, UPDATE dikunci ke `id` + `url_path` + `type` sekaligus |
| Kolom yang diubah | `title` (nilai sama, ditulis eksplisit), `h1` (**berubah**, lihat Before/After), `meta_title`, `meta_description`, `content`, `faq_json` |
| Kolom yang **tidak** disentuh | `intro`, `url_path`, `type`, `area_ref`, `service_ref`, `status`, `sort_order`, `tier`, `target_keyword`, `cover_image` |
| Proteksi endpoint | Basic Auth via `.htaccess` (pola sama seperti semua skrip `fix-*.php` lain) |
| Idempotensi | Aman dijalankan berulang (UPDATE murni) |
| Kredensial | Tidak ditampilkan — memakai `inc/db.php` sesuai pola proyek |

Nilai `before` (title/h1/meta_title/meta_description lama) otomatis dibaca & dilaporkan dalam response JSON skrip saat admin menjalankannya.

---

## Files Modified

| File | Alasan | Halaman lain terdampak? |
|---|---|---|
| `fix-service-perawatan-rutin.php` (baru) | Implementasi seluruh optimasi — konten halaman ini 100% dari 1 baris database | **Tidak** — query dikunci `id`+`url_path`+`type` |
| `.htaccess` | Melindungi skrip baru dengan Basic Auth | Tidak memengaruhi proteksi file lain |

**TIDAK disentuh:** `inc/templates/service.php`, homepage, 19 halaman layanan lain (termasuk 2 yang sudah dioptimasi di Phase 3 & 6 — dibiarkan seperti hasil fase masing-masing), 10 halaman area, artikel, portofolio, skema database, routing, sitemap, robots.txt.

---

## DATA_REQUIRED

| Item | Status |
|---|---|
| **Portofolio tambahan** ("lebih dari 20 pekerjaan nyata" menurut pemilik) | `DATA_REQUIRED` — database saat ini hanya punya 7 item portofolio total, 2 di antaranya tema perawatan. Kalau memang ada proyek perawatan lain yang sudah dikerjakan tapi belum didokumentasikan di sistem portofolio, itu perlu diinput dulu (foto + deskripsi) lewat panel admin sebelum bisa ditampilkan — tidak dikarang di sini. |
| Testimoni khusus perawatan | `DATA_REQUIRED` — tabel `testimonials` tidak punya baris `published` (temuan berulang). |
| Jumlah teknisi/tim perawatan | `DATA_REQUIRED` — tidak ada data ini di mana pun di source. |
| Sertifikasi | `DATA_REQUIRED` — tidak ada data untuk diverifikasi. |
| Harga paket perawatan | `DATA_REQUIRED` — sesuai kebijakan situs (estimasi setelah survei), tidak ditambahkan sebagai angka pasti. |

---

## SHARED_TEMPLATE — OUT OF SCOPE

| Nama File | Masalah | Kenapa Tetap Di Luar Scope |
|---|---|---|
| `inc/templates/service.php` | H2 CTA band duplikat literal H1 (`render_cta_band($page['title'], ...)`) — sekarang lebih terlihat karena H1 sudah diperkuat jadi "Jasa Perawatan Kolam Renang" sementara CTA band masih pakai `title` lama ("Perawatan & Pembersihan Rutin") | Perbaikannya butuh edit template ini, memengaruhi 20 halaman layanan. Sudah tercatat sejak audit halaman "Pembuatan Kolam Renang Baru" — tetap tidak disentuh. |
| `inc/templates/service.php` | Schema `Service.areaServed` string generik `'Bogor'` | Sama — level template, 20 halaman. Tidak disentuh. |

---

## Testing

Karena perubahan **belum di-deploy**, pengujian berikut adalah verifikasi statis/lokal:

| # | Test | Hasil |
|---|---|---|
| 1 | Skrip PHP valid | ✅ `php -l` lolos |
| 2 | Heading hierarchy valid (H1→H2→H3, tidak ada skip) | ✅ 7× H2, 2× H3, 0× H4 di konten |
| 3 | 11 URL tujuan internal link valid | ✅ Semua dicek `curl` **sebelum** ditautkan — 200 OK semua |
| 4 | Gambar bukan gambar rusak, dimensi akurat | ✅ `getimagesize()` nyata (738×414, 426×292), sesuai atribut yang ditulis |
| 5 | Deskripsi kartu portofolio tidak dikarang | ✅ Kalimat pertama identik dengan deskripsi asli di halaman detail portofolio |
| 6 | Tahapan Pengerjaan (6 langkah) tidak diubah isinya | ✅ Identik, hanya dipindah dari `.info-box`/H4 ke `<ol>` di bawah H2 |
| 7 | FAQ lama dipertahankan persis | ✅ Identik kata-per-kata |
| 8 | **Bug ditemukan & diperbaiki:** `<a>` di dalam `faq_json` akan di-escape oleh `render_faq_block()` (`h($item['a'])`), tidak akan jadi link | ✅ Diperbaiki — FAQ #2 dikembalikan ke teks polos (link ke Kontrak Perawatan Bulanan sudah ada di konten utama sebagai gantinya) |
| 9 | Query UPDATE dikunci 1 baris (id+url_path+type) | ✅ Tidak ada risiko UPDATE massal |
| 10 | Meta title/description panjang wajar | ✅ 62 & 154 karakter |
| 11 | Idempoten | ✅ UPDATE murni, aman dijalankan berulang |

**Belum bisa diuji sampai deploy disetujui:** HTTP 200 live, canonical/robots live, tampilan visual browser, CTA live, homepage & halaman lain live.

---

## Regression

**Terhadap kode/file:** Dijamin oleh scope query (1 baris, dikunci `id`+`url_path`+`type`) dan `inc/templates/service.php` tidak disentuh — 19 halaman layanan lain (termasuk hasil Phase 3 & 6), homepage, 10 halaman area, semua artikel, semua portofolio dijamin tidak berubah.

**Terhadap situs live:** Belum bisa diverifikasi karena belum dideploy. Rekomendasi verifikasi setelah deploy & skrip dijalankan:
1. Fetch `/` — pastikan tidak berubah.
2. Fetch `/layanan/pembuatan-kolam-renang-baru/` (Phase 3) dan `/layanan/perbaikan-kebocoran-kolam/` (Phase 6) — pastikan tidak berubah, dan link BARU dari halaman ini ke `/layanan/perbaikan-kebocoran-kolam/` tetap mengarah ke halaman yang benar.
3. Fetch `/area/bogor-kota/` (Phase 5) — pastikan tidak berubah.
4. Fetch `/portofolio/perawatan-kolam-rutin/` dan `/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/` — pastikan tidak berubah.
5. Fetch `/layanan/perawatan-pembersihan-rutin/` — pastikan H1/heading/meta/link/gambar/FAQ baru tampil sesuai §After.

---

# FINAL STATUS

✅ Target ditemukan & dikonfirmasi dari data live (bukan asumsi).
✅ Implementasi selesai untuk seluruh optimasi (metadata, H1, heading, konten, portofolio, gambar, internal link, FAQ) — perubahan konten 1 baris database.
✅ Bug potensial (link ter-escape di FAQ) ditemukan & diperbaiki sebelum deploy.
🛑 2 isu shared-template (`service.php`) dikonfirmasi **tetap out-of-scope**.
⏳ **Belum di-commit, belum di-push**, sesuai instruksi eksplisit.

**STOP — menunggu review.**
