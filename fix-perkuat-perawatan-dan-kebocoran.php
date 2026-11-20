<?php
/**
 * Skrip sekali-jalan: memperkuat 2 halaman komersial yang menurut data
 * GSC (7 hari terakhir, dilaporkan admin) sudah dekat halaman 1:
 * - "jasa perawatan kolam renang bogor" -> posisi 8 -> /layanan/jasa-perawatan-kolam-renang/
 * - "jasa perbaikan kolam renang bocor" -> posisi 6 -> /layanan/perbaikan-kebocoran-kolam/
 *
 * Plus 1 perbaikan link balik di artikel pendukung yang relevan
 * (artikel "Cara Mendeteksi Titik Kebocoran" belum link ke halaman
 * layanan perbaikan kebocoran yang spesifik, hanya ke renovasi umum).
 *
 * 4 UPDATE independen pada 3 baris berbeda, masing-masing idempoten
 * (ALREADY_ADDED/SKIPPED kalau needle tidak ketemu atau sudah ada).
 * Content di-fetch & dicek strpos()/needle dulu sebelum str_replace,
 * BUKAN overwrite penuh. FAQ lewat kolom faq_json (dirender dinamis
 * oleh render_faq_block(), tidak perlu sentuh HTML statis).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function add_faq_items($pdo, $urlPath, $type, array $newItems) {
    $sel = $pdo->prepare('SELECT id, faq_json FROM pages WHERE url_path = :u AND type = :t');
    $sel->execute([':u' => $urlPath, ':t' => $type]);
    $row = $sel->fetch();
    if (!$row) {
        return ['status' => 'SKIPPED', 'reason' => 'baris tidak ditemukan: ' . $urlPath];
    }
    $faq = json_decode($row['faq_json'], true);
    if (!is_array($faq)) {
        $faq = [];
    }
    $existing = array_map(function ($f) { return $f['q']; }, $faq);
    $added = [];
    foreach ($newItems as $item) {
        if (in_array($item['q'], $existing, true)) {
            continue;
        }
        $faq[] = $item;
        $added[] = $item['q'];
    }
    $newFaqJson = json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $upd = $pdo->prepare('UPDATE pages SET faq_json = :f WHERE id = :id AND url_path = :u AND type = :t');
    $upd->execute([':f' => $newFaqJson, ':id' => $row['id'], ':u' => $urlPath, ':t' => $type]);
    return [
        'status' => empty($added) ? 'ALREADY_ADDED' : 'ADDED',
        'ditambahkan' => $added,
        'total_faq_setelah' => count($faq),
        'rows_affected' => $upd->rowCount(),
    ];
}

function apply_content_patch($pdo, $urlPath, $type, $needle, $replacement, $markerCheck) {
    $sel = $pdo->prepare('SELECT id, content FROM pages WHERE url_path = :u AND type = :t');
    $sel->execute([':u' => $urlPath, ':t' => $type]);
    $row = $sel->fetch();
    if (!$row) {
        return ['status' => 'SKIPPED', 'reason' => 'baris tidak ditemukan: ' . $urlPath];
    }
    $content = $row['content'];
    $backup = $content;
    if (strpos($content, $markerCheck) !== false) {
        return ['status' => 'ALREADY_ADDED', 'backup_content' => $backup];
    }
    if (strpos($content, $needle) === false) {
        return ['status' => 'SKIPPED', 'reason' => 'needle tidak ditemukan persis -- kemungkinan content sudah berubah, tidak dipaksa diubah', 'backup_content' => $backup];
    }
    $newContent = str_replace($needle, $replacement, $content);
    $upd = $pdo->prepare('UPDATE pages SET content = :c WHERE id = :id AND url_path = :u AND type = :t');
    $upd->execute([':c' => $newContent, ':id' => $row['id'], ':u' => $urlPath, ':t' => $type]);
    return [
        'status' => 'ADDED',
        'rows_affected' => $upd->rowCount(),
        'verify_marker_present' => strpos($newContent, $markerCheck) !== false,
        'backup_content' => $backup,
    ];
}

try {
    $pdo = get_db();
    $report = [];

    // ===== 1. jasa-perawatan-kolam-renang: link artikel biaya =====
    $needle1 = 'Kami menyediakan jasa perawatan kolam renang dengan paket mingguan maupun bulanan yang bisa disesuaikan dengan kebutuhan rumah tinggal, villa, maupun properti komersial di wilayah Bogor dan sekitarnya.</p>';
    $replacement1 = $needle1 . '<p style="color:var(--gray-600)">Soal biaya, kami tidak mencantumkan angka pasti di halaman ini karena setiap kolam berbeda kebutuhannya -- baca faktor-faktor yang menentukan biaya di <a href="/artikel/berapa-biaya-perawatan-kolam-renang-di-bogor/" style="color:var(--blue-600);font-weight:600">artikel biaya perawatan kolam renang</a>, atau hubungi kami langsung untuk estimasi.</p>';
    $report['jasa_perawatan_link_biaya'] = apply_content_patch($pdo, '/layanan/jasa-perawatan-kolam-renang/', 'service', $needle1, $replacement1, '/artikel/berapa-biaya-perawatan-kolam-renang-di-bogor/');

    // ===== 2. jasa-perawatan-kolam-renang: foto tambahan (id=20, Sentul) =====
    $needle2 = '<a class="portfolio-card" href="/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=12" alt="Perawatan kolam renang di Cijeruk, Bogor" width="426" height="292" loading="lazy"></div><div class="portfolio-body"><span class="tag">Cijeruk</span><h3>Perawatan Kolam Renang di Cijeruk, Bogor</h3><p style="color:var(--gray-600)">Proyek perawatan kolam renang di Cijeruk, Bogor, Jawa Barat, dilakukan dengan pemeriksaan kondisi air, kebersihan kolam, sirkulasi, pompa, dan filter.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a></div>';
    $newCard = '<a class="portfolio-card" href="/portofolio/perawatan-kolam-renang-di-sentul-bogor/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=20" alt="Perawatan kolam renang di Sentul, Bogor" width="427" height="299" loading="lazy"></div><div class="portfolio-body"><span class="tag">Sentul</span><h3>Perawatan Kolam Renang di Sentul, Bogor</h3><p style="color:var(--gray-600)">Program perawatan rutin untuk kolam renang villa di kawasan Sentul, mencakup pengecekan kualitas air, kebersihan kolam, dan sistem filtrasi.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a></div>';
    $replacement2 = str_replace('</div></a></div>', '</div></a>' . $newCard, $needle2);
    $report['jasa_perawatan_foto_sentul'] = apply_content_patch($pdo, '/layanan/jasa-perawatan-kolam-renang/', 'service', $needle2, $replacement2, '/portofolio/perawatan-kolam-renang-di-sentul-bogor/');

    // ===== 3. jasa-perawatan-kolam-renang: 3 FAQ baru =====
    $report['jasa_perawatan_faq'] = add_faq_items($pdo, '/layanan/jasa-perawatan-kolam-renang/', 'service', [
        ['q' => 'Berapa biaya perawatan kolam renang rutin?', 'a' => 'Biaya tergantung ukuran kolam dan frekuensi kunjungan -- kami jelaskan detail faktornya lewat artikel biaya perawatan kami, atau hubungi kami langsung untuk estimasi.'],
        ['q' => 'Apakah bahan kimia yang dipakai aman untuk kulit dan mata?', 'a' => 'Ya, dosis bahan kimia kami sesuaikan dengan standar aman kolam renang, termasuk menjaga pH tetap stabil supaya nyaman untuk kulit dan mata perenang.'],
        ['q' => 'Apakah bisa request jadwal perawatan di luar mingguan/bulanan?', 'a' => 'Bisa, jadwal kunjungan kami sesuaikan dengan kebutuhan dan pola pemakaian kolam Anda.'],
    ]);

    // ===== 4. perbaikan-kebocoran-kolam: link ke artikel deteksi kebocoran =====
    $needle4 = 'Kebocoran kolam renang sering tidak langsung terlihat jelas. Tanda paling umum adalah permukaan air yang terus turun melebihi penguapan wajar, muncul area basah atau lembap di sekitar kolam, atau tagihan air yang naik tanpa sebab jelas.</p>';
    $replacement4 = 'Kebocoran kolam renang sering tidak langsung terlihat jelas. Tanda paling umum adalah permukaan air yang terus turun melebihi penguapan wajar, muncul area basah atau lembap di sekitar kolam, atau tagihan air yang naik tanpa sebab jelas. Kami juga menulis panduan lengkap <a href="/artikel/cara-mendeteksi-titik-kebocoran-kolam-renang/" style="color:var(--blue-600);font-weight:600">cara mendeteksi titik kebocoran kolam renang</a> sebelum memanggil teknisi.</p>';
    $report['perbaikan_kebocoran_link_artikel'] = apply_content_patch($pdo, '/layanan/perbaikan-kebocoran-kolam/', 'service', $needle4, $replacement4, '/artikel/cara-mendeteksi-titik-kebocoran-kolam-renang/');

    // ===== 5. perbaikan-kebocoran-kolam: 3 FAQ baru =====
    $report['perbaikan_kebocoran_faq'] = add_faq_items($pdo, '/layanan/perbaikan-kebocoran-kolam/', 'service', [
        ['q' => 'Berapa biaya perbaikan kebocoran kolam renang?', 'a' => 'Biaya tergantung tingkat keparahan dan lokasi titik kebocoran -- kami berikan estimasi pasti setelah pemeriksaan langsung di lokasi.'],
        ['q' => 'Apakah ada garansi untuk perbaikan kebocoran?', 'a' => 'Ya, pekerjaan perbaikan kebocoran kami sertai garansi -- detail garansi akan disampaikan saat penawaran.'],
        ['q' => 'Apakah kebocoran kecil bisa jadi masalah besar kalau dibiarkan?', 'a' => 'Bisa. Kebocoran kecil yang dibiarkan berisiko merembes ke struktur bangunan sekitar dan membuat perbaikan jadi lebih mahal.'],
    ]);

    // ===== 6. artikel deteksi-kebocoran: link ke halaman layanan spesifik =====
    $needle6 = 'langkah selanjutnya adalah <a href="/layanan/renovasi-perbaikan-kolam/">renovasi dan perbaikan kolam</a> untuk menemukan dan menutup titik rembesannya secara tuntas';
    $replacement6 = 'langkah selanjutnya adalah <a href="/layanan/perbaikan-kebocoran-kolam/">perbaikan kebocoran kolam</a> lewat <a href="/layanan/renovasi-perbaikan-kolam/">renovasi dan perbaikan kolam</a> untuk menemukan dan menutup titik rembesannya secara tuntas';
    $report['artikel_deteksi_link_layanan'] = apply_content_patch($pdo, '/artikel/cara-mendeteksi-titik-kebocoran-kolam-renang/', 'article', $needle6, $replacement6, '/layanan/perbaikan-kebocoran-kolam/');

    respond(true, 'Selesai memperkuat halaman jasa-perawatan-kolam-renang & perbaikan-kebocoran-kolam.', $report);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
