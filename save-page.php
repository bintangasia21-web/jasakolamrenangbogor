<?php
/**
 * Simpan (buat baru atau perbarui) satu baris di tabel "pages". Dilindungi
 * Basic Auth. status=published berarti langsung live seketika, tanpa
 * perlu deploy — page-router.php membaca langsung dari database.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Metode tidak diizinkan.');
}

$payloadRaw = $_POST['payload'] ?? '';
$data = json_decode($payloadRaw, true);
if (!is_array($data)) {
    respond(false, 'Data tidak valid.');
}

$allowedTypes = ['area', 'service', 'combo', 'article', 'portfolio', 'page'];
if (empty($data['type']) || !in_array($data['type'], $allowedTypes, true)) {
    respond(false, 'Tipe halaman tidak valid.');
}
if (empty($data['url_path']) || empty($data['title'])) {
    respond(false, 'URL dan judul wajib diisi.');
}
$urlPath = '/' . trim($data['url_path'], '/') . '/';

$faqJson = null;
if (!empty($data['faq']) && is_array($data['faq'])) {
    $faqJson = json_encode($data['faq'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

$fields = [
    ':type' => $data['type'],
    ':tier' => $data['tier'] ?? null,
    ':url_path' => $urlPath,
    ':title' => $data['title'],
    ':meta_title' => $data['meta_title'] ?? null,
    ':meta_description' => $data['meta_description'] ?? null,
    ':target_keyword' => $data['target_keyword'] ?? null,
    ':h1' => $data['h1'] ?? null,
    ':area_ref' => $data['area_ref'] ?? null,
    ':service_ref' => $data['service_ref'] ?? null,
    ':intro' => $data['intro'] ?? null,
    ':content' => $data['content'] ?? null,
    ':faq_json' => $faqJson,
    ':status' => in_array($data['status'] ?? '', ['draft', 'published'], true) ? $data['status'] : 'draft'
];

try {
    $pdo = get_db();
    if (!empty($data['id'])) {
        $fields[':id'] = (int) $data['id'];
        $stmt = $pdo->prepare('UPDATE pages SET type=:type, tier=:tier, url_path=:url_path, title=:title, meta_title=:meta_title, meta_description=:meta_description, target_keyword=:target_keyword, h1=:h1, area_ref=:area_ref, service_ref=:service_ref, intro=:intro, content=:content, faq_json=:faq_json, status=:status WHERE id=:id');
        $stmt->execute($fields);
        $id = $data['id'];
    } else {
        // Tanpa id: upsert berdasar url_path (bisa jadi baris sudah ada dari
        // import-pages.php sebagai draft) supaya tidak gagal karena UNIQUE.
        $stmt = $pdo->prepare(
            'INSERT INTO pages (type, tier, url_path, title, meta_title, meta_description, target_keyword, h1, area_ref, service_ref, intro, content, faq_json, status)
             VALUES (:type,:tier,:url_path,:title,:meta_title,:meta_description,:target_keyword,:h1,:area_ref,:service_ref,:intro,:content,:faq_json,:status)
             ON DUPLICATE KEY UPDATE type=VALUES(type), tier=VALUES(tier), title=VALUES(title), meta_title=VALUES(meta_title),
               meta_description=VALUES(meta_description), target_keyword=VALUES(target_keyword), h1=VALUES(h1),
               area_ref=VALUES(area_ref), service_ref=VALUES(service_ref), intro=VALUES(intro), content=VALUES(content),
               faq_json=VALUES(faq_json), status=VALUES(status)'
        );
        $stmt->execute($fields);
        $idRow = $pdo->prepare('SELECT id FROM pages WHERE url_path = :url_path');
        $idRow->execute([':url_path' => $urlPath]);
        $id = $idRow->fetchColumn();
    }
    respond(true, 'Halaman berhasil disimpan.', ['id' => $id, 'url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal menyimpan: ' . $e->getMessage());
}
