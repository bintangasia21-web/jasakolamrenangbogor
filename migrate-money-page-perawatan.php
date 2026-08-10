<?php
/**
 * Skrip sekali-jalan (Phase 7, 2026-08-11): MIGRASI URL + optimasi
 * money page "Jasa Perawatan Kolam Renang".
 *
 *   /layanan/perawatan-pembersihan-rutin/  -->  /layanan/jasa-perawatan-kolam-renang/
 *
 * Menggantikan fix-service-perawatan-rutin.php sebelumnya (kalau belum
 * sempat dijalankan, TIDAK APA -- skrip ini melakukan UPDATE penuh
 * sendiri, tidak bergantung pada state dari skrip itu). Setelah skrip
 * ini berhasil, fix-service-perawatan-rutin.php sudah tidak relevan
 * lagi (URL lamanya sudah tidak ada) dan sebaiknya dihapus terpisah.
 *
 * STEP 1 -- Migrasi baris utama (pages, type='service'):
 *   ubah url_path lama -> baru, sekaligus tulis ulang title/h1/
 *   meta_title/meta_description/content/faq_json yang sudah dioptimasi
 *   sebagai money page. SEBELUM ubah, dicek dulu apakah url_path baru
 *   sudah dipakai baris LAIN (guard wajib sesuai instruksi) -- kalau
 *   ya, skrip berhenti tanpa mengubah apa pun.
 *
 * STEP 2 & 3 -- Perbaiki 2 internal link yang masih hardcode ke URL
 *   lama (ditemukan lewat crawl penuh seluruh situs -- lihat laporan
 *   Phase 7): halaman /area/bogor-kota/ dan /layanan/ (hub). Dilakukan
 *   dengan str_replace() terhadap NILAI CONTENT YANG SEDANG TERSIMPAN
 *   di database saat skrip dijalankan (bukan menimpa dengan HTML yang
 *   ditulis ulang manual) -- supaya tidak berisiko salah ketik ulang
 *   ratusan baris HTML hub /layanan/ yang berisi 20 kartu layanan.
 *
 * Homepage (index.php) TIDAK perlu skrip ini -- section layanan di
 * homepage membaca url_path langsung dari query database (dinamis),
 * jadi otomatis ikut berubah begitu STEP 1 selesai. Sitemap.xml juga
 * dinamis (baca url_path live dari DB), otomatis ikut berubah.
 *
 * Dijalankan lewat browser (Basic Auth). AMAN dijalankan berkali-kali
 * SETELAH migrasi pertama berhasil (STEP 1 idempoten berdasarkan
 * url_path baru; STEP 2/3 idempoten karena str_replace tidak melakukan
 * apa-apa kalau string lama sudah tidak ada).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$oldUrl = '/layanan/perawatan-pembersihan-rutin/';
$newUrl = '/layanan/jasa-perawatan-kolam-renang/';

$h1 = 'Jasa Perawatan Kolam Renang';
$title = 'Jasa Perawatan Kolam Renang';
$metaTitle = 'Jasa Perawatan Kolam Renang Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Jasa perawatan kolam renang rutin di Bogor: pembersihan, cek kualitas air, dan perawatan filter berkala. Untuk rumah, villa, dan hotel. Konsultasi gratis.';

$content = '<h2>Jasa Perawatan Kolam Renang di Bogor</h2>'
    . '<p style="color:var(--gray-600)">Perawatan rutin adalah kunci menjaga kolam renang tetap sehat dan nyaman digunakan dalam jangka panjang. Tanpa perawatan berkala, air kolam mudah keruh, berlumut, dan sistem filtrasi cepat rusak — terutama di Bogor yang punya curah hujan tinggi sepanjang tahun.</p>'
    . '<p style="color:var(--gray-600)">Kami menyediakan jasa perawatan kolam renang dengan paket mingguan maupun bulanan yang bisa disesuaikan dengan kebutuhan rumah tinggal, villa, maupun properti komersial di wilayah Bogor dan sekitarnya.</p>'

    . '<h2 style="margin-top:32px">Apa Saja yang Dilakukan dalam Perawatan Kolam Renang</h2>'
    . '<ol style="color:var(--gray-600);padding-left:20px;margin:0"><li>Pengecekan kejernihan &amp; kualitas air</li><li>Vacuum dasar &amp; dinding kolam</li><li>Backwash/pembersihan filter</li><li>Penyeimbangan kimia air (pH &amp; klorin)</li><li>Pengecekan pompa &amp; sistem sirkulasi</li><li>Laporan kondisi kolam ke pemilik</li></ol>'

    . '<h2 style="margin-top:32px">Masalah yang Dapat Dicegah dengan Perawatan Rutin</h2>'
    . '<p style="color:var(--gray-600)">Kolam yang jarang dirawat cenderung mengalami <a href="/artikel/cara-mengatasi-air-kolam-berwarna-hijau/" style="color:var(--blue-600);font-weight:600">air berubah keruh atau hijau</a> akibat pertumbuhan alga, gangguan pada <a href="/layanan/penyeimbangan-kimia-air-kolam/" style="color:var(--blue-600);font-weight:600">keseimbangan kimia air</a>, hingga masalah kecil seperti <a href="/layanan/perbaikan-kebocoran-kolam/" style="color:var(--blue-600);font-weight:600">kebocoran</a> yang lebih mudah terlewat kalau tidak ada pengecekan berkala. Perawatan rutin membantu menangkap tanda-tanda ini lebih awal, sebelum jadi masalah yang lebih besar dan mahal — beberapa kesalahan umum seputar ini juga kami bahas di artikel <a href="/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/" style="color:var(--blue-600);font-weight:600">Kesalahan Umum Pemilik Kolam Renang Rumahan</a>.</p>'

    . '<h2 style="margin-top:32px">Perawatan Kolam untuk Rumah, Villa, Hotel, dan Properti Lain</h2>'
    . '<p style="color:var(--gray-600)">Kebutuhan perawatan berbeda-beda tergantung jenis properti — rumah tinggal umumnya cukup kunjungan mingguan, sementara hotel atau guesthouse dengan operasional harian butuh jadwal yang lebih ketat dan fleksibel. Untuk kebutuhan jangka panjang dengan jadwal tetap, kami juga menyediakan <a href="/layanan/kontrak-perawatan-bulanan/" style="color:var(--blue-600);font-weight:600">Kontrak Perawatan Bulanan</a>.</p>'

    . '<h2 style="margin-top:32px">Portofolio Perawatan Kolam Renang</h2>'
    . '<p style="color:var(--gray-600)">Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut beberapa contoh pekerjaan perawatan kolam renang yang pernah kami tangani.</p>'
    . '<div class="portfolio-grid">'
    . '<a class="portfolio-card" href="/portofolio/perawatan-kolam-rutin/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=16" alt="Perawatan kolam renang rutin untuk hotel di Bogor Kota" width="738" height="414" loading="lazy"></div><div class="portfolio-body"><span class="tag">Bogor Kota</span><h3>Perawatan Kolam Rutin</h3><p style="color:var(--gray-600)">Program perawatan bulanan untuk kolam renang hotel di kawasan Bogor Kota, mencakup pengecekan kualitas air, pembersihan filter, dan penyeimbangan kimia secara berkala.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>'
    . '<a class="portfolio-card" href="/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=12" alt="Perawatan kolam renang di Cijeruk, Bogor" width="426" height="292" loading="lazy"></div><div class="portfolio-body"><span class="tag">Cijeruk</span><h3>Perawatan Kolam Renang di Cijeruk, Bogor</h3><p style="color:var(--gray-600)">Proyek perawatan kolam renang di Cijeruk, Bogor, Jawa Barat, dilakukan dengan pemeriksaan kondisi air, kebersihan kolam, sirkulasi, pompa, dan filter.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>'
    . '</div>'

    . '<h2 style="margin-top:32px">Area Layanan</h2>'
    . '<p style="color:var(--gray-600)">Jasa perawatan kolam renang kami mencakup seluruh wilayah Bogor dan sekitarnya, termasuk <a href="/area/bogor-kota/" style="color:var(--blue-600);font-weight:600">Bogor Kota</a> dan <a href="/area/cijeruk/" style="color:var(--blue-600);font-weight:600">Cijeruk</a>. Untuk daftar lengkap area yang kami layani, lihat <a href="/area-layanan/" style="color:var(--blue-600);font-weight:600">halaman Area Layanan</a>.</p>'

    . '<h2 style="margin-top:32px">Konsultasi Perawatan Kolam Renang</h2>'
    . '<p style="color:var(--gray-600)">Ingin kolam Anda selalu jernih dan siap pakai tanpa perlu memikirkan jadwal perawatannya sendiri? Hubungi kami untuk konsultasi paket perawatan yang sesuai dengan jenis properti dan frekuensi pemakaian kolam Anda.</p>'
    . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';

$faq = [
    ['q' => 'Seberapa sering kolam renang perlu dirawat?', 'a' => 'Untuk penggunaan rumah tinggal umumnya cukup 1x seminggu, sementara kolam komersial atau yang sering dipakai bisa membutuhkan kunjungan lebih rapat.'],
    ['q' => 'Apakah tersedia paket kontrak bulanan?', 'a' => 'Ya, silakan lihat halaman Kontrak Perawatan Bulanan kami atau hubungi tim untuk penawaran sesuai kebutuhan.'],
    ['q' => 'Sudah berapa lama menangani jasa perawatan kolam renang?', 'a' => 'Perawatan kolam renang adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

try {
    $pdo = get_db();

    // ---- STEP 1: migrasi baris utama ----
    $before = $pdo->prepare('SELECT id, url_path, title, h1, meta_title, meta_description, content, faq_json FROM pages WHERE url_path = :u AND type = \'service\'');
    $before->execute([':u' => $oldUrl]);
    $beforeRow = $before->fetch();

    if (!$beforeRow) {
        // Mungkin sudah pernah dimigrasi sebelumnya -- cek apakah baris baru sudah ada & sesuai.
        $already = $pdo->prepare('SELECT id FROM pages WHERE url_path = :u AND type = \'service\'');
        $already->execute([':u' => $newUrl]);
        if ($already->fetch()) {
            respond(true, 'URL lama sudah tidak ada dan URL baru sudah ada -- migrasi tampaknya sudah pernah dijalankan sebelumnya. Tidak ada perubahan.');
        }
        respond(false, 'Baris dengan url_path lama tidak ditemukan -- tidak ada yang diubah.');
    }

    // Guard wajib: pastikan URL target belum dipakai baris LAIN.
    $clash = $pdo->prepare('SELECT id FROM pages WHERE url_path = :u AND id != :id');
    $clash->execute([':u' => $newUrl, ':id' => $beforeRow['id']]);
    if ($clash->fetch()) {
        respond(false, 'STOP: url_path target sudah dipakai oleh baris lain. Migrasi dibatalkan, tidak ada yang diubah.');
    }

    $stmt = $pdo->prepare(
        'UPDATE pages SET url_path = :new_url, title = :title, h1 = :h1, meta_title = :meta_title, meta_description = :meta_description, content = :content, faq_json = :faq_json
         WHERE id = :id AND url_path = :old_url AND type = \'service\''
    );
    $stmt->execute([
        ':new_url' => $newUrl,
        ':title' => $title,
        ':h1' => $h1,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':content' => $content,
        ':faq_json' => json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ':id' => $beforeRow['id'],
        ':old_url' => $oldUrl,
    ]);
    $step1Rows = $stmt->rowCount();

    // Verifikasi akhir: tidak ada dua baris untuk layanan yang sama.
    $dupCheck = $pdo->prepare('SELECT COUNT(*) c FROM pages WHERE url_path IN (:old_url, :new_url)');
    $dupCheck->execute([':old_url' => $oldUrl, ':new_url' => $newUrl]);
    $dupCount = (int) $dupCheck->fetch()['c'];

    // ---- STEP 2: perbaiki link di /area/bogor-kota/ ----
    $step2 = ['found' => false, 'updated' => false];
    $areaStmt = $pdo->prepare('SELECT id, content FROM pages WHERE url_path = :u AND type = \'area\'');
    $areaStmt->execute([':u' => '/area/bogor-kota/']);
    $areaRow = $areaStmt->fetch();
    if ($areaRow && strpos($areaRow['content'], $oldUrl) !== false) {
        $step2['found'] = true;
        $newAreaContent = str_replace($oldUrl, $newUrl, $areaRow['content']);
        $updAreaStmt = $pdo->prepare('UPDATE pages SET content = :c WHERE id = :id');
        $updAreaStmt->execute([':c' => $newAreaContent, ':id' => $areaRow['id']]);
        $step2['updated'] = true;
    }

    // ---- STEP 3: perbaiki link di /layanan/ (hub) ----
    $step3 = ['found' => false, 'updated' => false];
    $hubStmt = $pdo->prepare('SELECT id, content FROM pages WHERE url_path = :u');
    $hubStmt->execute([':u' => '/layanan/']);
    $hubRow = $hubStmt->fetch();
    if ($hubRow && strpos($hubRow['content'], $oldUrl) !== false) {
        $step3['found'] = true;
        $newHubContent = str_replace($oldUrl, $newUrl, $hubRow['content']);
        $updHubStmt = $pdo->prepare('UPDATE pages SET content = :c WHERE id = :id');
        $updHubStmt->execute([':c' => $newHubContent, ':id' => $hubRow['id']]);
        $step3['updated'] = true;
    }

    respond(true, 'Migrasi money page selesai.', [
        'page_id' => $beforeRow['id'],
        'old_url' => $oldUrl,
        'new_url' => $newUrl,
        'step1_rows_affected' => $step1Rows,
        'duplicate_url_check' => $dupCount === 1 ? 'OK (tidak ada duplikat)' : "PERINGATAN: ditemukan $dupCount baris cocok, seharusnya 1",
        'step2_area_bogor_kota' => $step2,
        'step3_layanan_hub' => $step3,
        'before' => [
            'title' => $beforeRow['title'],
            'h1' => $beforeRow['h1'],
            'meta_title' => $beforeRow['meta_title'],
            'meta_description' => $beforeRow['meta_description'],
        ],
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal migrasi: ' . $e->getMessage());
}
