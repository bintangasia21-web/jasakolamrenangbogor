<?php
/**
 * Endpoint publik (tanpa login) yang dibaca oleh main.js/admin.js sebagai
 * sumber data live utama situs — menggantikan assets/js/data.json versi
 * file statis. Kontennya sama sekali tidak rahasia (info bisnis, area,
 * FAQ, portofolio memang untuk ditampilkan ke pengunjung).
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = get_db();

    $business = $pdo->query('SELECT * FROM business WHERE id = 1')->fetch();
    if (!$business) {
        $business = new stdClass();
    } else {
        unset($business['id']);
        $business['yearsExperience'] = (int) $business['yearsExperience'];
        $business['projectsDone'] = (int) $business['projectsDone'];
    }

    $areas = array_map(function ($r) {
        return [
            'name' => $r['name'],
            'link' => $r['link'],
            'desc' => $r['description'],
            'lat' => $r['lat'] !== null ? (float) $r['lat'] : null,
            'lng' => $r['lng'] !== null ? (float) $r['lng'] : null,
            'priority' => (bool) $r['priority']
        ];
    }, $pdo->query('SELECT * FROM areas ORDER BY sort_order, id')->fetchAll());

    $faq = array_map(function ($r) {
        return ['q' => $r['question'], 'a' => $r['answer']];
    }, $pdo->query('SELECT * FROM faq ORDER BY sort_order, id')->fetchAll());

    $portfolio = array_map(function ($r) {
        return [
            'title' => $r['title'],
            'area' => $r['area'],
            'desc' => $r['description'],
            'image' => $r['image'],
            'color1' => $r['color1'],
            'color2' => $r['color2']
        ];
    }, $pdo->query('SELECT * FROM portfolio ORDER BY sort_order, id')->fetchAll());

    echo json_encode([
        'business' => $business,
        'areas' => $areas,
        'faq' => $faq,
        'portfolio' => $portfolio
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
