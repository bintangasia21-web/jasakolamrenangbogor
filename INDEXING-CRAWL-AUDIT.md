# INDEXING & CRAWL AUDIT — jasakolamrenangbogor.com

Status: **AUDIT MURNI, TIDAK ADA PERUBAHAN**. Tidak ada database, konten, URL, .htaccess, robots.txt, sitemap, template, internal link, schema, atau kode aplikasi yang diubah. Semua data di bawah berasal dari fetch HTTP live terhadap situs, bukan asumsi maupun hasil Google Search.

Metodologi: crawl otomatis dimulai dari homepage, mengikuti seluruh `<a href>` internal yang ditemukan (BFS), ditambah fetch langsung terhadap seluruh URL yang ada di `sitemap.xml` (termasuk yang tidak ditemukan lewat crawl, untuk mendeteksi orphan page). Total 103 URL diperiksa.

---

## A. Executive Summary

- **103 URL live diperiksa**, seluruhnya HTTP 200 (tidak ditemukan 404/5xx pada crawl maupun pada seluruh isi sitemap).
- **103 URL unik di sitemap.xml** (dari 104 entri — ada 1 duplikat: `/faq/` muncul 2x).
- **103 dari 103 URL indexable** (status 200, tidak ada `noindex` di meta robots maupun `X-Robots-Tag`). Tidak ditemukan satupun halaman ber-`noindex` atau `nofollow`.
- **Tidak ada canonical mismatch** — seluruh halaman yang berhasil di-crawl memiliki canonical self-referencing yang benar (canonical == URL halaman itu sendiri).
- **Masalah utama BUKAN indexability, tapi DISCOVERY**: 40 URL (36x Combo (Area x Layanan), 4x Portfolio) ada di sitemap dan HTTP 200, tapi **nol internal incoming link** — tidak dapat ditemukan crawler hanya dengan mengikuti link dari halaman manapun yang live di situs ini. Ini P0.
- **6 dari 10 halaman area** (Cijeruk, Puncak, Yasmin, Rancamaya, Bogor Raya, Karadenan) hanya mendapat 3-5 incoming link (homepage + hub `/area-layanan/` + kadang 1 portofolio), sementara 4 area lain (Bogor Kota, Sentul, Ciawi, Cibinong) mendapat 64-66 incoming link karena tampil di footer/nav SETIAP halaman situs. Ini kesenjangan struktural, bukan bug — tapi berdampak pada distribusi otoritas internal.
- **Money page** (`/layanan/jasa-perawatan-kolam-renang/`) indexable, canonical benar, ada di sitemap, dan punya 24 incoming link dari kombinasi homepage, hub layanan, 6 halaman layanan lain, 4 halaman area, dan seluruh 11 portofolio perawatan. Kondisinya kuat — lihat §G.
- Tidak ada broken internal link ke URL lama `/layanan/perawatan-pembersihan-rutin/` — migrasi money page sebelumnya bersih, dan redirect 301 → 200 berfungsi.
- Sesuai instruksi, status index aktual di Google **tidak disimpulkan** dari crawl ini — ditandai `GSC_REQUIRED` (§P).

---

## B. Live URL Inventory

Berdasarkan link live yang ditemukan lewat crawl (BFS dari homepage) digabung dengan seluruh URL di sitemap.xml (untuk menangkap yang tidak ter-link sama sekali):

| Kelompok | Jumlah URL |
|---|---|
| Homepage | 1 |
| Hub | 6 |
| Layanan | 20 |
| Area | 10 |
| Portfolio | 20 |
| Artikel | 10 |
| Combo (Area x Layanan) | 36 |
| Lainnya | 0 |
| **Total** | **103** |

Catatan kategori:
- **F. Problem/solution**: tidak ada namespace URL terpisah untuk problem/solution. Halaman "masalah" (mis. kebocoran kolam) hidup sebagai bagian dari kategori Layanan (`/layanan/perbaikan-kebocoran-kolam/`) dan Portfolio (`/portofolio/perbaikan-kebocoran-kolam/`), bukan struktur URL tersendiri.
- **G. Combo area × layanan**: pola URL `/area/[area]/[layanan]/`, ditemukan 4 layanan × 9 area = 36 URL live (area Puncak tidak memiliki kombinasi ini — dikonfirmasi 404 saat dicoba langsung, jadi bukan kesalahan crawl).
- **Hub** mencakup: `/layanan/`, `/area/`, `/portofolio/`, `/artikel/`, `/area-layanan/`, `/faq/`, `/kontak/`.

---

## C. Sitemap Audit

- Fetch langsung `https://jasakolamrenangbogor.com/sitemap.xml` → **HTTP 200**.
- Jumlah entri `<loc>`: **104** (103 URL unik — ada duplikat: `/faq/` muncul 2x, sisanya unik).
- Bukan sitemap index (tidak ada sub-sitemap terpisah).
- Seluruh entri sitemap memakai domain non-www (`https://jasakolamrenangbogor.com/...`), konsisten dengan domain kanonik situs.
- Semua 103 URL dalam sitemap di-fetch langsung: **seluruhnya HTTP 200**. Tidak ada SITEMAP_REDIRECT maupun SITEMAP_MISSING.
- Tidak ditemukan URL lama (`/area-sentul.html`, `/layanan/perawatan-pembersihan-rutin/`, dll) di dalam sitemap — sitemap sudah bersih dari legacy URL.

