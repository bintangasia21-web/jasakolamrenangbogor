<?php
/**
 * Endpoint upload foto (AJAX, dipakai tab-tab lama di admin.html:
 * Testimonial, galeri Area, cover Layanan/Artikel/Halaman Utama).
 * Dilindungi Basic Auth yang sama dengan admin.html lewat .htaccess.
 * Logic penyimpanan sesungguhnya ada di inc/photo-helpers.php, dipakai
 * bersama dengan edit-portfolio.php (form PHP tradisional).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/photo-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$result = store_photo_upload($_FILES['photo'] ?? null);
echo json_encode($result);
