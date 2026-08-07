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
    $domain = $business ? rtrim($business['domain'], '/') : 'https://www.jasakolamrenangbogor.com';
    $pages = $pdo->query("SELECT url_path, updated_at FROM pages WHERE status = 'published' ORDER BY url_path")->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    exit;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= htmlspecialchars($domain, ENT_QUOTES) ?>/</loc>
    <changefreq>monthly</changefreq>
    <priority>1.0</priority>
  </url>
<?php foreach ($pages as $p): ?>
  <url>
    <loc><?= htmlspecialchars($domain . $p['url_path'], ENT_QUOTES) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