**Klasifikasi:**

- `SITEMAP_OK`: 103 URL (semua entri, kecuali catatan duplikat di bawah).
- `SITEMAP_MISSING`: tidak ada (sitemap fetch berhasil).
- `SITEMAP_REDIRECT`: tidak ada — tidak ada entri sitemap yang me-redirect.
- `SITEMAP_NONCANONICAL`: tidak ada — setiap URL sitemap yang berhasil di-fetch memiliki `<link rel=canonical>` yang menunjuk ke dirinya sendiri.
- `SITEMAP_POTENTIAL_PROBLEM`: **1** — `/faq/` terdaftar 2x (duplikat literal dalam XML, bukan 2 URL berbeda). Tidak fatal (Google akan dedupe), tapi seharusnya 1 entri saja.

---

## D. Robots Audit

robots.txt live (`https://jasakolamrenangbogor.com/robots.txt`, HTTP 200):

```
User-agent: *
Allow: /
Disallow: /admin.html

Sitemap: https://www.jasakolamrenangbogor.com/sitemap.xml
```

Analisis:
- `Allow: /` + hanya `Disallow: /admin.html` → tidak ada rule yang memblokir homepage, `/layanan/`, `/area/`, `/portofolio/`, `/artikel/`, atau money page. Semua **tidak diblokir (benar-benar not blocked)**, bukan sekadar "tidak di-disallow" secara implisit — rule `Allow: /` eksplisit mengizinkan seluruh path kecuali yang di-disallow.
- `/admin.html` di-disallow — relevan (halaman admin, benar-benar dimaksudkan untuk diblokir dari crawler, sudah sesuai).
- **Catatan**: baris `Sitemap:` di robots.txt menunjuk ke `https://www.jasakolamrenangbogor.com/sitemap.xml` (pakai **www**), padahal domain kanonik situs adalah non-www dan `www.jasakolamrenangbogor.com/sitemap.xml` di-redirect 301 ke versi non-www. Diverifikasi live: crawler harus mengikuti 1 hop redirect tambahan untuk sampai ke sitemap yang benar. Bukan blocker, tapi tidak ideal — idealnya baris `Sitemap:` langsung menunjuk ke domain kanonik tanpa redirect.

---

## E. Indexability Audit

Dari 103 URL yang diperiksa: **103 INDEXABLE**, 0 NOINDEX, 0 NOFOLLOW, 0 BLOCKED (robots.txt), 0 CANONICAL_MISMATCH, 0 CANONICAL_SELF issue (semua canonical yang ada sudah self-referencing dengan benar), 0 REDIRECT tak terduga.

Prioritas yang diperiksa eksplisit:

| Target | HTTP | Meta Robots | X-Robots-Tag | Canonical | Status |
|---|---|---|---|---|---|
| `/layanan/jasa-perawatan-kolam-renang/` (money page) | 200 | index, follow | - | self | INDEXABLE |
| 20 halaman layanan | semua 200 | semua `index, follow` | tidak ada | semua self | INDEXABLE (20/20) |
| 10 halaman area | semua 200 | semua `index, follow` | tidak ada | semua self | INDEXABLE (10/10) |
| 20 halaman portofolio | semua 200 | semua `index, follow` | tidak ada | semua self | INDEXABLE (20/20) |
| 10 artikel | semua 200 | semua `index, follow` | tidak ada | semua self | INDEXABLE (10/10) |
| 36 halaman combo area×layanan | semua 200 | semua `index, follow` | tidak ada | semua self | INDEXABLE tapi **ORPHAN** (lihat §L) |

Kesimpulan Indexability: **tidak ada masalah teknis indexability**. Setiap halaman yang diperiksa bisa diindeks jika ditemukan. Masalah nyata ada di discovery/internal link (lihat bagian F, G, L).

---

## F. Internal Link Audit

Diperiksa lewat crawl link internal (bukan estimasi): untuk setiap URL dicatat jumlah *incoming* (halaman lain yang menaut ke sana) dan *outgoing* (link ke halaman lain).

Klasifikasi yang dipakai:
- `ORPHAN_PAGE` — 0 incoming link dari halaman manapun yang live/crawlable.
- `LOW_INTERNAL_LINK` — 1-2 incoming link.
- `GOOD_INTERNAL_LINK` — 3-5 incoming link.
- `STRONG_INTERNAL_LINK` — 6+ incoming link.

| Klasifikasi | Jumlah URL |
|---|---|
| ORPHAN_PAGE | 40 |
| LOW_INTERNAL_LINK | 10 |
| GOOD_INTERNAL_LINK | 23 |
| STRONG_INTERNAL_LINK | 30 |

### Cluster Money Page ↔ Area ↔ Portfolio ↔ Artikel ↔ Problem page

Diverifikasi arah hubungan aktual dari crawl (bukan asumsi):

