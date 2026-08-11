# PHASE — FIX ORPHAN PAGES / INTERNAL LINK DISCOVERY

Status: **implementasi lokal selesai, BELUM dieksekusi ke database** (script belum dijalankan admin via browser), **BELUM commit, BELUM push**.

Sumber kebenaran: `INDEXING-CRAWL-AUDIT.md` (audit sebelumnya) + crawl/fetch LIVE baru yang dilakukan ulang khusus untuk fase ini (bukan asumsi URL). Script implementasi: `fix-orphan-internal-links.php`.

---

## 1. Total Orphan Sebelum

40 orphan (dikonfirmasi ulang lewat crawl internal live, bukan hanya dari sitemap):
- 36 halaman combo Area × Service
- 4 halaman portofolio

---

## 2. Inventarisasi 36 Combo Pages (Sebelum)

Pola URL: `/area/{area}/{service}/`. Dikonfirmasi live: **9 area × 4 layanan = 36**, bukan 40 — **Puncak tidak memiliki combo page sama sekali** (dicoba langsung: `/area/puncak/instalasi-filter-pompa/` → HTTP 404, dan tidak ada satupun entri `/area/puncak/*` di sitemap). Ini bukan kesalahan crawl, dikonfirmasi ulang untuk fase ini.

| Area | Parent Area URL | Service | Combo URL | HTTP | Canonical | Indexable | Orphan (crawl) |
|---|---|---|---|---|---|---|---|
| Sentul | `/area/sentul/` | 4 layanan | `/area/sentul/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Ciawi | `/area/ciawi/` | 4 layanan | `/area/ciawi/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Bogor Kota | `/area/bogor-kota/` | 4 layanan | `/area/bogor-kota/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Cibinong | `/area/cibinong/` | 4 layanan | `/area/cibinong/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Yasmin | `/area/yasmin/` | 4 layanan | `/area/yasmin/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Cijeruk | `/area/cijeruk/` | 4 layanan | `/area/cijeruk/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Rancamaya | `/area/rancamaya/` | 4 layanan | `/area/rancamaya/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Bogor Raya | `/area/bogor-raya/` | 4 layanan | `/area/bogor-raya/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| Karadenan | `/area/karadenan/` | 4 layanan | `/area/karadenan/{service}/` | 200 (4/4) | self | Ya | Ya (0 incoming) |
| **Puncak** | `/area/puncak/` | — | tidak ada | 404 | — | — | tidak relevan (tidak ada halamannya) |

4 service slug per area (sama untuk semua 9 area, dikonfirmasi via fetch H1 tiap combo): `pembuatan-kolam-renang-baru` (Pembuatan Kolam Renang Baru), `perawatan-pembersihan-rutin` (Perawatan & Pembersihan Rutin — catatan: slug ini masih memakai nama lama, berbeda dari money page `/layanan/jasa-perawatan-kolam-renang/` yang sudah dimigrasi; **tidak diubah** di fase ini sesuai larangan "jangan mengubah URL combo"), `renovasi-perbaikan-kolam` (Renovasi & Perbaikan Kolam), `instalasi-filter-pompa` (Instalasi Filter & Pompa).

---

## 3. Inventarisasi 4 Portofolio Orphan (Sebelum)

Diaudit satu per satu lewat fetch live penuh (title, H1, meta, intro/content):

| URL | HTTP | Canonical | Indexable | Temuan Konten Aktual |
|---|---|---|---|---|
| `/portofolio/kolam-renang-villa-modern/` | 200 | self | Ya | "Pembuatan kolam renang infinity edge untuk villa di **kawasan perbukitan**." Area tidak disebut eksplisit (bukan "Sentul", "Puncak", dll — hanya "perbukitan", ambigu). Service jelas: **Pembuatan**. |
| `/portofolio/perawatan-kolam-renang-di-area-belum-dipilih/` | 200 | self | Ya | Title & konten **literally** mengandung teks "(area belum dipilih)" — placeholder admin yang belum diisi, bukan proyek nyata. |
| `/portofolio/perawatan-kolam-renang-di-cijeruk/` | 200 | self | Ya | Title & konten berisi **catatan internal admin** ("saya mau deskripsi dapat dibuat oleh ai dengan cara. admin mengisi judul dan area layanan...") — bukan deskripsi proyek, kemungkinan ter-paste tidak sengaja ke field yang salah. |
| `/portofolio/renovasi-kolam-resort/` | 200 | self | Ya | "Renovasi total lapisan kolam dan sistem filtrasi **tahan cuaca dingin**." Area tidak disebut nama, tapi frasa "tahan cuaca dingin" cocok dengan karakteristik Puncak yang SUDAH dipublikasikan di `/area/puncak/` ("suhu udara yang lebih dingin", "curah hujan tinggi"). Service jelas: **Renovasi**. |

