<?php
/**
 * Potongan markup bersama (head/nav/footer/JSON-LD dasar/breadcrumb)
 * dipakai oleh semua template di inc/templates/*.php lewat page-router.php.
 * Server-side penuh (bukan render lewat JS) supaya ramah SEO/crawler.
 */

function h($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function placeholder_initials($title) {
    $words = preg_split('/\s+/', trim($title ?: 'Kolam Renang'));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        if ($w !== '') $initials .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    return $initials ?: 'KR';
}

function placeholder_svg($title, $color1, $color2) {
    return '<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' . h($title) . '">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0%" stop-color="' . h($color1) . '"/><stop offset="100%" stop-color="' . h($color2) . '"/>'
        . '</linearGradient></defs>'
        . '<rect width="400" height="300" fill="url(#g)"/>'
        . '<path d="M0 230 Q 50 210 100 230 T 200 230 T 300 230 T 400 230 V300 H0 Z" fill="rgba(255,255,255,0.15)"/>'
        . '<path d="M0 250 Q 50 232 100 250 T 200 250 T 300 250 T 400 250 V300 H0 Z" fill="rgba(255,255,255,0.22)"/>'
        . '<circle cx="200" cy="120" r="46" fill="rgba(255,255,255,0.18)"/>'
        . '<text x="200" y="132" font-family="Arial, sans-serif" font-size="34" font-weight="700" fill="#ffffff" text-anchor="middle">' . h(placeholder_initials($title)) . '</text>'
        . '</svg>';
}

function render_head($meta, $business) {
    $title = $meta['meta_title'] ?: $meta['title'];
    $desc = $meta['meta_description'] ?: $meta['intro'];
    $canonical = rtrim($business['domain'], '/') . $meta['url_path'];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta name="robots" content="index, follow">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php
}

function render_header_nav($business) {
    ?>
<header class="site-header">
  <div class="container nav">
    <a href="/" class="brand">
      <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"><path d="M2 17c1.5 1.2 3 1.2 4.5 0s3-1.2 4.5 0 3 1.2 4.5 0 3-1.2 4.5 0"/><path d="M12 13V4l3 2"/></svg></span>
      <span><?= h($business['name']) ?></span>
    </a>
    <nav class="nav-links">
      <a href="/layanan/">Layanan</a>
      <a href="/area-layanan/">Area Layanan</a>
      <a href="/portofolio/">Portofolio</a>
      <a href="/">FAQ</a>
      <a href="/">Kontak</a>
    </nav>
    <div class="nav-cta">
      <a href="tel:<?= h($business['phoneHref']) ?>" class="nav-phone"><?= h($business['phoneDisplay']) ?></a>
      <a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1s-.6.8-.7.9-.3.1-.5 0a6.6 6.6 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.4l.4-.4.2-.4v-.3c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.4a.9.9 0 0 0-.6.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.8 10.8 0 0 0 4.1 3.6c.6.2 1 .4 1.4.5a3.3 3.3 0 0 0 1.5.1 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c-.1-.1-.2-.2-.4-.3Z"/></svg>
        Chat WhatsApp
      </a>
      <button class="nav-toggle" aria-label="Buka menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
    </div>
  </div>
</header>
    <?php
}

function render_breadcrumbs($items, $business) {
    // $items = [[label, url_or_null], ...] — item terakhir tanpa link (halaman aktif)
    $ldItems = [];
    $i = 1;
    foreach ($items as $item) {
        $ldItems[] = [
            '@type' => 'ListItem',
            'position' => $i++,
            'name' => $item[0],
            'item' => $item[1] ? rtrim($business['domain'], '/') . $item[1] : null
        ];
    }
    ?>
<div class="breadcrumb">
  <?php foreach ($items as $idx => $item): ?>
    <?php if ($item[1]): ?><a href="<?= h($item[1]) ?>"><?= h($item[0]) ?></a><?php else: ?><?= h($item[0]) ?><?php endif; ?>
    <?php if ($idx < count($items) - 1): ?><span>/</span><?php endif; ?>
  <?php endforeach; ?>
</div>
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $ldItems], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php
}