- **Money page → Area**: TIDAK ADA link langsung dari money page ke halaman area manapun (dicek outgoing money page: 29 link, tidak ada satupun ke `/area/*`).
- **Area → Money page**: HANYA 4 dari 10 area (Bogor Kota, Cibinong, Puncak, Sentul) yang terbukti menaut ke money page dengan anchor "Perawatan & Pembersihan Rutin" (lihat incoming list §G). 6 area lain (Bogor Raya, Ciawi, Cijeruk, Karadenan, Rancamaya, Yasmin) **tidak ditemukan** menaut ke money page dalam crawl ini.
- **Portfolio → Money page**: KUAT — seluruh 11 portofolio bertema "Perawatan Kolam Renang" menaut ke money page dengan anchor "jasa perawatan kolam renang".
- **Money page → Portfolio**: TIDAK ADA link langsung dari money page ke portofolio manapun.
- **Artikel → Layanan/Area/Portfolio**: KUAT dan konsisten — semua 10 artikel menaut ke 5 target unik campuran layanan/area/portofolio.
- **Layanan/Area/Portfolio → Artikel**: TIDAK ditemukan link dari halaman layanan, area, atau portofolio manapun menuju artikel dalam crawl ini (arah baliknya kosong).

**Kesimpulan cluster**: hubungan yang diminta brief (`Money page ↕ Area ↕ Portfolio ↕ Problem ↕ Artikel`) baru terbentuk **satu arah** secara konsisten (Portfolio→Money page, Artikel→Layanan/Area/Portfolio). Arah sebaliknya (Area→Money page hanya 4/10, Money/Portfolio/Layanan/Area→Artikel nyaris tidak ada) belum terbentuk merata. Ini P2 — melemahkan topical cluster meski tidak menghalangi crawlability individual tiap halaman.

---

## G. Money Page Discovery

Target: `https://jasakolamrenangbogor.com/layanan/jasa-perawatan-kolam-renang/`

- Ada di sitemap: **Ya**
- Canonical: **self** (`https://jasakolamrenangbogor.com/layanan/jasa-perawatan-kolam-renang/`)
- Robots: **index, follow**, tidak ada X-Robots-Tag
- Incoming internal links: **24**
- Sumber incoming link (dikelompokkan):
  - `https://jasakolamrenangbogor.com/` — anchor: 'Jasa Perawatan Kolam Renang'; 'Lihat Layanan Ini →'
  - `https://jasakolamrenangbogor.com/layanan/` — anchor: 'Perawatan & Pembersihan RutinProgram perawatan ber'
  - `https://jasakolamrenangbogor.com/layanan/jasa-pengurasan-kolam-renang/` — anchor: 'perawatan rutin'
  - `https://jasakolamrenangbogor.com/layanan/perawatan-kolam-air-asin/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/layanan/vacuum-pembersihan-dasar-kolam/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/layanan/perawatan-kolam-musim-hujan/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/layanan/kontrak-perawatan-bulanan/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/layanan/penyeimbangan-kimia-air-kolam/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/area/sentul/` — anchor: 'Perawatan & Pembersihan Rutin'
  - `https://jasakolamrenangbogor.com/area/puncak/` — anchor: 'Perawatan & Pembersihan Rutin'
  - `https://jasakolamrenangbogor.com/area/bogor-kota/` — anchor: 'Perawatan & Pembersihan Rutin'
  - `https://jasakolamrenangbogor.com/area/cibinong/` — anchor: 'Perawatan & Pembersihan Rutin'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-rutin/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-sentul-bogor/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-ciawi-bogor/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-bogor-kota/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-cibinong-bogor/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-yasmin-bogor/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-cijeruk-bogor-13/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/jasa-perawawatan-kolam-renang-rutin-di-rancamaya/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-bogor-raya/` — anchor: 'jasa perawatan kolam renang'
  - `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-karadenan-bogor/` — anchor: 'jasa perawatan kolam renang'
- Homepage menaut? **Ya** (2 link, hero CTA + link teks).
- `/layanan/` (hub) menaut? **Ya** (1 link dari card layanan).
- Halaman area menaut? **Sebagian** — 4 dari 10 (Sentul, Puncak, Bogor Kota, Cibinong).
- Portofolio perawatan menaut? **Ya, semua 11.**
- Artikel relevan menaut? **Tidak ditemukan** — tidak ada satupun dari 10 artikel yang menaut langsung ke money page dalam crawl ini (artikel menaut ke halaman layanan lain atau area, bukan spesifik ke money page ini).

### MONEY_PAGE_DISCOVERY_SCORE: KUAT (dengan 1 celah)

Berdasarkan indikator yang benar-benar ditemukan (bukan teori SEO umum): 24 incoming link dari kombinasi hub, homepage, layanan terkait, 4 area, dan seluruh cluster portofolio perawatan menunjukkan discovery yang kuat secara struktural. Celah nyata: (1) 6 dari 10 halaman area belum menaut ke money page, (2) tidak ada artikel yang menaut langsung ke money page meski beberapa artikel membahas topik perawatan rutin.

---

## H. Portfolio Discovery

Total portofolio live: **20**. Sitemap: 20/20 ada di sitemap.

Klasifikasi (A=sangat mudah ditemukan ... E=benar-benar orphan), berdasarkan incoming link nyata:

| Portofolio | Incoming | Ditemukan dari | Klasifikasi |
|---|---|---|---|
| `kolam-renang-villa-modern-di-sentul/` | 21 | hub /portofolio/ + banyak sumber | A — sangat mudah ditemukan |
| `kolam-renang-keluarga/` | 20 | hub /portofolio/ + banyak sumber | A — sangat mudah ditemukan |
| `renovasi-kolam-renang-di-puncak-bogor/` | 19 | hub /portofolio/ + banyak sumber | A — sangat mudah ditemukan |
| `perawatan-kolam-rutin/` | 8 | hub /portofolio/ + banyak sumber | A — sangat mudah ditemukan |
| `perbaikan-kebocoran-kolam/` | 6 | hub /portofolio/ + banyak sumber | A — sangat mudah ditemukan |
| `perawatan-kolam-renang-di-cijeruk-bogor/` | 5 | /, /area/cijeruk/, /layanan/jasa-perawatan-kolam-renang/, /portofolio/, /portofolio/perawatan-kolam-renang-di-cijeruk-bogor-13/ | B — cukup mudah ditemukan |
| `instalasi-sistem-filtrasi/` | 4 | /, /area/sentul/, /layanan/instalasi-filter-pompa/, /portofolio/ | B — cukup mudah ditemukan |
| `perawatan-kolam-renang-di-cijeruk-bogor-13/` | 3 | /, /portofolio/, /portofolio/perawatan-kolam-renang-di-cijeruk-bogor/ | B — cukup mudah ditemukan |
| `perawatan-kolam-renang-di-sentul-bogor/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `perawatan-kolam-renang-di-ciawi-bogor/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `perawatan-kolam-renang-di-bogor-kota/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `perawatan-kolam-renang-di-cibinong-bogor/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `perawatan-kolam-renang-di-yasmin-bogor/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `jasa-perawawatan-kolam-renang-rutin-di-rancamaya/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `perawatan-kolam-renang-di-bogor-raya/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `perawatan-kolam-renang-di-karadenan-bogor/` | 2 | /, /portofolio/ | C — hanya ditemukan dari hub |
| `kolam-renang-villa-modern/` | 0 | (tidak ada — hanya via sitemap) | E — benar-benar orphan |
| `perawatan-kolam-renang-di-area-belum-dipilih/` | 0 | (tidak ada — hanya via sitemap) | E — benar-benar orphan |
| `perawatan-kolam-renang-di-cijeruk/` | 0 | (tidak ada — hanya via sitemap) | E — benar-benar orphan |
| `renovasi-kolam-resort/` | 0 | (tidak ada — hanya via sitemap) | E — benar-benar orphan |