Catatan penting: URL `/portofolio/perawatan-kolam-renang-di-cijeruk/` (tanpa akhiran `-bogor`) **berbeda** dari 2 halaman Cijeruk yang sudah dioptimasi di fase sebelumnya (`perawatan-kolam-renang-di-cijeruk-bogor/` dan `-cijeruk-bogor-13/`). Ini adalah entri ke-3 yang terpisah, dan kontennya rusak/tidak valid seperti dijelaskan di atas — **tidak digabung, tidak dihapus, tidak diubah datanya** sesuai batasan fase ini.

---

## 4. Link yang Ditambahkan (ADDED)

### A. 36 combo pages — link dari halaman area induk

Untuk 9 area (Puncak dikecualikan, tidak punya combo), ditambahkan satu blok baru `<h2>Detail Layanan Kami di [Area]</h2>` + kalimat konteks (memakai fakta karakteristik area yang **sudah** dipublikasikan di halaman itu sendiri, bukan karangan baru) + daftar 4 link ke combo page area tersebut, dengan anchor text natural & unik per kombinasi area+layanan (mis. "Pembuatan Kolam Renang Baru di Sentul", bukan "Klik di sini" berulang):

| Area | Link Ditambahkan | Sumber Konteks Kalimat |
|---|---|---|
| Sentul | 4/4 link ke combo Sentul | "kontur lahan berbukit dan karakter villa & resort" — sudah ada di `/area/sentul/` |
| Ciawi | 4/4 link ke combo Ciawi | "jalur Ciawi menuju Puncak" — sudah ada di `/area/ciawi/` |
| Bogor Kota | 4/4 link ke combo Bogor Kota | "kepadatan permukiman" — sudah ada di `/area/bogor-kota/` |
| Cibinong | 4/4 link ke combo Cibinong | "permukiman dan perkantoran padat" — sudah ada di `/area/cibinong/` |
| Yasmin | 4/4 link ke combo Yasmin | "kolam rumah tinggal berpemakaian harian" — sudah ada di `/area/yasmin/` |
| Cijeruk | 4/4 link ke combo Cijeruk | "kelembapan udara dan curah hujan tinggi" — sudah ada di `/area/cijeruk/` |
| Rancamaya | 4/4 link ke combo Rancamaya | "gated community", "kolam privat besar" — sudah ada di `/area/rancamaya/` |
| Bogor Raya | 4/4 link ke combo Bogor Raya | "residensial modern" — sudah ada di `/area/bogor-raya/` |
| Karadenan | 4/4 link ke combo Karadenan | "terus berkembang", akses jalan — sudah ada di `/area/karadenan/` |

**Total: 36/36 link baru**, semua target combo URL diverifikasi HTTP 200 sebelum dipakai (lihat §2).

### B. 2 dari 4 portofolio orphan — link dari halaman relevan

| Portofolio | Link Ditambahkan Dari | Anchor Text | Justifikasi |
|---|---|---|---|
| `/portofolio/kolam-renang-villa-modern/` | `/layanan/pembuatan-kolam-renang-baru/` (di dalam paragraf "Pengalaman dan Proyek" yang sudah menyebut 2 contoh proyek lain) | "Kolam Renang Villa Modern" | Service (pembuatan) eksplisit disebut di deskripsi portofolio. Area TIDAK dilink karena tidak disebut eksplisit (lihat §6 DATA_REQUIRED). |
| `/portofolio/renovasi-kolam-resort/` | `/area/puncak/` (paragraf baru sebelum "Bacaan Terkait") DAN `/layanan/renovasi-perbaikan-kolam/` (paragraf baru setelah galeri proyek renovasi) | "renovasi kolam renang resort dengan sistem tahan cuaca dingin" | Area: frasa "tahan cuaca dingin" cocok dengan karakteristik Puncak yang sudah live. Service (renovasi) eksplisit. |

---

## 5. Link yang Sudah Ada (ALREADY_LINKED)

**Tidak ada.** Dikonfirmasi lewat live fetch sebelum implementasi: tidak satupun dari 9 area yang sudah memiliki link ke combo page-nya sendiri, dan kedua halaman layanan (pembuatan, renovasi) belum memiliki link ke 2 portofolio orphan yang relevan. Seluruh 38 link (36 combo + 2 portofolio) adalah net-new. Script itu sendiri tetap melakukan pengecekan `strpos()` per-link sebelum menambahkan (defensif, untuk kondisi di database saat script benar-benar dijalankan, jika berbeda dari saat live-fetch ini dilakukan) — hasil aktualnya akan tercatat di response JSON saat admin menjalankan script.

