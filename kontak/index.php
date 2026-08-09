<?php
/**
 * Halaman Kontak — file fisik (bukan lewat tabel "pages" /
 * page-router.php), pola sama seperti index.php, portofolio/index.php,
 * dan faq/index.php, supaya SELALU sinkron dengan info bisnis yang
 * dikelola admin (tab Info Bisnis) tanpa perlu "republish" manual.
 */
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/render-partials.php';

try {
    $pdo = get_db();
    $business = $pdo->query('SELECT * FROM business WHERE id = 1')->fetch();
    if (!$business) {
        http_response_code(500);
        exit('Konfigurasi bisnis belum ada.');
    }
    $areasRaw = $pdo->query('SELECT * FROM areas ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    http_response_code(500);
    exit('Terjadi kesalahan server.');
}

$areas = array_map(function ($r) {
    return ['name' => $r['name'], 'link' => $r['link']];
}, $areasRaw);

$s = get_page_sections('kontak');

$meta = [
    'title' => 'Kontak Kami',
    'meta_title' => 'Kontak | Jasa Kolam Renang Bogor',
    'meta_description' => 'Hubungi Jasa Kolam Renang Bogor untuk konsultasi gratis pembuatan, perawatan, atau renovasi kolam renang Anda — via WhatsApp, telepon, atau email.',
    'intro' => $s['lead'] ?? 'Konsultasikan kebutuhan kolam renang Anda, gratis tanpa biaya survei awal.',
    'url_path' => '/kontak/'
];

render_head($meta, $business);
render_header_nav($business);
render_local_business_ld($business, $areas);
render_breadcrumbs([['Beranda', '/'], ['Kontak', null]], $business);
?>

<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Kontak</span>
      <h1><?= h($s['h1'] ?? 'Hubungi Kami') ?></h1>
      <p class="lead"><?= h($meta['intro']) ?></p>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <?php render_contact_block($business); ?>
  </div>
</section>

<?php render_footer($business); ?>