Khusus portofolio bertema "Perawatan Kolam Renang" (11 item, subjek fase optimasi sebelumnya):
- Semua 11/11 ada di sitemap, HTTP 200, dan ditemukan dari hub `/portofolio/`.
- Semua terhubung ke money page (lihat §G).
- 9 dari 11 memiliki link ke halaman area yang sesuai (2 pengecualian: Perawatan Kolam Rutin dan Cijeruk-original — tidak diverifikasi apakah keduanya menaut area dalam struktur crawl ini, incoming count tidak mencerminkan outgoing).
- Rentang incoming: 2-8 — dominan `LOW` (2) atau `GOOD` (3-5), tidak ada yang orphan.

**4 portofolio yang benar-benar orphan** (ada di sitemap, HTTP 200, tapi 0 incoming link dari halaman manapun yang live-crawlable):
- `https://jasakolamrenangbogor.com/portofolio/kolam-renang-villa-modern/`
- `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-area-belum-dipilih/`
- `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-cijeruk/`
- `https://jasakolamrenangbogor.com/portofolio/renovasi-kolam-resort/`

Catatan: dua di antaranya terlihat seperti kemungkinan konten legacy/tidak selesai — `perawatan-kolam-renang-di-area-belum-dipilih` (nama slug secara harfiah berarti "area belum dipilih") dan `perawatan-kolam-renang-di-cijeruk` (mirip tapi bukan URL yang sama dengan 2 halaman Cijeruk yang sudah diaudit sebelumnya, yang slug-nya memakai akhiran `-bogor`). Ini murni observasi audit — **tidak diubah/dihapus/digabung** sesuai instruksi.

---

## I. Area Discovery

| Area | Sitemap | Canonical | Robots | Incoming | Outgoing | Klasifikasi |
|---|---|---|---|---|---|---|
| `bogor-kota/` | Ya | self | index,follow | 66 | 28 | STRONG |
| `sentul/` | Ya | self | index,follow | 65 | 27 | STRONG |
| `ciawi/` | Ya | self | index,follow | 65 | 22 | STRONG |
| `cibinong/` | Ya | self | index,follow | 64 | 26 | STRONG |
| `cijeruk/` | Ya | self | index,follow | 5 | 23 | GOOD |
| `puncak/` | Ya | self | index,follow | 3 | 26 | GOOD |
| `yasmin/` | Ya | self | index,follow | 3 | 23 | GOOD |
| `rancamaya/` | Ya | self | index,follow | 3 | 21 | GOOD |
| `bogor-raya/` | Ya | self | index,follow | 3 | 22 | GOOD |
| `karadenan/` | Ya | self | index,follow | 3 | 21 | GOOD |

Bukti proyek nyata per area (link ke portofolio dari halaman area — diverifikasi lewat outgoing link tiap area page):
- `bogor-kota/`: 2 link ke portofolio spesifik
- `sentul/`: 2 link ke portofolio spesifik
- `ciawi/`: 0 link ke portofolio spesifik
- `cibinong/`: 1 link ke portofolio spesifik
- `cijeruk/`: 1 link ke portofolio spesifik
- `puncak/`: 1 link ke portofolio spesifik
- `yasmin/`: 0 link ke portofolio spesifik
- `rancamaya/`: 0 link ke portofolio spesifik
- `bogor-raya/`: 0 link ke portofolio spesifik
- `karadenan/`: 0 link ke portofolio spesifik