---

## 6. Portofolio yang Membutuhkan DATA_REQUIRED

| Portofolio | Alasan | Tindakan |
|---|---|---|
| `/portofolio/kolam-renang-villa-modern/` | Area tidak disebut eksplisit ("kawasan perbukitan" — bisa Sentul, Puncak, atau Ciawi, tidak bisa dipastikan) | **Tidak dilink dari area manapun.** Hanya dilink dari service page (§4B). |
| `/portofolio/perawatan-kolam-renang-di-area-belum-dipilih/` | Konten literally berisi placeholder "(area belum dipilih)" — bukan proyek nyata yang bisa diverifikasi | **Tidak ada link ditambahkan sama sekali.** Lihat §7. |
| `/portofolio/perawatan-kolam-renang-di-cijeruk/` | Konten berisi catatan internal admin (instruksi cara membuat deskripsi lewat AI), bukan deskripsi proyek | **Tidak ada link ditambahkan sama sekali.** Data ini tampak seperti kesalahan input — direkomendasikan admin meninjau ulang entri ini (bukan tindakan saya, di luar scope aplikasi). |

---

## 7. Portofolio Legacy "area belum dipilih"

Diaudit khusus sesuai instruksi:

- **Bukan** proyek nyata dengan lokasi belum tersedia — judul, H1, meta description, dan isi konten SEMUANYA secara konsisten memuat literal string `(area belum dipilih)` menggantikan nama area, di posisi yang seharusnya diisi nama lokasi asli. Ini adalah tanda field "area" pada form admin tidak diisi saat entri dibuat, dan sistem template menghasilkan halaman dengan placeholder tersebut alih-alih nama area sungguhan.
- Tidak ada bukti ini record legacy dari sistem lama — strukturnya identis dengan 8 portofolio baru lain yang sudah dioptimasi di fase sebelumnya (deskripsi generik "pemeriksaan kondisi air, kebersihan kolam, sistem sirkulasi, pompa, dan filter..." — pola yang sama persis), menunjukkan ini kemungkinan besar entri yang **sedang** dibuat tapi form-nya belum lengkap saat disimpan.
- **Keputusan**: `DATA_REQUIRED`. Tidak diubah datanya, tidak dihapus, tidak diberi link internal baru (karena tidak ada area/lokasi valid untuk dijadikan dasar link yang genuinely relevan). Direkomendasikan ke admin: lengkapi field area pada entri ini lewat `edit-portfolio.php`, atau hapus jika memang draft yang tidak jadi dipakai — keputusan konten ini di luar scope perbaikan internal link yang saya kerjakan.

---

## 8. Total Record Database yang Berubah (rencana eksekusi)

Saat script `fix-orphan-internal-links.php` dijalankan admin, akan terjadi **11 UPDATE** pada tabel `pages` (masing-masing dikunci `id` + `url_path` + `type`, `id` dibaca dulu via SELECT):

| # | url_path | type | Perubahan |
|---|---|---|---|
| 1-9 | `/area/{sentul,ciawi,bogor-kota,cibinong,yasmin,cijeruk,rancamaya,bogor-raya,karadenan}/` | area | Append blok 4 link combo |
| 10 | `/layanan/pembuatan-kolam-renang-baru/` | service | str_replace presisi: tambah 1 link portofolio |
| 11 | `/layanan/renovasi-perbaikan-kolam/` | service | str_replace + insert presisi: ubah 1 kata ("salah satu"→"beberapa") + tambah 1 paragraf link |

Plus **1 UPDATE** pada `/area/puncak/` (type=area) untuk menambahkan paragraf link ke `renovasi-kolam-resort` — **total 12 UPDATE**.

Tidak ada UPDATE pada 2 portofolio DATA_REQUIRED, tidak ada UPDATE pada halaman manapun yang tidak disebut di atas, tidak ada INSERT, tidak ada DELETE.

**Backup isi `content` lama**: script menyertakan isi penuh `content` SEBELUM perubahan untuk seluruh 12 baris di response JSON (key `backups`), sehingga admin punya salinan persis sebelum-dan-sesudah begitu script dijalankan. Sebagai cadangan tambahan, isi `content` "sebelum" untuk seluruh 12 halaman ini juga sudah saya simpan secara terpisah dari hasil fetch live sebelum implementasi (dipakai untuk dry-run verifikasi transformasi di bawah).

