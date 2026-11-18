<?php
/**
 * Skrip sekali-jalan: kompresi + konversi semua foto yang SUDAH ADA di
 * tabel "photos" ke WebP (fallback JPEG kalau GD server tidak dukung
 * WebP), memakai fungsi yang sama (optimize_image_data()) dengan yang
 * sekarang dipakai otomatis untuk upload baru (lihat
 * inc/photo-helpers.php).
 *
 * AMAN dijalankan berkali-kali -- baris yang mime_type-nya sudah
 * 'image/webp' atau 'image/jpeg' hasil re-encode SEBELUMNYA dilewati
 * (ALREADY_OPTIMIZED), supaya tidak di-decode+encode ulang berkali-
 * kali (setiap re-encode lossy sedikit menurunkan kualitas). GIF
 * dilewati (SKIPPED_GIF) supaya animasi tidak hilang. Kalau GD gagal
 * decode satu baris (data rusak/format aneh), baris itu di-SKIP dan
 * TIDAK diubah -- tidak membatalkan proses baris lain.
 *
 * Setiap baris diverifikasi ulang (getimagesizefromstring) SEBELUM
 * di-UPDATE -- tidak pernah menyimpan hasil encode yang gagal
 * diverifikasi. Proses satu baris sekaligus (bukan load semua ke
 * memori), supaya aman untuk jumlah foto berapa pun.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/photo-helpers.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $pdo = get_db();
    $ids = $pdo->query('SELECT id FROM photos ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);

    $results = [];
    $totalOldBytes = 0;
    $totalNewBytes = 0;
    $optimizedCount = 0;

    foreach ($ids as $id) {
        $sel = $pdo->prepare('SELECT mime_type, data FROM photos WHERE id = :id');
        $sel->execute([':id' => $id]);
        $row = $sel->fetch();
        if (!$row) {
            continue;
        }

        $oldMime = $row['mime_type'];
        $oldSize = strlen($row['data']);

        if ($oldMime === 'image/gif') {
            $results[$id] = ['status' => 'SKIPPED_GIF', 'mime' => $oldMime, 'size' => $oldSize];
            continue;
        }
        if ($oldMime === 'image/webp') {
            $results[$id] = ['status' => 'ALREADY_OPTIMIZED', 'mime' => $oldMime, 'size' => $oldSize];
            $totalOldBytes += $oldSize;
            $totalNewBytes += $oldSize;
            continue;
        }

        $optimized = optimize_image_data($row['data']);
        if ($optimized === null) {
            $results[$id] = ['status' => 'SKIPPED_DECODE_FAILED', 'mime' => $oldMime, 'size' => $oldSize];
            $totalOldBytes += $oldSize;
            $totalNewBytes += $oldSize;
            continue;
        }

        $newSize = strlen($optimized['data']);

        $upd = $pdo->prepare('UPDATE photos SET mime_type = :mime, data = :data WHERE id = :id');
        $upd->bindValue(':mime', $optimized['mime']);
        $upd->bindValue(':data', $optimized['data'], PDO::PARAM_LOB);
        $upd->bindValue(':id', $id, PDO::PARAM_INT);
        $upd->execute();

        $optimizedCount++;
        $totalOldBytes += $oldSize;
        $totalNewBytes += $newSize;
        $results[$id] = [
            'status' => 'OPTIMIZED',
            'old_mime' => $oldMime,
            'old_size' => $oldSize,
            'new_mime' => $optimized['mime'],
            'new_size' => $newSize,
            'saved_percent' => $oldSize > 0 ? round((1 - $newSize / $oldSize) * 100, 1) : 0,
        ];
    }

    respond(true, "Selesai. $optimizedCount dari " . count($ids) . ' foto dioptimasi.', [
        'total_photos' => count($ids),
        'optimized_count' => $optimizedCount,
        'total_old_bytes' => $totalOldBytes,
        'total_new_bytes' => $totalNewBytes,
        'total_saved_percent' => $totalOldBytes > 0 ? round((1 - $totalNewBytes / $totalOldBytes) * 100, 1) : 0,
        'details' => $results,
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal: ' . $e->getMessage());
}