**Kesenjangan struktural (P1/P2)**: 4 area (Bogor Kota, Sentul, Ciawi, Cibinong) mendapat 64-66 incoming link karena tampil di footer/nav global setiap halaman situs. 6 area lain (Cijeruk, Puncak, Yasmin, Rancamaya, Bogor Raya, Karadenan) hanya mendapat 3-5 incoming link (homepage + `/area-layanan/` hub + kadang 1 portofolio). Ini bukan error crawl — dikonfirmasi dengan memeriksa langsung daftar incoming source tiap halaman.

---

## J. Service Discovery

| Layanan | Sitemap | Canonical | Robots | Incoming | Klasifikasi |
|---|---|---|---|---|---|
| `jasa-perawatan-kolam-renang/` | Ya | self | index,follow | 24 | STRONG |
| `instalasi-filter-pompa/` | Ya | self | index,follow | 14 | STRONG |
| `renovasi-perbaikan-kolam/` | Ya | self | index,follow | 11 | STRONG |
| `perbaikan-kebocoran-kolam/` | Ya | self | index,follow | 10 | STRONG |
| `pembuatan-kolam-renang-baru/` | Ya | self | index,follow | 9 | STRONG |
| `waterproofing-kolam-renang/` | Ya | self | index,follow | 9 | STRONG |
| `perbaikan-sistem-sirkulasi-air/` | Ya | self | index,follow | 8 | STRONG |
| `kontrak-perawatan-bulanan/` | Ya | self | index,follow | 8 | STRONG |
| `penyeimbangan-kimia-air-kolam/` | Ya | self | index,follow | 8 | STRONG |
| `penggantian-pasir-filter/` | Ya | self | index,follow | 7 | STRONG |
| `penggantian-keramik-kolam/` | Ya | self | index,follow | 6 | STRONG |
| `vacuum-pembersihan-dasar-kolam/` | Ya | self | index,follow | 5 | GOOD |
| `perbaikan-lampu-kolam-renang/` | Ya | self | index,follow | 5 | GOOD |
| `perawatan-kolam-musim-hujan/` | Ya | self | index,follow | 5 | GOOD |
| `jasa-pengurasan-kolam-renang/` | Ya | self | index,follow | 4 | GOOD |
| `perawatan-kolam-vila-penginapan/` | Ya | self | index,follow | 4 | GOOD |
| `pembuatan-ruang-mesin-kolam/` | Ya | self | index,follow | 3 | GOOD |
| `perawatan-kolam-air-asin/` | Ya | self | index,follow | 3 | GOOD |
| `konsultasi-desain-kolam-renang/` | Ya | self | index,follow | 3 | GOOD |
| `jasa-darurat-kolam-bocor-mendadak/` | Ya | self | index,follow | 3 | GOOD |

- **Kuat** (11): incoming 6+, termasuk money page (24).
- **Cukup** (9): incoming 3-5.
- **Lemah/hampir orphan** (0): incoming 1-2 — .
- Tidak ada layanan yang benar-benar orphan (0 incoming).

---

## K. Article & Informational Discovery

| Artikel | Sitemap | Indexable | Incoming | Link ke Layanan/Area/Portfolio |
|---|---|---|---|---|
| `tips-merawat-kolam-renang-vila-jarang-dipakai/` | Ya | Ya | 7 | 8 link |
| `kesalahan-umum-pemilik-kolam-renang-rumahan/` | Ya | Ya | 6 | 8 link |
| `cara-mengatasi-air-kolam-berwarna-hijau/` | Ya | Ya | 6 | 8 link |
| `kenapa-air-kolam-keruh-saat-musim-hujan-bogor/` | Ya | Ya | 5 | 8 link |
| `berapa-kali-cek-ph-air-kolam-per-minggu/` | Ya | Ya | 4 | 8 link |
| `apa-itu-backwash-filter-kolam-renang/` | Ya | Ya | 4 | 8 link |
| `cara-menjaga-ph-air-kolam-tetap-ideal/` | Ya | Ya | 4 | 8 link |
| `tanda-pompa-kolam-renang-perlu-diganti/` | Ya | Ya | 3 | 8 link |
| `berapa-biaya-perawatan-kolam-renang-di-bogor/` | Ya | Ya | 1 | 8 link |
| `jadwal-ideal-perawatan-kolam-renang-rumah/` | Ya | Ya | 1 | 8 link |

Catatan (sesuai instruksi, TIDAK menilai kualitas tulisan — hanya discovery/crawlability/hubungan):
- Semua 10 artikel: HTTP 200, indexable, ada di sitemap.
- Semua 10 artikel menaut ke minimal 5 target layanan/area/portofolio berbeda — hubungan keluar (artikel → aset lain) sangat konsisten.
- Tidak ada artikel yang orphan (incoming terendah: 1).

---

## L. Redirect & Legacy URL Audit

