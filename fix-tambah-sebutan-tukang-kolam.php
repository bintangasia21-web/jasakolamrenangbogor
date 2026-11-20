<?php
/**
 * Skrip sekali-jalan: tambah 1 kalimat di artikel kontraktor kolam
 * renang yang menyebut istilah "tukang kolam renang" -- sinonim
 * kasual/lokal yang muncul di data GSC 28 hari ("tukang kolam renang
 * terdekat", posisi 12, dekat halaman 1) tapi belum pernah disebut
 * eksplisit di konten manapun.
 *
 * 1 UPDATE, idempoten (ALREADY_ADDED kalau sudah ada, SKIPPED kalau
 * needle tidak ditemukan persis -- tidak dipaksa diubah).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/artikel/kontraktor-kolam-renang-bogor-apa-yang-perlu-diperhatikan/';

try {
    $pdo = get_db();
    $sel = $pdo->prepare("SELECT id, content FROM pages WHERE url_path = :u AND type = 'article'");
    $sel->execute([':u' => $urlPath]);
    $row = $sel->fetch();

    if (!$row) {
        respond(false, 'Baris tidak ditemukan: ' . $urlPath);
    }

    $content = $row['content'];
    $marker = 'sering juga disebut "tukang kolam renang"';

    if (strpos($content, $marker) !== false) {
        respond(true, 'Tidak ada perubahan -- kalimat sudah ada sebelumnya.', ['status' => 'ALREADY_ADDED']);
    }

    $needle = 'tanpa pengalaman khusus menangani tekanan air dan kebutuhan teknis kolam renang.</p>';
    if (strpos($content, $needle) === false) {
        respond(false, 'SKIPPED -- anchor teks yang diharapkan tidak ditemukan di content saat ini (kemungkinan sudah berubah). Tidak ada perubahan dilakukan.', [
            'status' => 'SKIPPED',
            'backup_current_content' => $content,
        ]);
    }

    $replacement = 'tanpa pengalaman khusus menangani tekanan air dan kebutuhan teknis kolam renang. Istilah "kontraktor kolam renang" ini di masyarakat sering juga disebut "tukang kolam renang" -- sama-sama merujuk pada pihak yang mengerjakan pembuatan kolam secara profesional, bukan tukang bangunan serabutan.</p>';

    $newContent = str_replace($needle, $replacement, $content);

    $upd = $pdo->prepare("UPDATE pages SET content = :content WHERE id = :id AND url_path = :u AND type = 'article'");
    $upd->execute([':content' => $newContent, ':id' => $row['id'], ':u' => $urlPath]);

    respond(true, 'Sebutan "tukang kolam renang" berhasil ditambahkan.', [
        'status' => 'ADDED',
        'rows_affected' => $upd->rowCount(),
        'verify_marker_present' => strpos($newContent, $marker) !== false,
        'backup_old_content' => $content,
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
