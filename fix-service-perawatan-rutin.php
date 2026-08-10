<?php
/**
 * Skrip sekali-jalan: Service SEO Optimization untuk halaman "Jasa
 * Perawatan Kolam Renang" -- SATU baris "pages" saja, url_path=
 * '/layanan/perawatan-pembersihan-rutin/', type='service'. Pola sama
 * persis dengan fix-service-pembuatan-kolam-baru.php (Phase 3) dan
 * fix-problem-perbaikan-kebocoran.php (Phase 6).
 *
 * Target dipilih karena ini SATU-SATUNYA halaman layanan yang secara
 * jelas = "Jasa Perawatan Kolam Renang" (title/H1 asli: "Perawatan &
 * Pembersihan Rutin"), dibedakan dari "Kontrak Perawatan Bulanan"
 * (varian paket kontrak, bukan layanan dasarnya).
 *
 * H1 & title diperkuat menyertakan frasa "Kolam Renang" secara
 * eksplisit (sebelumnya cuma "Perawatan & Pembersihan Rutin" tanpa
 * entitas utama halaman), menargetkan intent "jasa perawatan kolam
 * renang" secara langsung -- bukan keyword stuffing, cuma memperbaiki
 * H1 yang sebelumnya kurang eksplisit.
 *
 * Portofolio yang disisipkan (photo.php?id=16 & id=12) adalah 2 proyek
 * ASLI dari 7 total portofolio di database yang tag/temanya = perawatan
 * ("Perawatan Kolam Rutin" area=Bogor Kota, dan "Perawatan Kolam Renang
 * di Cijeruk, Bogor" area=Cijeruk) -- dimensi (738x414 & 426x292)
 * diverifikasi dari getimagesize() nyata di fase-fase sebelumnya,
 * deskripsi kartu memakai kalimat pertama deskripsi asli (tidak
 * dikarang).
 *
 * DATA SAFETY: UPDATE dikunci ke id (dibaca dulu) + url_path + type
 * sekaligus, bukan slug saja. Tidak menyentuh inc/templates/service.php
 * atau baris "pages" lain.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/layanan/perawatan-pembersihan-rutin/';

$h1 = 'Jasa Perawatan Kolam Renang';
$title = 'Perawatan & Pembersihan Rutin';
$metaTitle = 'Jasa Perawatan Kolam Renang di Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Jasa perawatan kolam renang rutin di Bogor: pembersihan, cek kualitas air, dan perawatan filter berkala. Untuk rumah, villa, dan hotel. Konsultasi gratis.';

$content = '<h2>Perawatan Kolam Renang untuk Menjaga Kondisi Tetap Optimal</h2>'
    . '<p style="color:var(--gray-600)">Perawatan rutin adalah kunci menjaga kolam renang tetap sehat dan nyaman digunakan dalam jangka panjang. Tanpa perawatan berkala, air kolam mudah keruh, berlumut, dan sistem filtrasi cepat rusak — terutama di Bogor yang punya curah hujan tinggi sepanjang tahun.</p>'
    . '<p style="color:var(--gray-600)">Kami menyediakan paket perawatan mingguan maupun bulanan yang bisa disesuaikan dengan kebutuhan rumah tinggal, villa, maupun properti komersial.</p>'

    . '<h2 style="margin-top:32px">Apa Saja yang Dilakukan dalam Perawatan Kolam Renang</h2>'
    . '<ol style="color:var(--gray-600);padding-left:20px;margin:0"><li>Pengecekan kejernihan &amp; kualitas air</li><li>Vacuum dasar &amp; dinding kolam</li><li>Backwash/pembersihan filter</li><li>Penyeimbangan kimia air (pH &amp; klorin)</li><li>Pengecekan pompa &amp; sistem sirkulasi</li><li>Laporan kondisi kolam ke pemilik</li></ol>'

    . '<h2 style="margin-top:32px">Masalah yang Sering Terjadi Tanpa Perawatan Rutin</h2>'
    . '<p style="color:var(--gray-600)">Kolam yang jarang dirawat cenderung mengalami <a href="/artikel/cara-mengatasi-air-kolam-berwarna-hijau/" style="color:var(--blue-600);font-weight:600">air berubah keruh atau hijau</a> akibat pertumbuhan alga, gangguan pada <a href="/layanan/penyeimbangan-kimia-air-kolam/" style="color:var(--blue-600);font-weight:600">keseimbangan kimia air</a>, hingga masalah kecil seperti <a href="/layanan/perbaikan-kebocoran-kolam/" style="color:var(--blue-600);font-weight:600">kebocoran</a> yang lebih mudah terlewat kalau tidak ada pengecekan berkala. Perawatan rutin membantu menangkap tanda-tanda ini lebih awal, sebelum jadi masalah yang lebih besar dan mahal — beberapa kesalahan umum seputar ini juga kami bahas di artikel <a href="/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/" style="color:var(--blue-600);font-weight:600">Kesalahan Umum Pemilik Kolam Renang Rumahan</a>.</p>'

    . '<h2 style="margin-top:32px">Perawatan untuk Rumah, Villa, Hotel, dan Properti Lain</h2>'
    . '<p style="color:var(--gray-600)">Kebutuhan perawatan berbeda-beda tergantung jenis properti — rumah tinggal umumnya cukup kunjungan mingguan, sementara hotel atau guesthouse dengan operasional harian butuh jadwal yang lebih ketat dan fleksibel. Untuk kebutuhan jangka panjang dengan jadwal tetap, kami juga menyediakan <a href="/layanan/kontrak-perawatan-bulanan/" style="color:var(--blue-600);font-weight:600">Kontrak Perawatan Bulanan</a>.</p>'

    . '<h2 style="margin-top:32px">Area Layanan</h2>'
    . '<p style="color:var(--gray-600)">Layanan perawatan kami mencakup seluruh wilayah Bogor dan sekitarnya, termasuk <a href="/area/bogor-kota/" style="color:var(--blue-600);font-weight:600">Bogor Kota</a> dan <a href="/area/cijeruk/" style="color:var(--blue-600);font-weight:600">Cijeruk</a>. Untuk daftar lengkap area yang kami layani, lihat <a href="/area-layanan/" style="color:var(--blue-600);font-weight:600">halaman Area Layanan</a>.</p>'

    . '<h2 style="margin-top:32px">Portofolio Perawatan Kolam Renang</h2>'
    . '<p style="color:var(--gray-600)">Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut beberapa contoh pekerjaan perawatan kolam renang yang pernah kami tangani.</p>'
    . '<div class="portfolio-grid">'
    . '<a class="portfolio-card" href="/portofolio/perawatan-kolam-rutin/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=16" alt="Perawatan kolam renang rutin untuk hotel di Bogor Kota" width="738" height="414" loading="lazy"></div><div class="portfolio-body"><span class="tag">Bogor Kota</span><h3>Perawatan Kolam Rutin</h3><p style="color:var(--gray-600)">Program perawatan bulanan untuk kolam renang hotel di kawasan Bogor Kota, mencakup pengecekan kualitas air, pembersihan filter, dan penyeimbangan kimia secara berkala.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>'
    . '<a class="portfolio-card" href="/portofolio/perawatan-kolam-renang-di-cijeruk-bogor/" style="display:block;color:inherit"><div class="portfolio-thumb"><img src="/photo.php?id=12" alt="Perawatan kolam renang di Cijeruk, Bogor" width="426" height="292" loading="lazy"></div><div class="portfolio-body"><span class="tag">Cijeruk</span><h3>Perawatan Kolam Renang di Cijeruk, Bogor</h3><p style="color:var(--gray-600)">Proyek perawatan kolam renang di Cijeruk, Bogor, Jawa Barat, dilakukan dengan pemeriksaan kondisi air, kebersihan kolam, sirkulasi, pompa, dan filter.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a>'
    . '</div>'

    . '<h2 style="margin-top:32px">Konsultasi Perawatan Kolam Renang</h2>'
    . '<p style="color:var(--gray-600)">Ingin kolam Anda selalu jernih dan siap pakai tanpa perlu memikirkan jadwal perawatannya sendiri? Hubungi kami untuk konsultasi paket perawatan yang sesuai dengan jenis properti dan frekuensi pemakaian kolam Anda.</p>'
    . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';

$faq = [
    ['q' => 'Seberapa sering kolam renang perlu dirawat?', 'a' => 'Untuk penggunaan rumah tinggal umumnya cukup 1x seminggu, sementara kolam komersial atau yang sering dipakai bisa membutuhkan kunjungan lebih rapat.'],
    ['q' => 'Apakah tersedia paket kontrak bulanan?', 'a' => 'Ya, silakan lihat halaman Kontrak Perawatan Bulanan kami atau hubungi tim untuk penawaran sesuai kebutuhan.'],
    ['q' => 'Sudah berapa lama menangani jasa perawatan kolam renang?', 'a' => 'Perawatan kolam renang adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

try {
    $pdo = get_db();

    $before = $pdo->prepare('SELECT id, title, h1, meta_title, meta_description, content, faq_json FROM pages WHERE url_path = :u AND type = \'service\'');
    $before->execute([':u' => $urlPath]);
    $beforeRow = $before->fetch();

    if (!$beforeRow) {
        respond(false, 'Baris tidak ditemukan -- tidak ada yang diubah.');
    }

    $stmt = $pdo->prepare(
        'UPDATE pages SET title = :title, h1 = :h1, meta_title = :meta_title, meta_description = :meta_description, content = :content, faq_json = :faq_json
         WHERE id = :id AND url_path = :url_path AND type = \'service\''
    );
    $stmt->execute([
        ':title' => $title,
        ':h1' => $h1,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':content' => $content,
        ':faq_json' => json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ':id' => $beforeRow['id'],
        ':url_path' => $urlPath,
    ]);

    respond(true, 'Halaman "Jasa Perawatan Kolam Renang" berhasil diperbarui.', [
        'page_id' => $beforeRow['id'],
        'url_path' => $urlPath,
        'rows_affected' => $stmt->rowCount(),
        'before' => [
            'title' => $beforeRow['title'],
            'h1' => $beforeRow['h1'],
            'meta_title' => $beforeRow['meta_title'],
            'meta_description' => $beforeRow['meta_description'],
        ],
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbarui: ' . $e->getMessage());
}