- URL lama money page `/layanan/perawatan-pembersihan-rutin/` → diverifikasi langsung: **301 → `/layanan/jasa-perawatan-kolam-renang/` → 200**. Sesuai ekspektasi, rantai redirect pendek (1 hop), tidak ada redirect loop.
- Dicek seluruh 103 halaman yang berhasil di-crawl untuk internal link yang masih mengarah ke URL lama tersebut: **0 ditemukan** — tidak ada link internal yang masih memakai URL lama.
- URL statis lama lain yang diketahui dari `.htaccess` (`/area-sentul.html`, `/area-cibinong.html`, `/area-bogor-kota.html`, `/area-puncak.html`, `/index.html`) — tidak ditemukan referensi internal ke URL-URL ini dalam crawl (sudah bersih).
- Domain `www.jasakolamrenangbogor.com` → dikonfirmasi 301 ke non-www untuk halaman biasa; **robots.txt sendiri masih mendeklarasikan sitemap dengan domain www** (lihat §D) — 1 redirect ekstra yang bisa dihindari.
- Tidak ditemukan 404, 410, atau redirect chain/loop di seluruh 103 URL yang diperiksa (crawl + sitemap).

---

## M. Orphan Page Audit

Definisi dipakai: ada di sitemap, HTTP 200, **0 incoming internal link** dari halaman manapun yang ditemukan lewat crawl.

### ORPHAN_CRITICAL (4 — kategori penting: Portfolio)
- `https://jasakolamrenangbogor.com/portofolio/kolam-renang-villa-modern/`
- `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-area-belum-dipilih/`
- `https://jasakolamrenangbogor.com/portofolio/perawatan-kolam-renang-di-cijeruk/`
- `https://jasakolamrenangbogor.com/portofolio/renovasi-kolam-resort/`

### ORPHAN_NORMAL (36 — kategori Combo area×layanan, bukan halaman utama)
Seluruh 36 halaman combo area×layanan orphan (0 incoming). Dikelompokkan per area:
- `bogor-kota`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `bogor-raya`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `ciawi`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `cibinong`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `cijeruk`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `karadenan`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `rancamaya`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `sentul`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam
- `yasmin`: instalasi-filter-pompa, pembuatan-kolam-renang-baru, perawatan-pembersihan-rutin, renovasi-perbaikan-kolam

### SITEMAP_ONLY (URL yang HANYA bisa ditemukan lewat sitemap, tidak lewat link apapun di situs)
Sama dengan gabungan ORPHAN_CRITICAL + ORPHAN_NORMAL di atas — total **40 URL** (4 portofolio + 36 combo). Tidak ada money page, layanan utama, area utama, atau artikel yang orphan.

---

## N. Crawl Depth

Depth dihitung dari homepage (depth 0) mengikuti link internal yang benar-benar ditemukan (BFS).

| Depth | Jumlah URL | Keterangan |
|---|---|---|
| 0 | 1 | Homepage |
| 1 | 55 | Layanan, Area, Portfolio, Hub, Combo yang ter-link — hampir semua |
| 2 | 7 | Sebagian artikel (link antar-artikel) |
| N/A | 40 | Tidak terjangkau lewat link apapun (hanya di sitemap) — lihat §M |

- Tidak ada halaman penting (money page, layanan, area, portofolio, artikel) dengan depth lebih dari 2.
- 40 URL tidak punya depth terdefinisi karena tidak terjangkau lewat link apapun — ini persis sama dengan daftar orphan di §M, bukan masalah depth tapi masalah keterhubungan total.

---

## O. Prioritas Tindakan (P0-P3)

**P0 — menghambat crawling/indexability**
- Tidak ditemukan masalah P0 murni indexability (tidak ada noindex/blocked/broken pada halaman yang sudah ter-link). Namun 40 URL yang 100% orphan digolongkan P0 karena secara praktis tidak dapat ditemukan Google lewat crawling normal (hanya lewat sitemap) — ini nyaris seburuk noindex dari sisi discovery, meski secara teknis indexable.

**P1 — menghambat discovery halaman penting**
- 36 halaman combo area×layanan: 0 incoming link — sepenuhnya bergantung pada sitemap untuk ditemukan.
- 4 halaman portofolio orphan (termasuk 1 yang tampak seperti entri belum selesai: `perawatan-kolam-renang-di-area-belum-dipilih`).
- 6 dari 10 halaman area tidak menaut ke money page.

**P2 — memperlemah internal topical cluster**
- Tidak ada artikel yang menaut langsung ke money page.
- Tidak ada link balik dari layanan/area/portofolio ke artikel (hubungan artikel→aset lain kuat, tapi tidak ada arah sebaliknya).
- Kesenjangan incoming link antar-area (64-66 vs 3-5) — 6 area "kelas dua" dari sisi internal authority distribution.

**P3 — improvement SEO tambahan**
- Baris `Sitemap:` di robots.txt memakai domain www (menambah 1 hop redirect yang tidak perlu).
- Duplikat `/faq/` di sitemap.xml (2 entri identik).

---

## P. GSC_REQUIRED

Sesuai instruksi, laporan ini **tidak menyimpulkan** status index aktual di Google. Tanpa akses Google Search Console, kesimpulan yang valid dari crawl ini terbatas pada:
- **Technically crawlable**: Ya, untuk seluruh 103 URL yang diperiksa (tidak ada blocking, semua 200).
- **Internally discoverable**: Ya untuk 63 URL yang ter-link dari crawl; **Tidak** untuk 40 URL (36 combo + 4 portofolio) yang hanya ada di sitemap.
- **Sitemap discoverable**: Ya untuk seluruh 103 URL unik di sitemap.
- **Indexable (secara teknis)**: Ya untuk seluruh 103 URL yang diperiksa.
- **Potentially orphan**: Ya untuk 40 URL di atas.

