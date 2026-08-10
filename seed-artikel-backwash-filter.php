<?php
/**
 * Skrip sekali-jalan: menerbitkan artikel "Apa Itu Backwash Filter dan
 * Kapan Perlu Dilakukan" (brief 2026-08-10, batch 8 artikel). Pola sama
 * persis dengan seed-artikel-biaya-perawatan.php.
 *
 * Dijalankan lewat browser (Basic Auth). Aman dijalankan berkali-kali --
 * INSERT ... ON DUPLICATE KEY UPDATE berdasarkan url_path.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$urlPath = '/artikel/apa-itu-backwash-filter-kolam-renang/';
$title = 'Apa Itu Backwash Filter dan Kapan Perlu Dilakukan';
$metaTitle = 'Apa Itu Backwash Filter Kolam Renang dan Kapan Perlu Dilakukan | Jasa Kolam Renang Bogor';
$metaDescription = 'Backwash filter adalah langkah penting menjaga sistem filtrasi kolam tetap optimal. Kenali tanda-tanda filter kolam Anda perlu di-backwash.';
$intro = 'Bagi banyak pemilik kolam renang, istilah "backwash" mungkin masih asing meski ini salah satu langkah perawatan paling penting untuk sistem filtrasi. Tanpa backwash yang rutin, filter yang seharusnya menjaga air tetap jernih justru bisa jadi kurang efektif, bahkan berisiko rusak.';

// Belum ada foto khusus untuk artikel ini -- pakai foto proyek
// "Instalasi Sistem Filtrasi" dari galeri Portofolio yang sudah ada
// (tema filtrasi paling relevan). Admin bisa menggantinya lewat tab
// Artikel.
$coverImage = 'photo.php?id=17';

$content = '<h2>Apa Itu Backwash?</h2>'
    . '<p>Backwash adalah proses membalik arah aliran air dalam sistem filter pasir, dari yang biasanya mengalir searah untuk menyaring kotoran, dibalik sehingga air mengalir ke arah sebaliknya dan mendorong kotoran yang menumpuk di media filter keluar melalui saluran pembuangan. Prosesnya cukup singkat, biasanya beberapa menit, tapi manfaatnya besar untuk menjaga performa filter. Setelah backwash selesai, biasanya diikuti dengan proses "rinse" singkat untuk memastikan media filter sudah tertata rapi kembali sebelum sistem dikembalikan ke mode penyaringan normal.</p>'
    . '<h2>Tanda Filter Perlu Di-Backwash</h2>'
    . '<p>Ada dua indikator utama yang biasa kami pakai untuk menentukan waktu backwash: pertama, tekanan pada pressure gauge filter naik signifikan dari tekanan normal saat filter masih bersih (biasanya kenaikan 8-10 psi dari baseline sudah jadi tanda). Kedua, aliran air dari nozzle/return jet terasa melemah dibanding biasanya, tanda media filter sudah terlalu padat oleh kotoran yang tersaring. Mencatat angka tekanan baseline saat filter baru dibersihkan sangat membantu supaya kenaikan tekanan di kunjungan berikutnya bisa dibandingkan secara objektif, bukan sekadar perkiraan.</p>'
    . '<h2>Seberapa Sering Backwash Perlu Dilakukan</h2>'
    . '<p>Frekuensinya bervariasi tergantung intensitas pemakaian kolam dan kondisi lingkungan sekitar — kolam yang sering dipakai atau berada di area dengan banyak debu/dedaunan (seperti kolam dekat area rindang atau musim hujan) biasanya butuh backwash lebih sering. Sebagai gambaran umum, banyak kolam residensial cukup di-backwash setiap 2-4 minggu sekali, tapi patokan paling akurat tetap indikator tekanan dan aliran di atas, bukan jadwal kaku semata.</p>'
    . '<h2>Bedanya dengan Mengganti Media Filter</h2>'
    . '<p>Backwash membersihkan media filter yang sudah ada, tapi tidak menggantinya. Pasir atau media filter lain tetap punya usia pakai tertentu (untuk filter pasir umumnya sekitar 5-7 tahun) — setelah itu, backwash saja tidak lagi cukup mengembalikan performa penyaringan seperti semula, dan medianya perlu diganti total. Ini beda dengan backwash rutin yang memang ditujukan untuk perawatan harian/mingguan, bukan penggantian material.</p>'
    . '<h2>Risiko Kalau Diabaikan</h2>'
    . '<p>Filter yang tidak pernah di-backwash akan terus kehilangan efektivitas menyaring kotoran, membuat air tampak kurang jernih meski bahan kimia sudah seimbang. Dalam jangka panjang, tekanan berlebih pada media filter yang tersumbat juga bisa membebani pompa dan mempercepat keausan komponen sistem sirkulasi. Kombinasi filter yang tersumbat dan pompa yang bekerja lebih keras dari seharusnya inilah yang sering membuat dua masalah muncul bersamaan — air kurang jernih dan tagihan listrik yang naik tanpa disadari penyebabnya.</p>'
    . '<p>Kalau Anda tidak yakin cara membaca pressure gauge atau kapan terakhir kali filter kolam Anda di-backwash, tim kami bisa cek kondisinya langsung dan masukkan ke jadwal perawatan rutin supaya sistem filtrasi tetap optimal.</p>';

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        "INSERT INTO pages (type, url_path, title, meta_title, meta_description, h1, intro, content, cover_image, status)
         VALUES ('article', :url_path, :title, :meta_title, :meta_description, :h1, :intro, :content, :cover_image, 'published')
         ON DUPLICATE KEY UPDATE title=VALUES(title), meta_title=VALUES(meta_title),
           meta_description=VALUES(meta_description), h1=VALUES(h1), intro=VALUES(intro),
           content=VALUES(content), cover_image=VALUES(cover_image), status='published'"
    );
    $stmt->execute([
        ':url_path' => $urlPath,
        ':title' => $title,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':h1' => $title,
        ':intro' => $intro,
        ':content' => $content,
        ':cover_image' => $coverImage,
    ]);

    respond(true, 'Artikel berhasil diterbitkan.', ['url_path' => $urlPath]);
} catch (Exception $e) {
    respond(false, 'Gagal menerbitkan artikel: ' . $e->getMessage());
}