function render_local_business_ld($business, $areas = []) {
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $business['name'],
        'description' => $business['description'],
        'url' => rtrim($business['domain'], '/') . '/',
        'telephone' => $business['phoneHref'],
        'email' => $business['email'],
        'priceRange' => $business['priceRange'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $business['addressLine'],
            'addressLocality' => $business['city'],
            'addressRegion' => $business['region'],
            'postalCode' => $business['postalCode'],
            'addressCountry' => $business['country']
        ],
        'areaServed' => array_map(function ($a) { return $a['name']; }, $areas)
    ];
    echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function render_faq_block($faqItems, $business, $urlPath) {
    if (empty($faqItems)) return;
    ?>
<section>
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">FAQ</span>
      <h2>Pertanyaan Seputar Halaman Ini</h2>
    </div>
    <div class="faq-list">
      <?php foreach ($faqItems as $i => $item): ?>
      <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= h($item['q']) ?></summary>
        <p><?= h($item['a']) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
    <?php
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(function ($item) {
            return ['@type' => 'Question', 'name' => $item['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']]];
        }, $faqItems)
    ];
    echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

function render_cta_band($title, $subtitle, $business) {
    ?>
<section class="section-alt">
  <div class="container cta-band">
    <h2><?= h($title) ?></h2>
    <p><?= h($subtitle) ?></p>
    <div class="hero-actions">
      <a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="btn btn-white">Chat WhatsApp Sekarang</a>
    </div>
  </div>
</section>
    <?php
}

function render_footer($business) {
    $priorityAreas = [
        ['Sentul', '/area/sentul/'], ['Ciawi', '/area/ciawi/'], ['Bogor Kota', '/area/bogor-kota/'],
        ['Cibinong', '/area/cibinong/']
    ];
    ?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"><path d="M2 17c1.5 1.2 3 1.2 4.5 0s3-1.2 4.5 0 3 1.2 4.5 0 3-1.2 4.5 0"/><path d="M12 13V4l3 2"/></svg></span>
          <span><?= h($business['name']) ?></span>
        </div>
        <p style="max-width:340px"><?= h($business['description']) ?></p>
      </div>
      <div>
        <h4>Navigasi</h4>
        <a href="/layanan/">Layanan</a>
        <a href="/area-layanan/">Area Layanan</a>
        <a href="/portofolio/">Portofolio</a>
        <a href="/">FAQ &amp; Kontak</a>
      </div>
      <div>
        <h4>Area Layanan</h4>
        <?php foreach ($priorityAreas as $a): ?>
        <a href="<?= h($a[1]) ?>"><?= h($a[0]) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="footer-bottom">&copy; <?= date('Y') ?> <?= h($business['name']) ?>. Seluruh hak cipta dilindungi. — <?= h(preg_replace('#^https?://#', '', $business['domain'])) ?></div>
  </div>
</footer>
<a href="https://wa.me/<?= h($business['whatsapp']) ?>" class="wa-float" aria-label="Chat WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3.1.8.8-3-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1s-.6.8-.7.9-.3.1-.5 0a6.6 6.6 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.6c-.1-.2 0-.3.1-.4l.4-.4.2-.4v-.3c0-.1-.5-1.3-.7-1.7s-.4-.4-.5-.4h-.4a.9.9 0 0 0-.6.3 2.7 2.7 0 0 0-.8 2 4.7 4.7 0 0 0 1 2.5 10.8 10.8 0 0 0 4.1 3.6c.6.2 1 .4 1.4.5a3.3 3.3 0 0 0 1.5.1 2.5 2.5 0 0 0 1.6-1.1 1.9 1.9 0 0 0 .1-1.1c-.1-.1-.2-.2-.4-.3Z"/></svg>
</a>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('.nav-toggle');
  var links = document.querySelector('.nav-links');
  if (toggle && links) {
    toggle.addEventListener('click', function () { links.classList.toggle('open'); });
  }
});
</script>
</body>
</html>
    <?php
}
