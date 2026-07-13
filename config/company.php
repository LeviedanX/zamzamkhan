<?php

// Data statis PT Zam Zam Khan (Fase 1). Nanti dapat dipindah ke database (CMS).
$waNumber = '6281256059099'; // format internasional untuk link wa.me
$waText = rawurlencode('Halo PT Zam Zam Khan, saya ingin berkonsultasi mengenai layanan Anda.');

return [
    'name' => 'PT Zam Zam Khan',
    'brand' => 'Zam Zam Khan',
    'tagline' => 'Bisnis & Legal Konsultan',
    'city' => 'Malang',
    'phone_display' => '081256059099',
    'phone_raw' => '081256059099',
    'whatsapp_number' => $waNumber,
    'email' => 'pt.zamzamkhan@gmail.com',
    'address' => 'Jl. MT. Haryono Gang 6B No.949, Dinoyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65144',
    'operating_hours' => 'Senin–Jumat, 08.00–16.00 WIB',
    'about' => "PT Zam Zam Khan hadir sebagai mitra pendamping bagi pelaku usaha yang ingin menata legalitas, sertifikasi, dan identitas produknya secara lebih profesional. Kami membantu proses sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, perpajakan, hingga desain logo dan label kemasan.\nDengan pendekatan yang terarah, kami mendampingi UMKM, restoran, catering, produsen makanan, dan badan usaha agar memiliki dokumen usaha yang lebih tertata, legal, dan siap bersaing di pasar.",
    'vision' => 'Jadikan bisnis Anda lebih berkembang dan berkah dengan layanan konsultasi bisnis halal dari PT Zam Zam Khan. Kami hadir untuk memberikan solusi strategis sesuai prinsip syariah agar setiap langkah bisnis berjalan tepat, aman, halal, dan berkelanjutan.',
    'mission' => "Membantu pelaku usaha, baik UMK maupun non-UMK, agar berkembang secara legal dan mampu bersaing di dunia usaha.\nMemberikan pendampingan mulai dari tahap perencanaan jenis usaha dan pengembangan branding.\nMembantu proses perizinan dan kebutuhan legalitas usaha secara terarah agar usaha dapat tumbuh dan bersaing.",
    'maps_url' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Jl. MT. Haryono Gang 6B No.949, Dinoyo, Lowokwaru, Kota Malang'),
    // Endpoint embed final. URL gaya lama `?output=embed` di-redirect Google dengan
    // X-Frame-Options: SAMEORIGIN sehingga peta diblokir browser.
    'maps_embed' => 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode('Jl. MT Haryono Gang 6B No.949, Dinoyo, Lowokwaru, Kota Malang, Jawa Timur'),
    'whatsapp' => "https://wa.me/{$waNumber}?text={$waText}",

    'socials' => [
        ['label' => 'Instagram', 'handle' => 'pt.zamzamkhan', 'url' => 'https://instagram.com/pt.zamzamkhan'],
        ['label' => 'Facebook', 'handle' => 'Zam Zam Khan', 'url' => 'https://facebook.com/'],
        ['label' => 'TikTok', 'handle' => 'pt.zamzamkhan', 'url' => 'https://tiktok.com/@pt.zamzamkhan'],
    ],

    'nav' => [
        ['label' => 'Tentang', 'anchor' => '#tentang'],
        ['label' => 'Visi & Misi', 'anchor' => '#visi-misi'],
        ['label' => 'Layanan', 'anchor' => '#layanan'],
        ['label' => 'Keunggulan', 'anchor' => '#keunggulan'],
        ['label' => 'Artikel', 'anchor' => '#artikel'],
        ['label' => 'Agenda', 'anchor' => '#agenda'],
        ['label' => 'Testimoni', 'anchor' => '#testimoni'],
        ['label' => 'FAQ', 'anchor' => '#faq'],
        ['label' => 'Kontak', 'anchor' => '#kontak'],
    ],

    'services' => [
        [
            'icon' => 'halal',
            'slug' => 'sertifikat-halal',
            'title' => 'Sertifikat Halal Self-Declare',
            'desc' => 'Pendampingan sertifikasi halal Self-Declare bagi UMK yang memiliki NIB, menggunakan bahan yang dipastikan halal, dan menjalankan proses produksi sederhana.',
            'detail' => 'Pendampingan Sertifikat Halal Self-Declare bagi pelaku usaha mikro dan kecil (UMK) yang memenuhi kriteria. Layanan mencakup pemeriksaan kelayakan awal, penyiapan data produk dan bahan, penyusunan dokumen Sistem Jaminan Produk Halal (SJPH), pengajuan melalui SIHALAL, verifikasi dan validasi oleh Pendamping PPH, perbaikan dokumen bila diperlukan, hingga pemantauan penetapan halal dan penerbitan sertifikat elektronik oleh BPJPH.',
            'benefits' => [
                'Pemeriksaan awal kesesuaian usaha dan produk dengan kriteria Self-Declare.',
                'Pendampingan penyiapan data bahan, proses produksi, dan dokumen SJPH.',
                'Pendampingan pengajuan serta pemenuhan catatan melalui SIHALAL.',
                'Pemantauan proses sampai sertifikat halal elektronik diterbitkan BPJPH.',
            ],
            'suitable_for' => 'Pelaku usaha mikro dan kecil yang telah memiliki NIB, menggunakan bahan yang jelas status kehalalannya, menjalankan proses produksi sederhana, serta produk dan prosesnya memenuhi kriteria skema Self-Declare.',
            'workflow_steps' => [
                'Konsultasi awal dan pemeriksaan kelayakan skema Self-Declare',
                'Penyiapan NIB, data pelaku usaha, daftar produk, bahan, pemasok, dan proses produksi',
                'Penyusunan dokumen Sistem Jaminan Produk Halal (SJPH) dan pernyataan halal pelaku usaha',
                'Pembuatan atau pelengkapan akun serta pengajuan permohonan melalui SIHALAL',
                'Verifikasi dan validasi dokumen serta proses produk oleh Pendamping PPH',
                'Perbaikan dan pemenuhan dokumen apabila terdapat catatan hasil verifikasi',
                'Pengajuan hasil pendampingan untuk penetapan kehalalan oleh Komite Fatwa Produk Halal',
                'Penerbitan sertifikat halal elektronik oleh BPJPH dan arahan penggunaan label halal',
            ],
            'whatsapp_message' => 'Halo PT Zam Zam Khan, saya ingin konsultasi layanan Sertifikat Halal Self-Declare. Mohon dibantu pemeriksaan kelayakan, persyaratan dokumen, dan alur pendampingannya.',
        ],
        ...require __DIR__.'/service-details.php',
    ],

    'advantages' => [
        'Pendampingan dari tahap awal hingga proses selesai sesuai ruang lingkup layanan.',
        'Cocok untuk UMKM, restoran, catering, cafe, produsen makanan, dan pelaku usaha lainnya.',
        'Informasi layanan disampaikan secara jelas dan terarah.',
        'Membantu usaha menjadi lebih tertib secara legalitas dan administrasi.',
        'Mendukung peningkatan nilai jual melalui legalitas, sertifikasi, dan identitas kemasan.',
    ],

    'stats' => [
        ['value' => '8+', 'label' => 'Jenis Layanan Pendampingan'],
        ['value' => '100%', 'label' => 'Pendampingan Terarah'],
        ['value' => 'UMKM', 'label' => 'Hingga Skala Usaha Besar'],
        ['value' => 'Malang', 'label' => 'Basis Layanan Konsultasi'],
    ],

    // Testimoni / dokumentasi pendampingan (slider). File di public/images/testimonials/.
    // Untuk mengubah: edit array ini (img, title, service, caption). Alt otomatis dari title+caption.
    'testimonials' => [
        ['img' => 'testi1.jpeg',  'title' => 'Hotel Santika Premiere Malang', 'service' => 'Sertifikasi Halal',  'caption' => 'Dokumentasi pendampingan dan penyerahan sertifikat halal untuk mendukung layanan usaha yang lebih terpercaya.'],
        ['img' => 'testi2.jpeg',  'title' => 'Dokumentasi Klien Hospitality', 'service' => 'Sertifikasi Halal',  'caption' => 'Pendampingan kebutuhan sertifikasi halal untuk pelaku usaha dan layanan hospitality.'],
        ['img' => 'testi3.jpeg',  'title' => 'Geprek Express Saiki',          'service' => 'Sertifikasi Halal',  'caption' => 'Penyerahan sertifikat halal sebagai penguatan kepercayaan konsumen terhadap produk kuliner.'],
        ['img' => 'testi4.jpeg',  'title' => 'Malang Sari Camilan',           'service' => 'Sertifikasi Halal',  'caption' => 'Pendampingan sertifikasi halal untuk pelaku usaha makanan dan camilan.'],
        ['img' => 'testi5.jpeg',  'title' => 'Esesa Cookies',                 'service' => 'Sertifikasi Halal',  'caption' => 'Dokumentasi penyerahan sertifikat halal kepada pelaku usaha produk cookies.'],
        ['img' => 'testi6.jpeg',  'title' => 'UMKM Kuliner Malang',           'service' => 'Sertifikasi Halal',  'caption' => 'Pendampingan dokumen halal bagi pelaku usaha kuliner agar proses lebih terarah.'],
        ['img' => 'testi7.jpeg',  'title' => 'Pelaku Usaha Makanan',          'service' => 'Sertifikasi Halal',  'caption' => 'Penyerahan dokumen dan label halal sebagai bagian dari penguatan legalitas produk.'],
        ['img' => 'testi8.jpeg',  'title' => 'Klien Produk Kemasan',          'service' => 'Halal & Legalitas',  'caption' => 'Pendampingan kebutuhan dokumen usaha dan sertifikasi produk.'],
        ['img' => 'testi9.jpeg',  'title' => 'Dokumentasi Pendampingan UMKM', 'service' => 'Sertifikasi Halal',  'caption' => 'Momen pendampingan dan penyerahan sertifikat halal kepada pelaku UMKM.'],
        ['img' => 'testi10.jpeg', 'title' => 'Pelaku Usaha Lokal',            'service' => 'Sertifikasi Halal',  'caption' => 'Pendampingan proses sertifikasi agar produk lebih siap dan dipercaya pelanggan.'],
        ['img' => 'testi11.jpeg', 'title' => 'Klien PT Zam Zam Khan',         'service' => 'Sertifikasi Halal',  'caption' => 'Dokumentasi penyerahan sertifikat halal bersama tim pendamping.'],
    ],

    'faq' => [
        ['q' => 'Apakah PT Zam Zam Khan hanya melayani sertifikat halal?', 'a' => 'Tidak. Selain sertifikat halal, PT Zam Zam Khan juga melayani legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, serta desain logo dan label kemasan.'],
        ['q' => 'Apakah UMKM bisa berkonsultasi?', 'a' => 'Ya. Layanan dapat disesuaikan dengan kebutuhan UMKM maupun pelaku usaha yang lebih besar.'],
        ['q' => 'Apakah bisa konsultasi terlebih dahulu sebelum menentukan layanan?', 'a' => 'Ya. Calon klien dapat menghubungi kontak resmi untuk menjelaskan kebutuhan usaha terlebih dahulu.'],
        ['q' => 'Apakah tersedia pendampingan untuk usaha makanan dan minuman?', 'a' => 'Ya. Layanan mencakup pendampingan untuk pelaku usaha makanan dan minuman, termasuk kebutuhan halal, BPOM, label kemasan, dan legalitas usaha.'],
        ['q' => 'Bagaimana cara menghubungi PT Zam Zam Khan?', 'a' => 'Calon klien dapat menghubungi melalui WhatsApp, email, atau datang langsung ke alamat kantor yang tercantum pada website.'],
    ],
];
