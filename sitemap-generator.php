<?php
/**
 * Sitemap dinamis — dibaca live dari database (bukan file statis) supaya
 * selalu akurat dan tidak bisa "hilang" akibat proses deploy. Diarahkan
 * dari /sitemap.xml lewat .htaccess.
 */
header('Content-Type: application/xml; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = get_db();
    $business = $pdo->query('SELECT domain FROM business WHERE id = 1')->fetch();
    $domain = $business ? rtrim($business['domain'], '/') : 'https://jasakolamrenangbogor.com';
    $pages = $pdo->query("SELECT url_path, updated_at FROM pages WHERE status = 'published' ORDER BY url_path")->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    exit;
}

// Halaman fisik (bukan lewat tabel "pages") yang selalu live-sync dari
// data lain (portfolio/faq/business) — harus disebut manual di sini
// karena query di atas tidak bisa "melihat" file fisik ini.
$staticDynamicPages = ['/portofolio/', '/faq/', '/kontak/'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= htmlspecialchars($domain, ENT_QUOTES) ?>/</loc>
    <changefreq>monthly</changefreq>
    <priority>1.0</priority>
  </url>
<?php foreach ($staticDynamicPages as $path): ?>
  <url>
    <loc><?= htmlspecialchars($domain . $path, ENT_QUOTES) ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>
<?php endforeach; ?>
<?php foreach ($pages as $p): ?>
  <url>
    <loc><?= htmlspecialchars($domain . $p['url_path'], ENT_QUOTES) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