`GSC_REQUIRED` — untuk mengetahui status index AKTUAL (sudah di-crawl Google, ada di index, mendapat impression/klik), dibutuhkan akses Google Search Console. Crawl ini tidak dan tidak bisa menggantikan data tersebut.

---

## Q. Rekomendasi Implementasi (untuk fase berikutnya — TIDAK dieksekusi sekarang)

Murni daftar potensi tindak lanjut berbasis temuan audit ini. Tidak ada satupun yang dieksekusi dalam fase ini.

1. **P0/P1**: tambahkan internal link ke 36 halaman combo area×layanan dari halaman area terkait (mis. area/sentul/ menaut ke area/sentul/instalasi-filter-pompa/, dst) dan/atau dari halaman layanan induk.
2. **P1**: investigasi 4 portofolio orphan — khususnya `perawatan-kolam-renang-di-area-belum-dipilih` yang namanya mengindikasikan draft/data tidak lengkap; tautkan minimal dari hub `/portofolio/` jika memang layak tampil, atau evaluasi apakah entri ini seharusnya ada.
3. **P1**: tambahkan link money page dari 6 halaman area yang belum menautkannya (Ciawi, Cijeruk, Yasmin, Rancamaya, Bogor Raya, Karadenan).
4. **P2**: tambahkan link balik dari beberapa artikel ke money page secara kontekstual.
5. **P2**: evaluasi footer/nav global untuk memasukkan lebih banyak/semua 10 area, bukan hanya 4, agar distribusi internal link lebih merata.
6. **P3**: perbaiki baris `Sitemap:` di robots.txt agar langsung memakai domain non-www.
7. **P3**: hilangkan duplikat entri `/faq/` di sitemap.xml.

---

## Tabel Ringkas Seluruh URL

