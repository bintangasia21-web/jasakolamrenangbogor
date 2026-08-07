<?php
/**
 * Halaman indeks FAQ — file fisik (bukan lewat tabel "pages" /
 * page-router.php), pola sama seperti index.php (root) dan
 * portofolio/index.php, supaya halaman ini SELALU sinkron dengan
 * tabel "faq" yang dikelola admin tanpa perlu "republish" manual.
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
    $faq = array_map(function ($r) {
        return ['q' => $r['question'], 'a' => $r['answer']];
    }, $pdo->query('SELECT * FROM faq ORDER BY sort_order, id')->fetchAll());
} catch (Exception $e) {
    http_response_code(500);
    exit('Terjadi kesalahan server.');
}

$areas = array_map(function ($r) {
    return ['name' => $r['name'], 'link' => $r['link']];
}, $areasRaw);

$meta = [
    'title' => 'FAQ - Pertanyaan yang Sering Diajukan',
    'meta_title' => 'FAQ Jasa Kolam Renang Bogor | Pertanyaan yang Sering Diajukan',
    'meta_description' => 'Jawaban atas pertanyaan yang sering diajukan seputar pembuatan, perawatan, dan renovasi kolam renang di Bogor.',
    'intro' => 'Belum menemukan jawaban? Hubungi kami langsung melalui WhatsApp.',
    'url_path' => '/faq/'
];

render_head($meta, $business);
render_header_nav($business);
render_local_business_ld($business, $areas);
render_breadcrumbs([['Beranda', '/'], ['FAQ', null]], $business);
?>

<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">FAQ</span>
      <h1>Pertanyaan yang Sering Diajukan</h1>
      <p class="lead"><?= h($meta['intro']) ?></p>
    </div>
  </div>
</section>

<?php
if (empty($faq)) {
    echo '<section><div class="container"><p class="portfolio-note">Belum ada FAQ. Tambahkan lewat panel admin (tab FAQ).</p></div></section>';
} else {
    render_faq_block($faq, $business, $meta['url_path']);
}
render_cta_band('Masih Ada Pertanyaan Lain?', 'Tim kami siap membantu menjawab langsung via WhatsApp.', $business);
render_footer($business);
