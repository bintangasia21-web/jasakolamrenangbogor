<?php
/**
 * Dispatcher utama untuk 300 halaman SEO/GEO. Dipanggil oleh .htaccess
 * untuk semua URL yang bukan file/folder fisik. Mencari baris yang
 * cocok di tabel "pages" (hanya status=published yang tampil ke
 * publik) lalu merender lewat template sesuai "type".
 */
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/render-partials.php';

$path = isset($_GET['path']) ? $_GET['path'] : '';
$path = '/' . trim($path, '/') . '/';
if ($path === '//') {
    $path = '/';
}

try {
    $pdo = get_db();
    $business = $pdo->query('SELECT * FROM business WHERE id = 1')->fetch();
    if (!$business) {
        http_response_code(500);
        exit('Konfigurasi bisnis belum ada.');
    }
    $areas = $pdo->query('SELECT name FROM areas ORDER BY sort_order, id')->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM pages WHERE url_path = :p AND status = "published" LIMIT 1');
    $stmt->execute([':p' => $path]);
    $page = $stmt->fetch();
} catch (Exception $e) {
    http_response_code(500);
    exit('Terjadi kesalahan server.');
}

if (!$page) {
    http_response_code(404);
    render_head(['title' => 'Halaman Tidak Ditemukan', 'meta_title' => '404 - Halaman Tidak Ditemukan', 'meta_description' => 'Halaman yang Anda cari tidak ditemukan.', 'intro' => '', 'url_path' => $path], $business);
    render_header_nav($business);
    ?>
    <section class="hero">
      <div class="container">
        <div style="max-width:600px">
          <h1>Halaman Tidak Ditemukan</h1>
          <p class="lead">Maaf, halaman yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
          <a href="/" class="btn btn-white">Kembali ke Beranda</a>
        </div>
      </div>
    </section>
    <?php
    render_footer($business);
    exit;
}

$page['faq'] = [];
if (!empty($page['faq_json'])) {
    $decoded = json_decode($page['faq_json'], true);
    if (is_array($decoded)) {
        $page['faq'] = $decoded;
    }
}

$templateFile = __DIR__ . '/inc/templates/' . $page['type'] . '.php';
if (!file_exists($templateFile)) {
    http_response_code(500);
    exit('Template untuk tipe halaman ini belum tersedia.');
}

render_head($page, $business);
render_header_nav($business);
render_local_business_ld($business, $areas);
require $templateFile;
render_footer($business);
