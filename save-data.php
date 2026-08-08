<?php
/**
 * Endpoint untuk menyimpan perubahan konten (info bisnis, area, FAQ,
 * portofolio) langsung ke database MySQL. Dilindungi Basic Auth lewat
 * .htaccess. Setiap section dikirim sebagai payload JSON lengkap
 * (menggantikan seluruh isi section tsb di database — pola yang sama
 * dengan versi file JSON sebelumnya, supaya kode admin.js tidak perlu
 * berubah banyak).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

$allowedSections = ['business', 'areas', 'faq', 'portfolio', 'testimonials', 'area_photos'];
$section = $_POST['section'] ?? '';
$payloadRaw = $_POST['payload'] ?? '';

if (!in_array($section, $allowedSections, true)) {
    respond(false, 'Bagian data tidak dikenali.');
}

$payload = json_decode($payloadRaw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    respond(false, 'Data yang dikirim tidak valid.');
}

// Cegah base64/data URI raksasa nyasar ke kolom URL gambar (harus pakai
// upload-photo.php, yang menghasilkan path file biasa, bukan data: URI).
if ($section === 'portfolio') {
    if (!is_array($payload)) respond(false, 'Data portofolio tidak valid.');
    foreach ($payload as $item) {
        if (isset($item['image']) && is_string($item['image']) && strlen($item['image']) > 500) {
            respond(false, 'Salah satu URL gambar terlalu panjang (kemungkinan base64). Gunakan tombol unggah foto, bukan tempel teks ke kolom URL.');
        }
    }
}
if (in_array($section, ['areas', 'faq', 'testimonials', 'area_photos'], true) && !is_array($payload)) {
    respond(false, 'Data tidak valid.');
}
if ($section === 'business' && !is_array($payload)) {
    respond(false, 'Data bisnis tidak valid.');
}

try {
    $pdo = get_db();
    $pdo->beginTransaction();

    if ($section === 'business') {
        $fields = ['name', 'legalName', 'tagline', 'description', 'phoneDisplay', 'phoneHref',
            'whatsapp', 'whatsappMessage', 'email', 'addressLine', 'city', 'region', 'postalCode',
            'country', 'hoursWeekday', 'hoursWeekend', 'mapsQuery', 'mapsUrl', 'priceRange',
            'yearsExperience', 'projectsDone', 'domain',
            'yearFounded', 'activeCustomers', 'employeeCount', 'monthlyRevenue'];
        // yearFounded & employeeCount boleh kosong (NULL) di skema, sisanya
        // teks bebas default string kosong.
        $nullableFields = ['yearFounded', 'employeeCount', 'monthlyRevenue'];
        $cols = implode(',', array_map(function ($f) { return "`$f`"; }, $fields));
        $vals = implode(',', array_map(function ($f) { return ":$f"; }, $fields));
        $updates = implode(',', array_map(function ($f) { return "`$f` = VALUES(`$f`)"; }, $fields));
        $stmt = $pdo->prepare("INSERT INTO business (id, $cols) VALUES (1, $vals) ON DUPLICATE KEY UPDATE $updates");
        $params = [];
        foreach ($fields as $f) {
            $v = $payload[$f] ?? '';
            if (in_array($f, $nullableFields, true) && ($v === '' || $v === null)) {
                $v = null;
            }
            $params[":$f"] = $v;
        }
        $stmt->execute($params);
    } elseif ($section === 'areas') {
        $pdo->exec('DELETE FROM areas');
        $stmt = $pdo->prepare('INSERT INTO areas (name, link, description, lat, lng, priority, sort_order) VALUES (:name, :link, :description, :lat, :lng, :priority, :sort_order)');
        $order = 0;
        foreach ($payload as $item) {
            $stmt->execute([
                ':name' => $item['name'] ?? '',
                ':link' => $item['link'] ?? '',
                ':description' => $item['desc'] ?? '',
                ':lat' => (isset($item['lat']) && $item['lat'] !== null && $item['lat'] !== '') ? $item['lat'] : null,
                ':lng' => (isset($item['lng']) && $item['lng'] !== null && $item['lng'] !== '') ? $item['lng'] : null,
                ':priority' => !empty($item['priority']) ? 1 : 0,
                ':sort_order' => $order++
            ]);
        }
    } elseif ($section === 'faq') {
        $pdo->exec('DELETE FROM faq');
        $stmt = $pdo->prepare('INSERT INTO faq (question, answer, sort_order) VALUES (:q, :a, :sort_order)');
        $order = 0;
        foreach ($payload as $item) {
            $stmt->execute([':q' => $item['q'] ?? '', ':a' => $item['a'] ?? '', ':sort_order' => $order++]);
        }
    } elseif ($section === 'portfolio') {
        $pdo->exec('DELETE FROM portfolio');
        $stmt = $pdo->prepare('INSERT INTO portfolio (title, area, description, image, color1, color2, sort_order) VALUES (:title, :area, :description, :image, :color1, :color2, :sort_order)');
        $order = 0;
        foreach ($payload as $item) {
            $stmt->execute([
                ':title' => $item['title'] ?? '',
                ':area' => $item['area'] ?? '',
                ':description' => $item['desc'] ?? '',
                ':image' => $item['image'] ?? null,
                ':color1' => $item['color1'] ?? null,
                ':color2' => $item['color2'] ?? null,
                ':sort_order' => $order++
            ]);
        }
    } elseif ($section === 'testimonials') {
        $pdo->exec('DELETE FROM testimonials');
        $stmt = $pdo->prepare('INSERT INTO testimonials (name, area, service, content, photo, testimonial_date, status, sort_order) VALUES (:name, :area, :service, :content, :photo, :testimonial_date, :status, :sort_order)');
        $order = 0;
        foreach ($payload as $item) {
            $status = ($item['status'] ?? '') === 'published' ? 'published' : 'draft';
            $date = trim($item['date'] ?? '');
            $stmt->execute([
                ':name' => $item['name'] ?? '',
                ':area' => $item['area'] ?? '',
                ':service' => $item['service'] ?? '',
                ':content' => $item['content'] ?? '',
                ':photo' => $item['photo'] ?? null,
                ':testimonial_date' => $date !== '' ? $date : null,
                ':status' => $status,
                ':sort_order' => $order++
            ]);
        }
    } elseif ($section === 'area_photos') {
        $pdo->exec('DELETE FROM area_photos');
        $stmt = $pdo->prepare('INSERT INTO area_photos (area_link, photo, caption, sort_order) VALUES (:area_link, :photo, :caption, :sort_order)');
        $order = 0;
        foreach ($payload as $item) {
            if (empty($item['photo'])) continue;
            $stmt->execute([
                ':area_link' => $item['areaLink'] ?? '',
                ':photo' => $item['photo'],
                ':caption' => $item['caption'] ?? '',
                ':sort_order' => $order++
            ]);
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(false, 'Gagal menyimpan ke database: ' . $e->getMessage());
}

respond(true, 'Perubahan berhasil disimpan dan langsung tampil di situs.');
