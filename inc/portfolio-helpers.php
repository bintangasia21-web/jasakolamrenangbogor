<?php
/**
 * Logic bersama untuk Portfolio: pembuatan slug URL dan sinkronisasi
 * otomatis halaman detail SEO (tabel "pages" type=portfolio) setiap
 * kali satu proyek disimpan. Dipakai oleh edit-portfolio.php.
 */

function portfolio_slugify($text) {
    $text = strtolower(trim((string) $text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Buat/perbarui baris "pages" (type=portfolio) yang jadi halaman detail
 * SEO untuk satu proyek portfolio. Selalu status=published karena item
 * galeri portfolio memang sudah dianggap live begitu tersimpan.
 * cover_image HANYA diisi saat baris pertama kali dibuat (tidak
 * menimpa kalau admin sudah menyesuaikan lewat editor halaman lain).
 *
 * Mengembalikan url_path final (dipakai untuk portfolio.detail_link).
 */
function portfolio_sync_seo_page($pdo, $portfolioId, $title, $area, $desc, $image) {
    $title = trim((string) $title);
    if ($title === '') {
        return null;
    }

    $slug = portfolio_slugify($title);
    if ($slug === '') {
        $slug = 'proyek-' . $portfolioId;
    }
    $urlPath = '/portofolio/' . $slug . '/';

    // Kalau url_path ini sudah "dimiliki" proyek portfolio LAIN (dicek
    // langsung dari portfolio.detail_link, sumber kebenaran kepemilikan
    // -- bukan menebak dari isi konten yang bisa berubah kapan saja),
    // sisipkan id proyek ini supaya slug-nya unik dan tidak menimpa
    // halaman detail proyek lain.
    $ownerCheck = $pdo->prepare('SELECT id FROM portfolio WHERE detail_link = :url_path AND id != :id LIMIT 1');
    $ownerCheck->execute([':url_path' => $urlPath, ':id' => $portfolioId]);
    if ($ownerCheck->fetch() !== false) {
        $urlPath = '/portofolio/' . $slug . '-' . $portfolioId . '/';
    }

    // Deskripsi bisa berapa pun panjangnya (mis. hasil tempel dari AI
    // yang menulis beberapa paragraf) -- pecah jadi paragraf HTML yang
    // benar (bukan satu <p> raksasa tanpa jeda, karena browser
    // mengabaikan baris baru mentah), dan pakai paragraf PERTAMA saja
    // sebagai lead/intro singkat di bagian hero halaman detail.
    $paragraphs = preg_split('/\n\s*\n/', trim($desc));
    $intro = trim($paragraphs[0] ?? $desc);

    // Paragraf pertama sudah ditampilkan sebagai intro/lead di hero, jadi
    // body tidak perlu mengulanginya lagi. Kalau cuma ada satu paragraf,
    // body dikosongkan (bukan diisi ulang dengan paragraf yang sama) --
    // template portfolio.php menyembunyikan section body kalau kosong.
    $bodyParagraphs = count($paragraphs) > 1 ? array_slice($paragraphs, 1) : [];
    $contentHtml = implode('', array_map(function ($p) {
        return '<p>' . nl2br(htmlspecialchars(trim($p), ENT_QUOTES, 'UTF-8')) . '</p>';
    }, array_filter($bodyParagraphs, function ($p) { return trim($p) !== ''; })));

    $stmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, h1, area_ref, intro, content, cover_image, status)
         VALUES ('portfolio', :url_path, :title, :h1, :area_ref, :intro, :content, :cover_image, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), h1=VALUES(h1), area_ref=VALUES(area_ref),
           intro=VALUES(intro), content=VALUES(content), status='published'"
    );
    $stmt->execute([
        ':url_path' => $urlPath,
        ':title' => $title,
        ':h1' => $title,
        ':area_ref' => $area,
        ':intro' => $intro,
        ':content' => $contentHtml,
        ':cover_image' => $image
    ]);

    return $urlPath;
}
