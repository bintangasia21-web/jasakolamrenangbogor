<?php
/**
 * Skrip sekali-jalan: menulis ulang konten 6 halaman area yang sudah live
 * (Ciawi, Yasmin, Cijeruk, Rancamaya, Bogor Raya, Karadenan) supaya fokus
 * ke layanan Perawatan Kolam Renang (bukan 4 layanan generik seperti
 * sebelumnya), dengan karakteristik nyata tiap area yang relevan untuk
 * perawatan rutin (akses tim, kondisi lingkungan, tipe properti umum).
 *
 * Dijalankan lewat browser (Basic Auth), sama seperti setup-schema.php.
 * Aman dijalankan berkali-kali — UPDATE berdasarkan url_path yang sudah
 * ada, tidak membuat baris baru dan tidak mengubah status/tipe halaman.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/inc/db.php';

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$pages = [
    [
        'url_path' => '/area/ciawi/',
        'area_ref' => 'Ciawi',
        'title' => 'Perawatan Kolam Renang Ciawi',
        'meta_title' => 'Jasa Perawatan Kolam Renang Ciawi Bogor | Terjadwal & Terpercaya',
        'meta_description' => 'Jasa perawatan kolam renang rutin di Ciawi, Bogor — untuk vila, resort, dan tempat peristirahatan di jalur Puncak. Jadwal fleksibel, tim berpengalaman menangani kolam musiman.',
        'h1' => 'Jasa Perawatan Kolam Renang Ciawi',
        'intro' => 'Perawatan rutin kolam renang untuk vila, resort, dan tempat peristirahatan di Ciawi — gerbang menuju kawasan Puncak yang ramai dikunjungi akhir pekan.',
        'content' => '<h2>Karakteristik Area Ciawi untuk Perawatan Kolam Renang</h2>'
            . '<p>Ciawi adalah jalur utama menuju kawasan Puncak, sehingga lalu lintas di sekitar area ini cenderung padat terutama pada akhir pekan dan musim liburan. Kami menjadwalkan kunjungan perawatan di luar jam-jam padat tersebut agar tim tetap bisa datang tepat waktu sesuai jadwal yang disepakati.</p>'
            . '<p>Banyak kolam renang di Ciawi berada di vila, resort, dan tempat peristirahatan dengan pola hunian musiman — ramai saat akhir pekan atau libur panjang, kosong pada hari kerja biasa. Kolam yang jarang digunakan dalam waktu lama lebih rentan terhadap pertumbuhan lumut dan penurunan kualitas air jika tidak dirawat secara berkala, meski sedang tidak dihuni.</p>'
            . '<h2>Layanan Perawatan yang Kami Tawarkan di Ciawi</h2>'
            . '<ul>'
            . '<li><strong>Perawatan rutin terjadwal:</strong> pembersihan permukaan dan dasar kolam, pengecekan kadar kimia air, dan perawatan sistem filtrasi sesuai jadwal kunjungan yang disepakati.</li>'
            . '<li><strong>Persiapan sebelum kedatangan tamu:</strong> untuk vila dan resort dengan hunian musiman, kami menyediakan layanan pembersihan menjelang akhir pekan atau musim liburan agar kolam siap pakai saat tamu tiba.</li>'
            . '<li><strong>Penanganan lumut dan air keruh:</strong> perawatan khusus untuk kolam yang sempat kosong dalam waktu lama akibat pola hunian musiman.</li>'
            . '</ul>'
            . '<p>Tim kami memahami kebutuhan properti di jalur Puncak — jadwal yang fleksibel dan komunikasi yang jelas sangat penting bagi pemilik vila atau pengelola resort yang tidak selalu berada di lokasi.</p>',
        'faq' => [
            ['q' => 'Apakah bisa menjadwalkan perawatan hanya menjelang akhir pekan?', 'a' => 'Bisa. Banyak pelanggan kami di Ciawi memiliki vila atau resort dengan hunian musiman, jadi kami menyediakan jadwal fleksibel termasuk perawatan menjelang kedatangan tamu di akhir pekan atau musim liburan.'],
            ['q' => 'Bagaimana jika kolam sudah lama tidak dirawat karena jarang dihuni?', 'a' => 'Kami akan melakukan asesmen kondisi air dan permukaan kolam terlebih dahulu, lalu melakukan pembersihan menyeluruh dan penyeimbangan kimia air sebelum kolam siap digunakan kembali.'],
            ['q' => 'Apakah lalu lintas menuju Puncak memengaruhi jadwal kunjungan tim?', 'a' => 'Kami menyesuaikan jam kunjungan di luar waktu-waktu padat lalu lintas Ciawi–Puncak, terutama saat akhir pekan, supaya jadwal perawatan tetap tepat waktu.']
        ]
    ],
    [
        'url_path' => '/area/yasmin/',
        'area_ref' => 'Yasmin',
        'title' => 'Perawatan Kolam Renang Yasmin',
        'meta_title' => 'Jasa Perawatan Kolam Renang Yasmin Bogor Utara | Jadwal Rutin',
        'meta_description' => 'Jasa perawatan kolam renang rutin di Yasmin, Bogor Utara — untuk rumah tinggal di kawasan perumahan. Kunjungan terjadwal mingguan/bulanan, akses mudah dan konsisten.',
        'h1' => 'Jasa Perawatan Kolam Renang Yasmin',
        'intro' => 'Perawatan rutin kolam renang untuk rumah tinggal di kawasan perumahan Yasmin, Bogor Utara, dengan jadwal kunjungan yang konsisten.',
        'content' => '<h2>Karakteristik Area Yasmin untuk Perawatan Kolam Renang</h2>'
            . '<p>Yasmin merupakan kawasan perumahan yang sudah mapan di Bogor Utara, dengan akses jalan yang baik dan kepadatan hunian yang tinggi. Kondisi ini justru menguntungkan untuk perawatan kolam renang rutin — tim kami dapat menjangkau lokasi dengan mudah dan menjaga jadwal kunjungan tetap konsisten setiap minggu atau setiap bulan sesuai paket yang dipilih.</p>'
            . '<p>Mayoritas kolam renang di Yasmin berada di rumah tinggal dengan ukuran kolam kecil hingga menengah, umumnya digunakan setiap hari oleh penghuni rumah. Pemakaian harian ini membuat kualitas air perlu dipantau lebih sering dibanding kolam yang jarang dipakai, terutama kadar klorin dan pH air.</p>'
            . '<h2>Layanan Perawatan yang Kami Tawarkan di Yasmin</h2>'
            . '<ul>'
            . '<li><strong>Perawatan mingguan atau bulanan:</strong> pembersihan rutin, pengecekan dan penyeimbangan kimia air, serta perawatan filter sesuai jadwal yang disepakati dengan pemilik rumah.</li>'
            . '<li><strong>Pemantauan kualitas air harian pool:</strong> untuk kolam yang dipakai setiap hari, kami memastikan kadar klorin dan pH tetap dalam rentang aman untuk pemakaian rutin keluarga.</li>'
            . '<li><strong>Perawatan sistem sirkulasi:</strong> pengecekan pompa dan filter agar tetap bekerja optimal mengingat frekuensi pemakaian kolam yang tinggi.</li>'
            . '</ul>'
            . '<p>Karena lokasi yang mudah dijangkau, kami bisa menawarkan jadwal kunjungan yang lebih sering dan konsisten dibanding area yang aksesnya lebih jauh — cocok untuk pemilik rumah yang menginginkan kolam selalu siap pakai.</p>',
        'faq' => [
            ['q' => 'Berapa sering sebaiknya kolam renang rumah di Yasmin dirawat?', 'a' => 'Untuk kolam yang dipakai setiap hari, kami biasanya menyarankan kunjungan mingguan agar kualitas air tetap terjaga. Kunjungan bulanan juga tersedia untuk kolam dengan pemakaian lebih jarang.'],
            ['q' => 'Apakah tim bisa datang di jam tertentu secara rutin?', 'a' => 'Bisa. Berkat akses yang mudah di kawasan Yasmin, kami dapat menyepakati jam kunjungan tetap setiap minggu atau bulan sesuai preferensi pemilik rumah.'],
            ['q' => 'Apakah perawatan mencakup pengecekan pompa dan filter?', 'a' => 'Ya, setiap kunjungan rutin mencakup pengecekan sistem filtrasi, karena kolam yang dipakai setiap hari membutuhkan sirkulasi air yang bekerja optimal.']
        ]
    ],
    [
        'url_path' => '/area/cijeruk/',
        'area_ref' => 'Cijeruk',
        'title' => 'Perawatan Kolam Renang Cijeruk',
        'meta_title' => 'Jasa Perawatan Kolam Renang Cijeruk Bogor | Kaki Gunung Salak',
        'meta_description' => 'Jasa perawatan kolam renang rutin di Cijeruk, Bogor — kawasan kaki Gunung Salak dengan kelembaban tinggi. Penanganan khusus lumut dan alga untuk vila dan tempat peristirahatan.',
        'h1' => 'Jasa Perawatan Kolam Renang Cijeruk',
        'intro' => 'Perawatan rutin kolam renang untuk vila dan tempat peristirahatan di Cijeruk, kawasan kaki Gunung Salak dengan kondisi lingkungan yang lebih lembap.',
        'content' => '<h2>Karakteristik Area Cijeruk untuk Perawatan Kolam Renang</h2>'
            . '<p>Cijeruk berada di kaki Gunung Salak, di selatan wilayah Bogor, dengan kondisi udara yang lebih sejuk dan lembap dibanding area perkotaan. Curah hujan yang relatif tinggi dan kelembaban udara membuat kolam renang di kawasan ini lebih rentan terhadap pertumbuhan lumut dan alga jika tidak dirawat secara rutin.</p>'
            . '<p>Sebagian jalan menuju properti di Cijeruk berkontur menanjak dan lebih sempit dibanding area perkotaan, sehingga tim kami menyesuaikan waktu tempuh dan peralatan yang dibawa agar kunjungan tetap efisien. Properti di area ini umumnya berupa vila dan tempat peristirahatan yang memanfaatkan suasana sejuk pegunungan.</p>'
            . '<h2>Layanan Perawatan yang Kami Tawarkan di Cijeruk</h2>'
            . '<ul>'
            . '<li><strong>Penanganan lumut dan alga:</strong> perawatan intensif untuk mengatasi pertumbuhan lumut yang lebih cepat akibat kelembaban tinggi khas kawasan kaki gunung.</li>'
            . '<li><strong>Perawatan rutin terjadwal:</strong> pembersihan, penyeimbangan kimia air, dan perawatan filter yang disesuaikan dengan kondisi cuaca dan curah hujan di area ini.</li>'
            . '<li><strong>Pengecekan drainase sekitar kolam:</strong> memastikan air hujan tidak masuk berlebihan ke kolam dan menurunkan kualitas air secara drastis.</li>'
            . '</ul>'
            . '<p>Karena kondisi lingkungan Cijeruk yang khas, kami menyesuaikan frekuensi kunjungan dan jenis perawatan agar kolam tetap jernih meski berada di kawasan dengan kelembaban tinggi.</p>',
        'faq' => [
            ['q' => 'Mengapa kolam renang di Cijeruk lebih mudah berlumut?', 'a' => 'Cijeruk berada di kaki Gunung Salak dengan kelembaban udara dan curah hujan yang lebih tinggi dibanding area perkotaan Bogor, sehingga lumut dan alga tumbuh lebih cepat jika kolam tidak dirawat rutin.'],
            ['q' => 'Apakah akses jalan yang menanjak memengaruhi jadwal kunjungan?', 'a' => 'Kami memperhitungkan kondisi jalan dan waktu tempuh saat menyusun jadwal kunjungan ke Cijeruk agar tim tetap datang sesuai waktu yang disepakati.'],
            ['q' => 'Apakah perlu perawatan lebih sering saat musim hujan?', 'a' => 'Ya, kami biasanya menyarankan frekuensi kunjungan yang lebih sering saat musim hujan karena air hujan dan kelembaban tinggi mempercepat penurunan kualitas air kolam.']
        ]
    ],
    [
        'url_path' => '/area/rancamaya/',
        'area_ref' => 'Rancamaya',
        'title' => 'Perawatan Kolam Renang Rancamaya',
        'meta_title' => 'Jasa Perawatan Kolam Renang Rancamaya Bogor | Kompleks Residensial',
        'meta_description' => 'Jasa perawatan kolam renang rutin di Rancamaya, Bogor — kawasan residensial dengan kolam privat berstandar tinggi. Kunjungan terjadwal dan terkoordinasi dengan keamanan kompleks.',
        'h1' => 'Jasa Perawatan Kolam Renang Rancamaya',
        'intro' => 'Perawatan rutin kolam renang privat untuk rumah tinggal di kawasan residensial Rancamaya, dengan standar kebersihan dan koordinasi akses kompleks yang rapi.',
        'content' => '<h2>Karakteristik Area Rancamaya untuk Perawatan Kolam Renang</h2>'
            . '<p>Rancamaya dikenal sebagai kawasan residensial dengan konsep hunian yang tertata rapi di kompleks tertutup (gated community). Sebagian besar rumah di kawasan ini memiliki kolam renang privat dengan ukuran dan finishing yang lebih besar dan mewah dibanding rumah tinggal pada umumnya, sehingga standar kebersihan dan perawatan yang diharapkan pemilik juga lebih tinggi.</p>'
            . '<p>Karena berada di kompleks tertutup, setiap kunjungan tim perawatan perlu melalui proses masuk lewat pos keamanan. Kami selalu mengoordinasikan jadwal kunjungan terlebih dahulu dengan pemilik rumah maupun pihak keamanan kompleks agar proses akses berjalan lancar tanpa mengganggu jadwal perawatan.</p>'
            . '<h2>Layanan Perawatan yang Kami Tawarkan di Rancamaya</h2>'
            . '<ul>'
            . '<li><strong>Perawatan rutin terjadwal dengan koordinasi akses:</strong> jadwal kunjungan disepakati di awal dan dikomunikasikan ke keamanan kompleks agar tim dapat masuk tepat waktu.</li>'
            . '<li><strong>Perawatan material premium:</strong> penanganan permukaan kolam berbahan marmer atau batu alam yang umum digunakan pada kolam-kolam di kawasan ini, dengan metode pembersihan yang tidak merusak lapisan finishing.</li>'
            . '<li><strong>Penyeimbangan kimia air standar tinggi:</strong> menjaga kejernihan air sesuai ekspektasi pemilik rumah di kawasan residensial premium.</li>'
            . '</ul>'
            . '<p>Kami memahami pentingnya profesionalisme saat bekerja di kompleks tertutup — mulai dari kerapian saat bekerja hingga komunikasi jadwal yang jelas dengan pemilik rumah dan pengelola kawasan.</p>',
        'faq' => [
            ['q' => 'Apakah tim perlu izin khusus untuk masuk ke kompleks Rancamaya?', 'a' => 'Ya, karena Rancamaya adalah kompleks tertutup, kami mengoordinasikan jadwal kunjungan dengan pemilik rumah dan keamanan kompleks terlebih dahulu sebelum tim datang.'],
            ['q' => 'Apakah perawatan kolam bermaterial marmer atau batu alam berbeda dari kolam biasa?', 'a' => 'Ya, kami menyesuaikan bahan dan metode pembersihan agar tidak merusak lapisan finishing premium yang umum digunakan pada kolam-kolam di Rancamaya.'],
            ['q' => 'Apakah bisa berlangganan perawatan bulanan untuk rumah di Rancamaya?', 'a' => 'Bisa. Kami menawarkan paket perawatan terjadwal bulanan maupun mingguan, disesuaikan dengan kebutuhan dan frekuensi pemakaian kolam Anda.']
        ]
    ],
    [
        'url_path' => '/area/bogor-raya/',
        'area_ref' => 'Bogor Raya',
        'title' => 'Perawatan Kolam Renang Bogor Raya',
        'meta_title' => 'Jasa Perawatan Kolam Renang Bogor Raya | Kawasan Residensial Modern',
        'meta_description' => 'Jasa perawatan kolam renang rutin di Bogor Raya — kawasan residensial modern dengan kolam privat rumah tinggal. Jadwal terkoordinasi dan standar kebersihan tinggi.',
        'h1' => 'Jasa Perawatan Kolam Renang Bogor Raya',
        'intro' => 'Perawatan rutin kolam renang privat untuk rumah tinggal di kawasan residensial modern Bogor Raya, dengan jadwal kunjungan yang terkoordinasi rapi.',
        'content' => '<h2>Karakteristik Area Bogor Raya untuk Perawatan Kolam Renang</h2>'
            . '<p>Bogor Raya adalah kawasan residensial modern dengan tata kelola kompleks yang rapi, mencakup sistem keamanan dan akses yang terkontrol. Rumah-rumah di kawasan ini umumnya berukuran menengah hingga besar, dengan sebagian dilengkapi kolam renang privat sebagai fasilitas utama hunian.</p>'
            . '<p>Karena kawasan ini tertata sebagai kompleks residensial, akses untuk tim perawatan relatif mudah dan konsisten begitu jadwal kunjungan disepakati dengan pemilik rumah. Lingkungan yang lebih terbuka (tidak terlalu banyak pepohonan besar di sekitar kolam) membuat perawatan cenderung lebih fokus pada penjagaan kejernihan air dan sistem filtrasi dibanding penanganan kotoran dedaunan.</p>'
            . '<h2>Layanan Perawatan yang Kami Tawarkan di Bogor Raya</h2>'
            . '<ul>'
            . '<li><strong>Perawatan rutin terjadwal:</strong> pembersihan, penyeimbangan kimia air, dan pengecekan filtrasi sesuai jadwal mingguan atau bulanan yang disepakati.</li>'
            . '<li><strong>Perawatan sistem sirkulasi kolam privat:</strong> pengecekan pompa dan filter agar performa tetap optimal untuk kolam-kolam berukuran menengah hingga besar.</li>'
            . '<li><strong>Koordinasi jadwal dengan pengelola kawasan:</strong> memastikan kunjungan tim berjalan lancar sesuai aturan akses kompleks.</li>'
            . '</ul>'
            . '<p>Kami menyesuaikan paket perawatan dengan kebutuhan tiap rumah di Bogor Raya, baik untuk pemakaian harian keluarga maupun kolam yang lebih jarang digunakan.</p>',
        'faq' => [
            ['q' => 'Apakah perawatan kolam di Bogor Raya perlu koordinasi dengan pengelola kawasan?', 'a' => 'Untuk sebagian kompleks di Bogor Raya, ya — kami mengoordinasikan jadwal kunjungan dengan pemilik rumah dan, bila diperlukan, dengan pengelola kawasan agar akses tim berjalan lancar.'],
            ['q' => 'Berapa lama waktu yang dibutuhkan untuk satu kali kunjungan perawatan?', 'a' => 'Tergantung ukuran kolam dan kondisi air, umumnya satu kunjungan perawatan rutin membutuhkan waktu sekitar 1-2 jam.'],
            ['q' => 'Apakah tersedia paket perawatan bulanan untuk rumah di Bogor Raya?', 'a' => 'Tersedia. Kami menawarkan paket kontrak perawatan bulanan dengan jadwal kunjungan tetap sesuai kebutuhan Anda.']
        ]
    ],
    [
        'url_path' => '/area/karadenan/',
        'area_ref' => 'Karadenan',
        'title' => 'Perawatan Kolam Renang Karadenan',
        'meta_title' => 'Jasa Perawatan Kolam Renang Karadenan Cibinong | Terjadwal',
        'meta_description' => 'Jasa perawatan kolam renang rutin di Karadenan, Cibinong — kawasan perumahan berkembang dekat Jalan Raya Bogor. Kunjungan terjadwal untuk rumah tinggal dan properti komersial.',
        'h1' => 'Jasa Perawatan Kolam Renang Karadenan',
        'intro' => 'Perawatan rutin kolam renang untuk rumah tinggal dan properti komersial di Karadenan, Cibinong, kawasan yang terus berkembang dekat jalur utama Bogor-Jakarta.',
        'content' => '<h2>Karakteristik Area Karadenan untuk Perawatan Kolam Renang</h2>'
            . '<p>Karadenan merupakan kawasan di Cibinong yang terus berkembang sebagai area perumahan baru, berdekatan dengan Jalan Raya Bogor yang menjadi jalur utama penghubung Bogor dan Jakarta. Aktivitas lalu lintas yang cukup padat di sekitar jalur ini membuat debu dan partikel jalan raya lebih mudah terbawa angin ke kolam renang outdoor di sekitarnya.</p>'
            . '<p>Properti di Karadenan cukup beragam, mulai dari rumah tinggal di perumahan baru hingga bangunan komersial seperti ruko yang kadang memiliki fasilitas kolam renang kecil. Karena posisinya yang strategis di jalur utama, akses tim perawatan menuju area ini relatif mudah dan bisa dijadwalkan secara konsisten.</p>'
            . '<h2>Layanan Perawatan yang Kami Tawarkan di Karadenan</h2>'
            . '<ul>'
            . '<li><strong>Perawatan rutin terjadwal:</strong> pembersihan permukaan dan dasar kolam, penyeimbangan kimia air, serta perawatan filter sesuai jadwal yang disepakati.</li>'
            . '<li><strong>Penanganan debu dan partikel jalan raya:</strong> pembersihan tambahan untuk kolam outdoor yang lokasinya dekat jalur lalu lintas padat seperti Jalan Raya Bogor.</li>'
            . '<li><strong>Perawatan untuk properti komersial:</strong> jadwal kunjungan yang disesuaikan dengan jam operasional ruko atau properti komersial di kawasan ini.</li>'
            . '</ul>'
            . '<p>Kami memahami karakter Karadenan sebagai kawasan yang terus tumbuh, sehingga menyediakan jadwal perawatan yang fleksibel baik untuk rumah tinggal baru maupun properti komersial di sepanjang jalur utama.</p>',
        'faq' => [
            ['q' => 'Apakah kolam renang dekat Jalan Raya Bogor butuh perawatan lebih sering?', 'a' => 'Kolam outdoor yang berada dekat jalur lalu lintas padat seperti di Karadenan cenderung lebih cepat kotor akibat debu jalan raya, sehingga kami biasanya menyarankan frekuensi pembersihan permukaan yang sedikit lebih sering.'],
            ['q' => 'Apakah layanan perawatan tersedia untuk ruko atau properti komersial di Karadenan?', 'a' => 'Ya, kami melayani perawatan kolam renang untuk properti komersial seperti ruko, dengan jadwal kunjungan yang disesuaikan jam operasional properti tersebut.'],
            ['q' => 'Apakah area Karadenan termasuk mudah dijangkau tim perawatan?', 'a' => 'Ya, posisi Karadenan yang dekat dengan Jalan Raya Bogor membuat akses tim kami relatif mudah dan jadwal kunjungan bisa dijaga tetap konsisten.']
        ]
    ]
];

try {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'UPDATE pages SET title = :title, meta_title = :meta_title, meta_description = :meta_description,
         h1 = :h1, area_ref = :area_ref, intro = :intro, content = :content, faq_json = :faq_json,
         status = "published"
         WHERE url_path = :url_path'
    );

    $updated = [];
    $notFound = [];
    foreach ($pages as $p) {
        $stmt->execute([
            ':title' => $p['title'],
            ':meta_title' => $p['meta_title'],
            ':meta_description' => $p['meta_description'],
            ':h1' => $p['h1'],
            ':area_ref' => $p['area_ref'],
            ':intro' => $p['intro'],
            ':content' => $p['content'],
            ':faq_json' => json_encode($p['faq'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':url_path' => $p['url_path']
        ]);
        if ($stmt->rowCount() > 0) {
            $updated[] = $p['url_path'];
        } else {
            // rowCount() bisa 0 juga kalau isinya sama persis dengan yang lama
            // (dijalankan dua kali) — cek dulu apakah baris memang ada.
            $check = $pdo->prepare('SELECT id FROM pages WHERE url_path = :u');
            $check->execute([':u' => $p['url_path']]);
            if ($check->fetch()) {
                $updated[] = $p['url_path'] . ' (tidak berubah, sudah sama)';
            } else {
                $notFound[] = $p['url_path'];
            }
        }
    }

    respond(empty($notFound), empty($notFound) ? 'Konten 6 halaman area berhasil diperbarui.' : 'Sebagian halaman tidak ditemukan.', [
        'updated' => $updated,
        'not_found' => $notFound
    ]);
} catch (Exception $e) {
    respond(false, 'Gagal memperbarui konten: ' . $e->getMessage());
}
