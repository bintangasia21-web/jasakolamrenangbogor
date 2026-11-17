<?php
/**
 * Skrip sekali-jalan: tambahkan link dari 2 artikel yang sudah live ke
 * 2 halaman layanan baru yang lebih relevan (sebelumnya artikel-artikel
 * ini hanya link ke /layanan/pembuatan-kolam-renang-baru/ secara
 * generik):
 * - /artikel/apakah-kolam-renang-perlu-pemanas-heater/
 *   -> tambah link ke /layanan/instalasi-pemanas-kolam-renang/
 * - /artikel/keamanan-kolam-renang-untuk-rumah-dengan-anak/
 *   -> tambah link ke /layanan/pemasangan-pagar-pengaman-kolam/
 *
 * 2 str_replace presisi terpisah, masing-masing idempoten (cek
 * ALREADY_LINKED/SKIPPED via strpos sebelum mengubah), sama seperti
 * fix-orphan-internal-links.php. Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $pdo = get_db();
    $results = [];

    // ============================================================
    // a) Artikel heater -> link ke /layanan/instalasi-pemanas-kolam-renang/
    // ============================================================
    $artUrl1 = '/artikel/apakah-kolam-renang-perlu-pemanas-heater/';
    $svcUrl1 = '/layanan/instalasi-pemanas-kolam-renang/';

    $sel1 = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'article'");
    $sel1->execute([':u' => $artUrl1]);
    $row1 = $sel1->fetch();

    if (!$row1) {
        $results[$artUrl1] = ['status' => 'SKIPPED', 'reason' => 'Baris artikel tidak ditemukan.'];
    } else {
        $old1 = $row1['content'];
        if (strpos($old1, $svcUrl1) !== false) {
            $results[$artUrl1] = ['status' => 'ALREADY_LINKED'];
        } else {
            $needle1 = 'supaya instalasinya terintegrasi dengan sistem kolam secara keseluruhan.</p><h2>Yang Bisa Anda Lakukan Sekarang</h2>';
            if (strpos($old1, $needle1) === false) {
                $results[$artUrl1] = ['status' => 'SKIPPED', 'reason' => 'anchor teks tidak ditemukan di content saat ini (kemungkinan sudah berubah) -- tidak diubah.', 'backup_current_content' => $old1];
            } else {
                $replacement1 = 'supaya instalasinya terintegrasi dengan sistem kolam secara keseluruhan. Kami juga menyediakan layanan <a href="/layanan/instalasi-pemanas-kolam-renang/" style="color:var(--blue-600);font-weight:600">instalasi pemanas kolam renang</a> secara terpisah untuk kolam yang sudah ada.</p><h2>Yang Bisa Anda Lakukan Sekarang</h2>';
                $new1 = str_replace($needle1, $replacement1, $old1);
                $upd1 = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'article'");
                $upd1->execute([':content' => $new1, ':id' => $row1['id'], ':u' => $artUrl1]);
                $results[$artUrl1] = [
                    'status' => 'ADDED',
                    'id' => $row1['id'],
                    'rows_affected' => $upd1->rowCount(),
                    'verify_present_in_new_content' => strpos($new1, $svcUrl1) !== false,
                    'backup_old_content' => $old1,
                ];
            }
        }
    }

    // ============================================================
    // b) Artikel keamanan anak -> link ke /layanan/pemasangan-pagar-pengaman-kolam/
    // ============================================================
    $artUrl2 = '/artikel/keamanan-kolam-renang-untuk-rumah-dengan-anak/';
    $svcUrl2 = '/layanan/pemasangan-pagar-pengaman-kolam/';

    $sel2 = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'article'");
    $sel2->execute([':u' => $artUrl2]);
    $row2 = $sel2->fetch();

    if (!$row2) {
        $results[$artUrl2] = ['status' => 'SKIPPED', 'reason' => 'Baris artikel tidak ditemukan.'];
    } else {
        $old2 = $row2['content'];
        if (strpos($old2, $svcUrl2) !== false) {
            $results[$artUrl2] = ['status' => 'ALREADY_LINKED'];
        } else {
            $needle2 = 'Contoh kolam keluarga yang kami kerjakan bisa dilihat di <a href="/portofolio/kolam-renang-keluarga/">Kolam Renang Keluarga</a>.</p><h2>Yang Bisa Anda Lakukan Sekarang</h2>';
            if (strpos($old2, $needle2) === false) {
                $results[$artUrl2] = ['status' => 'SKIPPED', 'reason' => 'anchor teks tidak ditemukan di content saat ini (kemungkinan sudah berubah) -- tidak diubah.', 'backup_current_content' => $old2];
            } else {
                $replacement2 = 'Contoh kolam keluarga yang kami kerjakan bisa dilihat di <a href="/portofolio/kolam-renang-keluarga/">Kolam Renang Keluarga</a>. Kami juga menyediakan layanan <a href="/layanan/pemasangan-pagar-pengaman-kolam/" style="color:var(--blue-600);font-weight:600">pemasangan pagar pengaman kolam</a> secara terpisah untuk kolam yang sudah berdiri.</p><h2>Yang Bisa Anda Lakukan Sekarang</h2>';
                $new2 = str_replace($needle2, $replacement2, $old2);
                $upd2 = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'article'");
                $upd2->execute([':content' => $new2, ':id' => $row2['id'], ':u' => $artUrl2]);
                $results[$artUrl2] = [
                    'status' => 'ADDED',
                    'id' => $row2['id'],
                    'rows_affected' => $upd2->rowCount(),
                    'verify_present_in_new_content' => strpos($new2, $svcUrl2) !== false,
                    'backup_old_content' => $old2,
                ];
            }
        }
    }

    respond(true, 'Selesai.', $results);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
