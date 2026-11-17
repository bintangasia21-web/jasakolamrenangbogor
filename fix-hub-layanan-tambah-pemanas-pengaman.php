<?php
/**
 * Skrip sekali-jalan: tambahkan 2 card baru ("Instalasi Pemanas Kolam
 * Renang" dan "Pemasangan Pagar Pengaman Kolam") ke hub /layanan/.
 * Hub ini (type='page', url_path='/layanan/') isinya HTML statis di
 * kolom content, BUKAN query dinamis -- jadi 2 layanan baru yang
 * ditambahkan lewat seed-layanan-pemanas-kolam.php dan
 * seed-layanan-pagar-pengaman-kolam.php tidak otomatis muncul di sini
 * tanpa script ini.
 *
 * Content di-fetch & dicek strpos() dulu sebelum str_replace (bukan
 * full overwrite), sama seperti fix-orphan-internal-links.php. Aman
 * dijalankan berkali-kali (idempoten).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$hubUrl = '/layanan/';
$url1 = '/layanan/instalasi-pemanas-kolam-renang/';
$url2 = '/layanan/pemasangan-pagar-pengaman-kolam/';

try {
    $pdo = get_db();
    $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'page'");
    $sel->execute([':u' => $hubUrl]);
    $row = $sel->fetch();

    if (!$row) {
        respond(false, 'Baris hub tidak ditemukan: ' . $hubUrl);
    }

    $oldContent = $row['content'];

    if (strpos($oldContent, $url1) !== false && strpos($oldContent, $url2) !== false) {
        respond(true, 'Tidak ada perubahan -- kedua link sudah ada sebelumnya.', [
            'status' => 'ALREADY_LINKED',
            'url_path' => $hubUrl,
        ]);
    }

    $needle = '<div class="area-card"><a href="/layanan/jasa-darurat-kolam-bocor-mendadak/" style="display:block"><h3>Jasa Darurat Kolam Bocor Mendadak</h3><p style="color:var(--gray-600);font-size:.88rem">Layanan tanggap cepat untuk kondisi darurat kolam renang bocor mendadak, mencegah kerusakan lebih lanjut ke st...</p></a></div></div>';

    if (strpos($oldContent, $needle) === false) {
        respond(false, 'SKIPPED -- anchor teks yang diharapkan tidak ditemukan di content saat ini (kemungkinan sudah berubah sejak script ini dibuat). Tidak ada perubahan dilakukan.', [
            'status' => 'SKIPPED',
            'backup_current_content' => $oldContent,
        ]);
    }

    $replacement = '<div class="area-card"><a href="/layanan/jasa-darurat-kolam-bocor-mendadak/" style="display:block"><h3>Jasa Darurat Kolam Bocor Mendadak</h3><p style="color:var(--gray-600);font-size:.88rem">Layanan tanggap cepat untuk kondisi darurat kolam renang bocor mendadak, mencegah kerusakan lebih lanjut ke st...</p></a></div><div class="area-card"><a href="/layanan/instalasi-pemanas-kolam-renang/" style="display:block"><h3>Instalasi Pemanas Kolam Renang</h3><p style="color:var(--gray-600);font-size:.88rem">Pemasangan sistem pemanas (heater) kolam renang agar air tetap nyaman digunakan pagi, malam, atau saat musim hujan di Bogor.</p></a></div><div class="area-card"><a href="/layanan/pemasangan-pagar-pengaman-kolam/" style="display:block"><h3>Pemasangan Pagar Pengaman Kolam</h3><p style="color:var(--gray-600);font-size:.88rem">Pemasangan pagar dan pengaman akses kolam renang untuk mengurangi risiko bagi anak kecil di rumah dengan kolam pribadi.</p></a></div></div>';

    $newContent = str_replace($needle, $replacement, $oldContent);

    $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'page'");
    $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $hubUrl]);

    respond(true, '2 card layanan baru berhasil ditambahkan ke hub /layanan/.', [
        'status' => 'ADDED',
        'url_path' => $hubUrl,
        'id' => $row['id'],
        'rows_affected' => $upd->rowCount(),
        'verify_url1_present' => strpos($newContent, $url1) !== false,
        'verify_url2_present' => strpos($newContent, $url2) !== false,
        'backup_old_content' => $oldContent,
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
