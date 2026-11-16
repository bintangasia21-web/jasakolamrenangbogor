<?php
/**
 * Skrip sekali-jalan: tambahkan 1 internal link dari
 * /layanan/pembuatan-kolam-renang-baru/ ke artikel baru
 * /artikel/berapa-biaya-membuat-kolam-renang-baru-di-bogor/ (lihat
 * seed-artikel-biaya-pembuatan-kolam.php). Artikel sudah otomatis
 * punya incoming link dari hub /artikel/ dan homepage "artikel
 * terbaru", tapi service page yang paling relevan (pembuatan) belum
 * menyebutnya sama sekali -- ditambahkan di sini secara kontekstual
 * (kalimat soal survei & biaya, bagian "Tahapan Pekerjaan").
 *
 * Content live di-fetch & dicek strpos() dulu sebelum str_replace
 * (bukan full overwrite) -- konten service page ini sudah berubah
 * beberapa kali sejak Phase 3 (fix-service-pembuatan-kolam-baru.php),
 * jadi tidak aman mengandalkan snapshot lama. Kalau anchor teks yang
 * diharapkan tidak ditemukan (sudah berubah lagi), script SKIP, tidak
 * memaksa perubahan. Aman dijalankan berkali-kali (idempoten).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$svcUrl = '/layanan/pembuatan-kolam-renang-baru/';
$artUrl = '/artikel/berapa-biaya-membuat-kolam-renang-baru-di-bogor/';

try {
    $pdo = get_db();
    $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'service'");
    $sel->execute([':u' => $svcUrl]);
    $row = $sel->fetch();

    if (!$row) {
        respond(false, 'Baris service tidak ditemukan: ' . $svcUrl);
    }

    $oldContent = $row['content'];

    if (strpos($oldContent, $artUrl) !== false) {
        respond(true, 'Tidak ada perubahan -- link sudah ada sebelumnya.', [
            'status' => 'ALREADY_LINKED',
            'url_path' => $svcUrl,
        ]);
    }

    $needle = 'Survei lokasi di tahap awal kami berikan tanpa biaya, sehingga Anda mendapat gambaran biaya dan waktu pengerjaan yang akurat sebelum memutuskan untuk melanjutkan.</p>';
    if (strpos($oldContent, $needle) === false) {
        respond(false, 'SKIPPED -- anchor teks yang diharapkan tidak ditemukan di content saat ini (kemungkinan sudah berubah sejak script ini dibuat). Tidak ada perubahan dilakukan.', [
            'status' => 'SKIPPED',
            'backup_current_content' => $oldContent,
        ]);
    }

    $replacement = 'Survei lokasi di tahap awal kami berikan tanpa biaya, sehingga Anda mendapat gambaran biaya dan waktu pengerjaan yang akurat sebelum memutuskan untuk melanjutkan. Untuk gambaran lebih lengkap soal faktor yang memengaruhi besar-kecilnya biaya, baca <a href="' . $artUrl . '" style="color:var(--blue-600);font-weight:600">artikel kami soal biaya pembuatan kolam renang baru</a>.</p>';

    $newContent = str_replace($needle, $replacement, $oldContent);

    $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'service'");
    $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $svcUrl]);

    respond(true, 'Link ke artikel berhasil ditambahkan.', [
        'status' => 'ADDED',
        'url_path' => $svcUrl,
        'id' => $row['id'],
        'rows_affected' => $upd->rowCount(),
        'verify_present_in_new_content' => strpos($newContent, $artUrl) !== false,
        'backup_old_content' => $oldContent,
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
