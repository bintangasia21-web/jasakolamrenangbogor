<?php
/**
 * Skrip sekali-jalan (Phase 6, 2026-08-11): implementasi Problem SEO
 * untuk SATU baris "pages" saja -- url_path='/layanan/perbaikan-
 * kebocoran-kolam/', type='service' -- dipilih sebagai target Problem
 * SEO paling potensial (lihat SELECTED TARGET di
 * PROBLEM-PAGE-SEO-PHASE-6.md): intent komersial tinggi (kolam bocor),
 * sudah ada bukti kerja (portofolio "Perbaikan Kebocoran Kolam",
 * area="Bogor Kota"), sudah ada artikel relevan ("Kesalahan Umum
 * Pemilik Kolam Renang Rumahan" -- section "Mengabaikan Kebocoran
 * Kecil"), dan sudah ditautkan dari halaman /area/bogor-kota/ (Phase 5).
 *
 * Hanya menyentuh kolom title, meta_title, meta_description, content,
 * faq_json milik baris ini. h1/intro TIDAK diubah (H1 sudah baik).
 * Tidak menyentuh inc/templates/service.php atau baris "pages" lain.
 *
 * DATA SAFETY (Phase 6, aturan #18): query UPDATE dibatasi dengan
 * WHERE url_path = :url_path AND type = 'service' (bukan slug LIKE
 * ambigu) -- baris tunggal, tidak mungkin menimpa baris lain. Nilai
 * lama (title/meta_title/meta_description) dibaca & dilaporkan sebagai
 * "before" SEBELUM UPDATE dijalankan, untuk verifikasi before/after
 * yang akurat tanpa perlu akses DB terpisah.
 *
 * Gambar yang disisipkan (photo.php?id=15) adalah foto portofolio ASLI
 * proyek "Perbaikan Kebocoran Kolam" (area="Bogor Kota") yang sudah
 * live publik -- dimensi (427x299) diverifikasi dari getimagesize()
 * nyata, bukan ditebak. Deskripsi kartu memakai kalimat pertama
 * deskripsi asli proyek tsb (tidak dikarang).
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/layanan/perbaikan-kebocoran-kolam/';

$title = 'Perbaikan Kebocoran Kolam';
$metaTitle = 'Perbaikan Kebocoran Kolam di Bogor | Jasa Kolam Renang Bogor';
$metaDescription = 'Kolam renang bocor atau air terus berkurang tanpa sebab jelas? Kami deteksi dan perbaiki kebocoran secara akurat di Bogor — survei & estimasi gratis.';

$content = '<h2>Mengenali Masalah</h2>'
    . '<p style="color:var(--gray-600)">Kebocoran kolam renang sering tidak langsung terlihat jelas. Tanda paling umum adalah permukaan air yang terus turun melebihi penguapan wajar, muncul area basah atau lembap di sekitar kolam, atau tagihan air yang naik tanpa sebab jelas.</p>'

    . '<h2 style="margin-top:32px">Penyebab yang Perlu Diperiksa</h2>'
    . '<p style="color:var(--gray-600)">Kebocoran kolam renang bisa berasal dari berbagai sumber — retak pada dinding/dasar kolam, sambungan pipa yang longgar, atau kerusakan pada skimmer dan fitting. Setiap sumber butuh metode pemeriksaan yang berbeda, sehingga identifikasi penyebab yang akurat jadi langkah penting sebelum perbaikan dilakukan.</p>'

    . '<h2 style="margin-top:32px">Solusi dan Penanganan</h2>'
    . '<p style="color:var(--gray-600)">Kami menggunakan metode pengujian tekanan dan pengamatan visual menyeluruh untuk memastikan sumber kebocoran ditemukan secara akurat sebelum perbaikan dilakukan. Tahapan pengerjaannya:</p>'
    . '<ol style="color:var(--gray-600);padding-left:20px;margin:12px 0 0"><li>Pengujian tekanan sistem pipa</li><li>Pemeriksaan visual struktur &amp; sambungan</li><li>Penentuan titik kebocoran</li><li>Perbaikan &amp; waterproofing area terdampak</li><li>Pengisian ulang &amp; pemantauan level air</li><li>Laporan hasil perbaikan</li></ol>'

    . '<h2 style="margin-top:32px">Kapan Perlu Ditangani Teknisi</h2>'
    . '<p style="color:var(--gray-600)">Begitu salah satu tanda di atas muncul, sebaiknya jangan ditunda — air yang terus berkurang tanpa sebab jelas berisiko merembes ke struktur bangunan sekitar. Kebocoran kecil yang dibiarkan juga bisa membesar seiring waktu dan berujung perbaikan yang jauh lebih mahal, seperti yang kami bahas lebih lanjut di artikel <a href="/artikel/kesalahan-umum-pemilik-kolam-renang-rumahan/" style="color:var(--blue-600);font-weight:600">Kesalahan Umum Pemilik Kolam Renang Rumahan</a>.</p>'

    . '<h2 style="margin-top:32px">Layanan yang Terkait</h2>'
    . '<p style="color:var(--gray-600)">Perbaikan kebocoran sering berjalan beriringan dengan <a href="/layanan/waterproofing-kolam-renang/" style="color:var(--blue-600);font-weight:600">waterproofing kolam renang</a> untuk mencegah rembesan berulang di titik yang sama, terutama untuk kolam lama yang lapisan waterproofing-nya sudah menurun.</p>'

    . '<h2 style="margin-top:32px">Proyek yang Pernah Kami Kerjakan</h2>'
    . '<p style="color:var(--gray-600)">Dengan lebih dari 10 tahun pengalaman dan 350+ proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya, berikut salah satu contoh penanganan kebocoran kolam yang pernah kami kerjakan.</p>'
    . '<div class="portfolio-grid"><a class="portfolio-card" href="/portofolio/perbaikan-kebocoran-kolam/" style="display:block;color:inherit;max-width:360px"><div class="portfolio-thumb"><img src="/photo.php?id=15" alt="Perbaikan kebocoran dan waterproofing kolam di Bogor Kota" width="427" height="299" loading="lazy"></div><div class="portfolio-body"><span class="tag">Bogor Kota</span><h3>Perbaikan Kebocoran Kolam</h3><p style="color:var(--gray-600)">Perbaikan struktur dan pelapisan ulang waterproofing untuk kolam renang lama di kawasan Bogor Kota yang mengalami rembesan akibat curah hujan tinggi.</p><span class="portfolio-link">Lihat Detail <span class="arrow">&rarr;</span></span></div></a></div>'

    . '<h2 style="margin-top:32px">Konsultasi Perbaikan Kebocoran Kolam</h2>'
    . '<p style="color:var(--gray-600)">Curiga kolam Anda bocor? Hubungi kami untuk pemeriksaan awal — kami bantu identifikasi sumber kebocoran dan berikan estimasi perbaikan sebelum masalah membesar.</p>'
    . '<p><a href="https://wa.me/6282216623388" style="color:var(--blue-600);font-weight:600">Chat via WhatsApp untuk Konsultasi &rarr;</a></p>';

$faq = [
    ['q' => 'Bagaimana cara mengetahui kolam renang saya bocor?', 'a' => 'Tanda umum meliputi permukaan air yang terus turun melebihi penguapan wajar, area basah di sekitar kolam, atau tagihan air yang naik tanpa sebab jelas.'],
    ['q' => 'Berapa lama proses deteksi dan perbaikan kebocoran?', 'a' => 'Deteksi biasanya memakan waktu 1–2 hari, sedangkan perbaikan tergantung tingkat keparahan — akan kami sampaikan estimasi setelah pemeriksaan.'],
    ['q' => 'Sudah berapa lama menangani perbaikan kebocoran kolam renang?', 'a' => 'Perbaikan kebocoran adalah bagian dari lebih dari 10 tahun pengalaman kami, dengan lebih dari 350 proyek yang telah kami selesaikan di wilayah Bogor dan sekitarnya.'],
];

try {
    $pdo = get_db();

    // Simpan nilai lama SEBELUM UPDATE untuk laporan before/after yang akurat.
    $before = $pdo->prepare('SELECT id, title, meta_title, meta_description, content, faq_json FROM pages WHERE url_path = :u AND type = \'service\'');
    $before->execute([':u' => $urlPath]);
    $beforeRow = $before->fetch();

    if (!$beforeRow) {
        respond(false, 'Baris tidak ditemukan -- tidak ada yang diubah.');
    }

    $stmt = $pdo->prepare(
        'UPDATE pages SET title = :title, meta_title = :meta_title, meta_description = :meta_description, content = :content, faq_json = :faq_json
         WHERE id = :id AND url_path = :url_path AND type = \'service\''
    );
    $stmt->execute([
        ':title' => $title,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':content' => $content,
        ':faq_json' => json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ':id' => $beforeRow['id'],
        ':url_path' => $urlPath,
    ]);

    respond(true, 'Halaman "Perbaikan Kebocoran Kolam" berhasil diperbarui.', [
        'page_id' => $beforeRow['id'],
        'url_path' => $urlPath,
        'rows_affected' => $stmt->rowCount(),
        'before' => [
            'title' => $beforeRow['title'],
            'meta_title' => $beforeRow['meta_title'],
            'meta_description' => $beforeRow['meta_description'],
        ],
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbarui: ' . $e->getMessage());
}
