<?php
/**
 * Migrasi satu-kali: salin data dari assets/js/data.json (atau
 * data.default.json bila belum ada) ke database MySQL. Dilindungi
 * Basic Auth. Menolak jalan kalau tabel "business" sudah berisi data,
 * supaya tidak sengaja menimpa/menduplikasi data yang sudah live.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$sourceFile = __DIR__ . '/assets/js/data.json';
if (!file_exists($sourceFile)) {
    $sourceFile = __DIR__ . '/assets/js/data.default.json';
}
if (!file_exists($sourceFile)) {
    respond(false, 'Tidak ada sumber data (data.json / data.default.json) untuk dimigrasikan.');
}

$data = json_decode(file_get_contents($sourceFile), true);
if (!is_array($data)) {
    respond(false, 'Sumber data (' . basename($sourceFile) . ') tidak valid.');
}

try {
    $pdo = get_db();

    $existing = (int) $pdo->query('SELECT COUNT(*) c FROM business')->fetch()['c'];
    if ($existing > 0) {
        respond(false, 'Migrasi dilewati: tabel business sudah berisi data. Kosongkan tabel manual lewat phpMyAdmin dulu kalau memang ingin migrasi ulang.');
    }

    $pdo->beginTransaction();

    $b = $data['business'] ?? [];
    $fields = ['name', 'legalName', 'tagline', 'description', 'phoneDisplay', 'phoneHref',
        'whatsapp', 'whatsappMessage', 'email', 'addressLine', 'city', 'region', 'postalCode',
        'country', 'hoursWeekday', 'hoursWeekend', 'mapsQuery', 'mapsUrl', 'priceRange',
        'yearsExperience', 'projectsDone', 'domain'];
    $cols = implode(',', array_map(function ($f) { return "`$f`"; }, $fields));
    $vals = implode(',', array_map(function ($f) { return ":$f"; }, $fields));
    $stmt = $pdo->prepare("INSERT INTO business (id, $cols) VALUES (1, $vals)");
    $params = [];
    foreach ($fields as $f) {
        $params[":$f"] = $b[$f] ?? '';
    }
    $stmt->execute($params);

    $stmt = $pdo->prepare('INSERT INTO areas (name, link, description, lat, lng, priority, sort_order) VALUES (:name, :link, :description, :lat, :lng, :priority, :sort_order)');
    $order = 0;
    foreach (($data['areas'] ?? []) as $item) {
        $stmt->execute([
            ':name' => $item['name'] ?? '', ':link' => $item['link'] ?? '',
            ':description' => $item['desc'] ?? '',
            ':lat' => $item['lat'] ?? null, ':lng' => $item['lng'] ?? null,
            ':priority' => !empty($item['priority']) ? 1 : 0, ':sort_order' => $order++
        ]);
    }

    $stmt = $pdo->prepare('INSERT INTO faq (question, answer, sort_order) VALUES (:q, :a, :sort_order)');
    $order = 0;
    foreach (($data['faq'] ?? []) as $item) {
        $stmt->execute([':q' => $item['q'] ?? '', ':a' => $item['a'] ?? '', ':sort_order' => $order++]);
    }

    $stmt = $pdo->prepare('INSERT INTO portfolio (title, area, description, image, color1, color2, sort_order) VALUES (:title, :area, :description, :image, :color1, :color2, :sort_order)');
    $order = 0;
    foreach (($data['portfolio'] ?? []) as $item) {
        $stmt->execute([
            ':title' => $item['title'] ?? '', ':area' => $item['area'] ?? '',
            ':description' => $item['desc'] ?? '', ':image' => $item['image'] ?? null,
            ':color1' => $item['color1'] ?? null, ':color2' => $item['color2'] ?? null,
            ':sort_order' => $order++
        ]);
    }

    $pdo->commit();
    respond(true, 'Migrasi berhasil dari ' . basename($sourceFile) . '.', [
        'areas' => count($data['areas'] ?? []),
        'faq' => count($data['faq'] ?? []),
        'portfolio' => count($data['portfolio'] ?? [])
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(false, 'Migrasi gagal: ' . $e->getMessage());
}
