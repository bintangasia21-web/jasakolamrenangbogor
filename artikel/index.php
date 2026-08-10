<?php
/**
 * Halaman indeks Artikel — file fisik (bukan lewat tabel "pages" /
 * page-router.php), persis seperti /portofolio/, /faq/, /kontak/,
 * supaya selalu sinkron dengan artikel yang dikelola lewat tab admin
 * "Artikel" tanpa perlu "republish" manual.
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

// Artikel dibungkus try/catch terpisah: kolom cover_image atau tabel
// pages mungkin belum sepenuhnya termigrasi -- kalau gagal, halaman
// tetap tampil dengan status kosong alih-alih error total.
try {
    $articles = $pdo->query("SELECT * FROM pages WHERE type = 'article' AND status = 'published' ORDER BY sort_order, id")->fetchAll();
} catch (Exception $e) {
    $articles = [];
}

$areas = array_map(function ($r) {
    return ['name' => $r['name'], 'link' => $r['link']];
}, $areasRaw);

$meta = [
    'title' => 'Artikel & Panduan Kolam Renang',
    'meta_title' => 'Artikel Kolam Renang | Jasa Kolam Renang Bogor',
    'meta_description' => 'Kumpulan panduan seputar perawatan, biaya, dan perbaikan kolam renang di Bogor — ditulis oleh tim berpengalaman.',
    'intro' => 'Tips praktis seputar perawatan, biaya, dan perbaikan kolam renang di Bogor, ditulis berdasarkan pengalaman lapangan kami.',
    'url_path' => '/artikel/'
];

render_head($meta, $business);
render_header_nav($business);
render_local_business_ld($business, $areas);
render_breadcrumbs([['Beranda', '/'], ['Artikel', null]], $business);

// CollectionPage + ItemList: daftar artikel yang dipublikasikan supaya
// mesin pencari memahami halaman ini sebagai indeks/koleksi artikel,
// bukan satu artikel tunggal (beda @type dengan halaman detail artikel
// yang pakai "Article", lihat inc/templates/article.php).
$canonical = rtrim($business['domain'], '/') . $meta['url_path'];
$collectionLd = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $meta['meta_title'],
    'description' => $meta['meta_description'],
    'url' => $canonical,
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => array_values(array_map(function ($item, $i) use ($business) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => rtrim($business['domain'], '/') . $item['url_path'],
                'name' => $item['title']
            ];
        }, $articles, array_keys($articles)))
    ]
];
?>
<script type="application/ld+json"><?= json_encode($collectionLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="hero">
  <div class="container">
    <div style="max-width:720px">
      <span class="hero-badge">Panduan Kolam Renang</span>
      <h1><?= h($meta['title']) ?></h1>
      <p class="lead"><?= h($meta['intro']) ?></p>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <?php if (!empty($articles)): ?>
    <div class="portfolio-grid">
      <?php foreach ($articles as $item): ?>
      <a class="portfolio-card" href="<?= h($item['url_path']) ?>" style="display:block;color:inherit">
        <div class="portfolio-thumb">
          <?php if (!empty($item['cover_image'])): ?>
          <img src="/<?= h(ltrim($item['cover_image'], '/')) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
          <?php else: ?>
          <?= placeholder_svg($item['title'], '#1478c8', '#00b8d9') ?>
          <?php endif; ?>
        </div>
        <div class="portfolio-body">
          <h3><?= h($item['title']) ?></h3>
          <p><?= h(short_desc($item['intro'], 30)) ?></p>
          <span class="portfolio-link">Baca Selengkapnya <span class="arrow">&rarr;</span></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <h4 style="margin:0">Artikel Segera Hadir</h4>
      <p>Kami sedang menyiapkan kumpulan panduan kolam renang yang bermanfaat.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
render_cta_band('Tidak Menemukan Jawaban yang Anda Cari?', 'Chat langsung dengan tim kami, kami bantu jawab sesuai kondisi kolam Anda.', $business);
render_footer($business);