**Verifikasi dry-run (dilakukan terhadap salinan konten live nyata, BUKAN terhadap database)**: seluruh 3 transformasi non-trivial (str_replace pembuatan, str_replace+insert renovasi, insert Puncak) sudah diuji dengan PHP murni terhadap teks `content` yang benar-benar live saat ini — seluruhnya menghasilkan HTML valid dan mengandung link target persis 1x. Untuk 9 blok combo (append sederhana, tidak ada string matching kompleks) — diverifikasi seluruh 36 URL combo target BELUM ada di content manapun (tidak ada risiko duplikasi).

---

## 9. HTTP Validation (Sebelum Eksekusi)

Seluruh 40 URL orphan di-fetch live sebelum implementasi: **40/40 HTTP 200**, seluruhnya `index, follow`, seluruhnya canonical self-referencing, tidak ada redirect. (Detail lengkap di §2 dan §3.)

---

## 10. Full Crawl Setelah Perubahan

**PENDING — menunggu admin menjalankan script.** Crawl ulang situs penuh baru bisa dilakukan setelah `fix-orphan-internal-links.php` benar-benar dieksekusi terhadap database live (saya tidak punya akses database langsung; hanya admin yang bisa menjalankan script via browser dengan Basic Auth). Begitu dikonfirmasi sudah jalan, saya akan:
1. Fetch ulang seluruh 40 URL orphan untuk memastikan tetap 200, canonical benar, robots index/follow, tidak ada redirect baru.
2. Crawl ulang seluruh situs untuk menghitung incoming link baru per halaman.
3. Update laporan ini dengan hasil aktual.

---

## 11. Total Orphan Setelah Perubahan

**PENDING** — sama seperti §10, membutuhkan eksekusi live dan crawl ulang. Target (belum terverifikasi): 36 combo pages dan 2 dari 4 portofolio (`kolam-renang-villa-modern`, `renovasi-kolam-resort`) masing-masing mendapat >=1 incoming link, sehingga tersisa maksimal 2 orphan (`perawatan-kolam-renang-di-area-belum-dipilih`, `perawatan-kolam-renang-di-cijeruk` — sengaja tidak diberi link karena DATA_REQUIRED).

---

## 12. Broken Links Setelah Perubahan

**PENDING** — akan dicek saat crawl ulang (§10). Tidak diekspektasikan ada broken link baru karena seluruh 38 link yang ditambahkan menunjuk ke URL yang sudah diverifikasi HTTP 200 sebelum dipakai.

---

## 13. Regression Result

**PENDING eksekusi**, tapi berikut yang sudah diverifikasi/dijamin oleh desain script:

- Script hanya melakukan `UPDATE ... SET content = ... WHERE id = :id AND url_path = :url_path AND type = :type` — tidak ada query lain, tidak menyentuh baris di luar 12 target, tidak menyentuh tabel `portfolio`, `photos`, atau lainnya.
- Tidak ada perubahan pada `title`, `h1`, `meta_description`, `intro`, `faq_json`, atau schema JSON-LD manapun — semua perubahan murni pada field `content`.
- Tidak ada file template, `.htaccess` (kecuali penambahan proteksi Basic Auth untuk script baru ini sendiri), `robots.txt`, atau `sitemap` yang diubah.
- Setelah script dijalankan, saya akan memverifikasi live: homepage 200, `/layanan/` 200, `/area/` 200, seluruh 10 area 200, `/portofolio/` 200, money page 200, seluruh 20 layanan 200, seluruh 10 artikel 200 — dan melaporkan hasilnya di sini.

---

## File yang Diubah/Ditambahkan

- **Baru**: `fix-orphan-internal-links.php` — script implementasi (di-lint `php -l`, tanpa error; transformasi non-trivial sudah diuji dry-run terhadap konten live nyata).
- **Baru**: `ORPHAN-INTERNAL-LINK-PHASE.md` — laporan ini.
- **Diubah**: `.htaccess` — menambahkan `fix-orphan-internal-links\.php` ke daftar `FilesMatch` yang diproteksi Basic Auth.
- Tidak ada file lain yang disentuh.

---

## Status & Langkah Selanjutnya

Implementasi lokal selesai, sesuai instruksi **tidak commit, tidak push, tidak deploy, tidak lanjut ke halaman lain, tidak membuat konten baru**. Menunggu instruksi Anda untuk:
1. Menjalankan `fix-orphan-internal-links.php` di server (via browser, Basic Auth).
2. Setelah itu saya akan melengkapi §10-13 dengan hasil crawl ulang live yang sebenarnya.
3. Commit + push — menunggu konfirmasi terpisah.