| URL | Type | HTTP | Robots | Canonical | Sitemap | Incoming | Depth | Status |
|---|---|---|---|---|---|---|---|---|
| `/` | Homepage | 200 | index, follow | self | Ya | 125 | 0 | STRONG |
| `/layanan/jasa-perawatan-kolam-renang/` | Layanan | 200 | index, follow | self | Ya | 24 | 1 | STRONG |
| `/layanan/instalasi-filter-pompa/` | Layanan | 200 | index, follow | self | Ya | 14 | 1 | STRONG |
| `/layanan/renovasi-perbaikan-kolam/` | Layanan | 200 | index, follow | self | Ya | 11 | 1 | STRONG |
| `/layanan/perbaikan-kebocoran-kolam/` | Layanan | 200 | index, follow | self | Ya | 10 | 1 | STRONG |
| `/layanan/pembuatan-kolam-renang-baru/` | Layanan | 200 | index, follow | self | Ya | 9 | 1 | STRONG |
| `/layanan/waterproofing-kolam-renang/` | Layanan | 200 | index, follow | self | Ya | 9 | 1 | STRONG |
| `/layanan/perbaikan-sistem-sirkulasi-air/` | Layanan | 200 | index, follow | self | Ya | 8 | 1 | STRONG |
| `/layanan/kontrak-perawatan-bulanan/` | Layanan | 200 | index, follow | self | Ya | 8 | 1 | STRONG |
| `/layanan/penyeimbangan-kimia-air-kolam/` | Layanan | 200 | index, follow | self | Ya | 8 | 1 | STRONG |
| `/layanan/penggantian-pasir-filter/` | Layanan | 200 | index, follow | self | Ya | 7 | 1 | STRONG |
| `/layanan/penggantian-keramik-kolam/` | Layanan | 200 | index, follow | self | Ya | 6 | 1 | STRONG |
| `/layanan/vacuum-pembersihan-dasar-kolam/` | Layanan | 200 | index, follow | self | Ya | 5 | 1 | GOOD |
| `/layanan/perbaikan-lampu-kolam-renang/` | Layanan | 200 | index, follow | self | Ya | 5 | 1 | GOOD |
| `/layanan/perawatan-kolam-musim-hujan/` | Layanan | 200 | index, follow | self | Ya | 5 | 1 | GOOD |
| `/layanan/jasa-pengurasan-kolam-renang/` | Layanan | 200 | index, follow | self | Ya | 4 | 1 | GOOD |
| `/layanan/perawatan-kolam-vila-penginapan/` | Layanan | 200 | index, follow | self | Ya | 4 | 1 | GOOD |
| `/layanan/pembuatan-ruang-mesin-kolam/` | Layanan | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/layanan/perawatan-kolam-air-asin/` | Layanan | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/layanan/konsultasi-desain-kolam-renang/` | Layanan | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/layanan/jasa-darurat-kolam-bocor-mendadak/` | Layanan | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/area/bogor-kota/` | Area | 200 | index, follow | self | Ya | 66 | 1 | STRONG |
| `/area/sentul/` | Area | 200 | index, follow | self | Ya | 65 | 1 | STRONG |
| `/area/ciawi/` | Area | 200 | index, follow | self | Ya | 65 | 1 | STRONG |
| `/area/cibinong/` | Area | 200 | index, follow | self | Ya | 64 | 1 | STRONG |
| `/area/cijeruk/` | Area | 200 | index, follow | self | Ya | 5 | 1 | GOOD |
| `/area/puncak/` | Area | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/area/yasmin/` | Area | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/area/rancamaya/` | Area | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/area/bogor-raya/` | Area | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/area/karadenan/` | Area | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/portofolio/kolam-renang-villa-modern-di-sentul/` | Portfolio | 200 | index, follow | self | Ya | 21 | 1 | STRONG |
| `/portofolio/kolam-renang-keluarga/` | Portfolio | 200 | index, follow | self | Ya | 20 | 1 | STRONG |
| `/portofolio/renovasi-kolam-renang-di-puncak-bogor/` | Portfolio | 200 | index, follow | self | Ya | 19 | 1 | STRONG |
| `/portofolio/perawatan-kolam-rutin/` | Portfolio | 200 | index, follow | self | Ya | 8 | 1 | STRONG |
| `/portofolio/perbaikan-kebocoran-kolam/` | Portfolio | 200 | index, follow | self | Ya | 6 | 1 | STRONG |
| `/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/` | Portfolio | 200 | index, follow | self | Ya | 5 | 1 | GOOD |
| `/portofolio/instalasi-sistem-filtrasi/` | Portfolio | 200 | index, follow | self | Ya | 4 | 1 | GOOD |
| `/portofolio/perawatan-kolam-renang-di-cijeruk-bogor-13/` | Portfolio | 200 | index, follow | self | Ya | 3 | 1 | GOOD |
| `/portofolio/perawatan-kolam-renang-di-sentul-bogor/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/perawatan-kolam-renang-di-ciawi-bogor/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/perawatan-kolam-renang-di-bogor-kota/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/perawatan-kolam-renang-di-cibinong-bogor/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/perawatan-kolam-renang-di-yasmin-bogor/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/jasa-perawawatan-kolam-renang-rutin-di-rancamaya/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/perawatan-kolam-renang-di-bogor-raya/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/perawatan-kolam-renang-di-karadenan-bogor/` | Portfolio | 200 | index, follow | self | Ya | 2 | 1 | LOW |
| `/portofolio/kolam-renang-villa-modern/` | Portfolio | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/portofolio/perawatan-kolam-renang-di-area-belum-dipilih/` | Portfolio | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/portofolio/perawatan-kolam-renang-di-cijeruk/` | Portfolio | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/portofolio/renovasi-kolam-resort/` | Portfolio | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/artikel/tips-merawat-kolam-renang-vila-jarang-dipakai/` | Artikel | 200 | index, follow | self | Ya | 7 | 1 | STRONG |
| `/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/` | Artikel | 200 | index, follow | self | Ya | 6 | 2 | STRONG |
| `/artikel/cara-mengatasi-air-kolam-berwarna-hijau/` | Artikel | 200 | index, follow | self | Ya | 6 | 2 | STRONG |
| `/artikel/kenapa-air-kolam-keruh-saat-musim-hujan-bogor/` | Artikel | 200 | index, follow | self | Ya | 5 | 2 | GOOD |
| `/artikel/berapa-kali-cek-ph-air-kolam-per-minggu/` | Artikel | 200 | index, follow | self | Ya | 4 | 1 | GOOD |
| `/artikel/apa-itu-backwash-filter-kolam-renang/` | Artikel | 200 | index, follow | self | Ya | 4 | 1 | GOOD |
| `/artikel/cara-menjaga-ph-air-kolam-tetap-ideal/` | Artikel | 200 | index, follow | self | Ya | 4 | 2 | GOOD |
| `/artikel/tanda-pompa-kolam-renang-perlu-diganti/` | Artikel | 200 | index, follow | self | Ya | 3 | 2 | GOOD |
| `/artikel/berapa-biaya-perawatan-kolam-renang-di-bogor/` | Artikel | 200 | index, follow | self | Ya | 1 | 2 | LOW |
| `/artikel/jadwal-ideal-perawatan-kolam-renang-rumah/` | Artikel | 200 | index, follow | self | Ya | 1 | 2 | LOW |
| `/area/bogor-kota/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-kota/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-kota/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-kota/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-raya/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-raya/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-raya/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/bogor-raya/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/ciawi/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/ciawi/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/ciawi/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/ciawi/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cibinong/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cibinong/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cibinong/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cibinong/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cijeruk/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cijeruk/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cijeruk/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/cijeruk/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/karadenan/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/karadenan/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/karadenan/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/karadenan/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/rancamaya/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/rancamaya/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/rancamaya/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/rancamaya/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/sentul/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/sentul/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/sentul/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/sentul/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/yasmin/instalasi-filter-pompa/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/yasmin/pembuatan-kolam-renang-baru/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/yasmin/perawatan-pembersihan-rutin/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area/yasmin/renovasi-perbaikan-kolam/` | Combo (Area x Layanan) | 200 | index, follow | self | Ya | 0 | - | ORPHAN |
| `/area-layanan/` | Hub | 200 | index, follow | self | Ya | 76 | 1 | STRONG |
| `/layanan/` | Hub | 200 | index, follow | self | Ya | 65 | 1 | STRONG |
| `/portofolio/` | Hub | 200 | index, follow | self | Ya | 64 | 1 | STRONG |
| `/artikel/` | Hub | 200 | index, follow | self | Ya | 64 | 1 | STRONG |
| `/faq/` | Hub | 200 | index, follow | self | Ya | 63 | 1 | STRONG |
| `/kontak/` | Hub | 200 | index, follow | self | Ya | 63 | 1 | STRONG |