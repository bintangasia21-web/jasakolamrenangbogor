<?php
/**
 * Logic penyimpanan foto (validasi + simpan ke tabel "photos" sebagai
 * BLOB) yang dipakai bersama oleh upload-photo.php (endpoint AJAX untuk
 * tab-tab lama di admin.html) dan edit-portfolio.php (form PHP
 * tradisional, submit langsung tanpa AJAX).
 */

/**
 * Resize (maks lebar $maxWidth, proporsional) + re-encode ke WebP
 * (fallback JPEG kalau GD di server tidak mendukung imagewebp()).
 * Dipakai untuk foto baru yang diupload, dan untuk mengonversi ulang
 * foto lama lewat optimize-existing-photos.php.
 *
 * Mengembalikan null kalau GD tidak bisa decode data gambar (data
 * rusak/format tak dikenal) -- caller WAJIB fallback ke data asli,
 * jangan pernah menyimpan null sebagai pengganti foto yang valid.
 */
function optimize_image_data($data, $maxWidth = 1920, $quality = 82) {
    // Ekstensi GD bisa saja tidak terpasang di server (jarang, tapi
    // jangan diasumsikan) -- cek dulu sebelum memanggil fungsi GD
    // apa pun, supaya tidak memicu fatal "call to undefined function"
    // yang TIDAK tertangkap oleh try/catch(Exception) di caller.
    if (!function_exists('imagecreatefromstring')) {
        return null;
    }

    $image = @imagecreatefromstring($data);
    if ($image === false) {
        return null;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    if ($width > $maxWidth) {
        $resized = imagescale($image, $maxWidth);
        if ($resized !== false) {
            imagedestroy($image);
            $image = $resized;
            $width = imagesx($image);
            $height = imagesy($image);
        }
    }

    // PNG/GIF dengan transparansi tetap didukung (imagewebp & imagejpeg
    // sama-sama butuh background putih untuk JPEG, tapi WebP mendukung
    // alpha langsung) -- preserve alpha channel sebelum encode.
    imagesavealpha($image, true);

    ob_start();
    if (function_exists('imagewebp')) {
        imagewebp($image, null, $quality);
        $mime = 'image/webp';
    } else {
        // Fallback kalau GD di server tidak dikompilasi dengan dukungan
        // WebP -- flatten alpha ke putih dulu supaya tidak jadi hitam
        // di JPEG (yang tidak punya alpha channel).
        $flat = imagecreatetruecolor($width, $height);
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);
        $image = $flat;
        imagejpeg($image, null, $quality);
        $mime = 'image/jpeg';
    }
    $newData = ob_get_clean();
    imagedestroy($image);

    // Verifikasi hasil encode benar-benar gambar valid sebelum dipakai
    // -- kalau gagal, caller harus fallback ke data asli (jangan pernah
    // menyimpan hasil yang tidak terverifikasi).
    if ($newData === false || $newData === '' || @getimagesizefromstring($newData) === false) {
        return null;
    }

    return ['mime' => $mime, 'data' => $newData, 'width' => $width, 'height' => $height];
}

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

    // Kompresi + konversi WebP otomatis supaya foto baru tidak lagi
    // tersimpan mentah (foto HP sering >1MB, format PNG/JPEG tanpa
    // kompresi). GIF dilewati supaya animasi tidak hilang. Fallback ke
    // data asli kalau GD gagal decode (tidak memblokir upload).
    if ($mime !== 'image/gif') {
        $optimized = optimize_image_data($data);
        if ($optimized !== null) {
            $mime = $optimized['mime'];
            $data = $optimized['data'];
        }
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
