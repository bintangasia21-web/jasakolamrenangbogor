/*
 * data.js — Sumber data utama situs Jasa Kolam Renang Bogor.
 * File ini dibaca oleh main.js (halaman publik) dan admin.js (panel admin).
 *
 * CARA KERJA:
 * 1. Nilai default disimpan di window.SITE_DATA di bawah ini.
 * 2. Panel admin (admin.html) menyimpan perubahan sementara ke localStorage
 *    browser (kunci "jkrb_data") untuk keperluan pratinjau langsung.
 * 3. Setelah admin menekan tombol "Unduh data.js" di panel admin, file baru
 *    hasil unduhan HARUS meng-upload/menimpa file assets/js/data.js ini di
 *    hosting agar perubahan tampil untuk SEMUA pengunjung (localStorage
 *    hanya berlaku di browser admin sendiri, bukan basis data server).
 *
 * Setiap item di "areas" memiliki lat/lng (titik peta) dan priority
 * (true = digambar sebagai zona lingkaran hijau transparan di peta).
 * Koordinat 4 area bawaan di bawah adalah perkiraan titik pusat wilayah,
 * bukan alamat presisi — sesuaikan lewat panel admin (klik-pilih di peta)
 * bila perlu titik yang lebih akurat.
 */
window.SITE_DATA = {
  business: {
    name: "Jasa Kolam Renang Bogor",
    legalName: "Jasa Kolam Renang Bogor",
    tagline: "Spesialis Pembuatan, Perawatan & Renovasi Kolam Renang di Bogor",
    description:
      "Jasa Kolam Renang Bogor melayani pembuatan kolam baru, perawatan rutin, renovasi, dan instalasi sistem air untuk rumah tinggal, villa, dan resort di wilayah Bogor dan sekitarnya.",
    phoneDisplay: "0822-1662-3388",
    phoneHref: "+6282216623388",
    whatsapp: "6282216623388",
    whatsappMessage: "Halo Jasa Kolam Renang Bogor, saya ingin bertanya tentang layanan kolam renang.",
    email: "info@jasakolamrenangbogor.com",
    addressLine: "Gedung Dewata, Cimahpar Kav. 10, Cimahpar, Bogor",
    city: "Bogor",
    region: "Jawa Barat",
    postalCode: "16110",
    country: "ID",
    hoursWeekday: "08.00 – 17.00",
    hoursWeekend: "08.00 – 15.00",
    mapsQuery: "Gedung Dewata, Cimahpar Kav. 10, Cimahpar, Bogor",
    mapsUrl: "https://maps.google.com/maps?q=Gedung%20Dewata%2C%20Cimahpar%20Kav.%2010%2C%20Cimahpar%2C%20Bogor&output=embed",
    priceRange: "$$",
    yearsExperience: 10,
    projectsDone: 350,
    domain: "https://www.jasakolamrenangbogor.com"
  },

  areas: [
    {
      name: "Sentul",
      link: "area-sentul.html",
      desc: "Kolam renang villa & hunian premium di kawasan perbukitan Sentul.",
      lat: -6.5730,
      lng: 106.8550,
      priority: true
    },
    {
      name: "Puncak",
      link: "area-puncak.html",
      desc: "Kolam renang tahan cuaca dingin & hujan untuk villa dan resort Puncak.",
      lat: -6.6960,
      lng: 106.9480,
      priority: true
    },
    {
      name: "Cibinong",
      link: "area-cibinong.html",
      desc: "Kolam renang keluarga & perumahan di pusat Kabupaten Bogor.",
      lat: -6.4820,
      lng: 106.8540,
      priority: true
    },
    {
      name: "Bogor Kota",
      link: "area-bogor-kota.html",
      desc: "Renovasi & perawatan kolam renang di kawasan padat Kota Bogor.",
      lat: -6.5971,
      lng: 106.8060,
      priority: true
    }
  ],

  faq: [
    {
      q: "Berapa lama waktu pengerjaan pembuatan kolam renang baru?",
      a: "Rata-rata pembuatan kolam renang baru berukuran standar rumah tinggal membutuhkan waktu 3–6 minggu, tergantung ukuran, desain, kondisi lahan, dan sistem filtrasi yang dipilih. Untuk kolam villa atau resort dengan desain khusus, waktu pengerjaan bisa lebih lama dan akan kami sampaikan secara rinci setelah survei lokasi."
    },
    {
      q: "Berapa kisaran biaya perawatan kolam renang rutin?",
      a: "Biaya perawatan rutin tergantung ukuran kolam, frekuensi kunjungan (mingguan/bulanan), dan kondisi sistem filtrasi yang ada. Kami menyediakan paket perawatan yang disesuaikan dengan kebutuhan dan anggaran Anda — silakan hubungi kami via WhatsApp untuk mendapatkan penawaran tanpa biaya survei awal."
    },
    {
      q: "Apakah Jasa Kolam Renang Bogor melayani wilayah di luar area yang tercantum?",
      a: "Selain Sentul, Puncak, Cibinong, dan Bogor Kota, kami juga melayani area sekitar seperti Ciawi, Cisarua, Gunung Putri, Citeureup, dan Parung. Hubungi kami untuk konfirmasi jangkauan layanan sesuai lokasi Anda."
    },
    {
      q: "Apakah ada garansi untuk pekerjaan renovasi dan perbaikan kolam?",
      a: "Ya. Setiap pekerjaan renovasi, perbaikan kebocoran, dan pembuatan kolam baru kami sertai garansi pengerjaan. Ketentuan dan lama garansi disampaikan secara tertulis dalam surat penawaran sebelum pekerjaan dimulai."
    },
    {
      q: "Bagaimana cara memesan jasa perawatan atau konsultasi pembuatan kolam?",
      a: "Anda cukup menekan tombol WhatsApp di situs ini atau menghubungi nomor kontak kami. Tim kami akan menjadwalkan survei lokasi, memberikan estimasi biaya, dan melanjutkan proses sesuai kesepakatan."
    },
    {
      q: "Apa saja yang termasuk dalam instalasi sistem air kolam renang?",
      a: "Instalasi sistem air mencakup pemasangan pompa sirkulasi, filter pasir/cartridge, sistem sanitasi (klorin/garam/ozone sesuai kebutuhan), plumbing inlet-outlet, hingga pengaturan sistem drainase dan pengisian air otomatis apabila diperlukan."
    }
  ],

  portfolio: [
    {
      title: "Kolam Renang Villa Modern",
      area: "Sentul",
      desc: "Pembuatan kolam renang infinity edge untuk villa di kawasan perbukitan.",
      color1: "#1478c8",
      color2: "#00b8d9"
    },
    {
      title: "Renovasi Kolam Resort",
      area: "Puncak",
      desc: "Renovasi total lapisan kolam dan sistem filtrasi tahan cuaca dingin.",
      color1: "#0a4a82",
      color2: "#4fadea"
    },
    {
      title: "Kolam Renang Keluarga",
      area: "Cibinong",
      desc: "Pembuatan kolam minimalis untuk hunian keluarga di perumahan.",
      color1: "#1e90e0",
      color2: "#073763"
    },
    {
      title: "Perawatan Kolam Rutin",
      area: "Bogor Kota",
      desc: "Program perawatan bulanan menjaga kejernihan air kolam hotel.",
      color1: "#00b8d9",
      color2: "#0f5ea8"
    },
    {
      title: "Instalasi Sistem Filtrasi",
      area: "Sentul",
      desc: "Pemasangan sistem sirkulasi dan sanitasi air kolam otomatis.",
      color1: "#0f5ea8",
      color2: "#1e90e0"
    },
    {
      title: "Perbaikan Kebocoran Kolam",
      area: "Bogor Kota",
      desc: "Perbaikan struktur dan waterproofing kolam renang lama.",
      color1: "#073763",
      color2: "#00b8d9"
    }
  ]
};
