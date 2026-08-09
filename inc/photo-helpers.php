<?php
/**
 * Logic penyimpanan foto (validasi + simpan ke tabel "photos" sebagai
 * BLOB) yang dipakai bersama oleh upload-photo.php (endpoint AJAX untuk
 * tab-tab lama di admin.html) dan edit-portfolio.php (form PHP
 * tradisional, submit langsung tanpa AJAX).
 */

function store_photo_upload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tidak ada file yang diterima atau upload gagal.'];
    }

    $maxBytes = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB.'];
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes, true)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.'];
    }

    $data = file_get_contents($file['tmp_name']);
    if ($data === false) {
        return ['success' => false, 'message' => 'Gagal membaca file yang diunggah.'];
    }

    try {
        $pdo = get_db();
        $stmt = $pdo->prepare('INSERT INTO photos (mime_type, data) VALUES (:mime, :data)');
        $stmt->bindValue(':mime', $mime);
        $stmt->bindValue(':data', $data, PDO::PARAM_LOB);
        $stmt->execute();
        $id = $pdo->lastInsertId();
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal menyimpan foto ke database: ' . $e->getMessage()];
    }

    return ['success' => true, 'url' => 'photo.php?id=' . $id];
}
