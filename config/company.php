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
    'about' => "PT Zam Zam Khan menangani sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, perpajakan, serta desain logo dan label kemasan.\nLayanan tersedia bagi UMKM, usaha kuliner, produsen, dan badan usaha melalui konsultasi di kantor maupun WhatsApp.",
    'vision' => 'Menjadi mitra konsultasi bisnis halal yang membantu pelaku usaha membangun usaha yang legal, aman, dan berkelanjutan sesuai prinsip syariah.',
    'mission' => "Mendampingi UMK dan non-UMK dalam menata legalitas usaha.\nMembantu perencanaan jenis usaha serta pengembangan merek dan kemasan.\nMendukung pengurusan izin, sertifikasi, dan administrasi usaha sesuai kebutuhan.",
    'maps_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('Jl. MT. Haryono Gang 6B No.949, Dinoyo, Lowokwaru, Kota Malang'),
    // Endpoint embed final. URL gaya lama `?output=embed` di-redirect Google dengan
    // X-Frame-Options: SAMEORIGIN sehingga peta diblokir browser.
    'maps_embed' => 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s'.urlencode('Jl. MT Haryono Gang 6B No.949, Dinoyo, Lowokwaru, Kota Malang, Jawa Timur'),
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
        ['label' => 'Dokumentasi', 'anchor' => '#testimoni'],
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
        ['icon' => 'clipboard', 'title' => 'Alur Kerja Sejak Awal', 'text' => 'Kebutuhan dan dokumen diperiksa sebelum proses layanan dimulai.'],
        ['icon' => 'chat', 'title' => 'Informasi Mudah Dipahami', 'text' => 'Persyaratan, tahapan, dan tindak lanjut dijelaskan dengan bahasa yang jelas.'],
        ['icon' => 'users', 'title' => 'UMKM dan Badan Usaha', 'text' => 'Layanan tersedia untuk usaha kuliner, produsen, UMKM, dan badan usaha.'],
        ['icon' => 'shield', 'title' => 'Dokumen Lebih Tertib', 'text' => 'Administrasi legalitas dan sertifikasi disiapkan sesuai layanan yang dipilih.'],
        ['icon' => 'star', 'title' => 'Mendukung Kesiapan Produk', 'text' => 'Halal, BPOM, HAKI, dan identitas kemasan membantu memperkuat kesiapan produk.'],
        ['icon' => 'pin', 'title' => 'Kantor di Kota Malang', 'text' => 'Konsultasi dapat dilakukan melalui WhatsApp atau kunjungan ke kantor di Dinoyo.'],
    ],

    'stats' => [
        ['value' => '8+', 'label' => 'Jenis Layanan Pendampingan'],
        ['value' => '100%', 'label' => 'Pendampingan Terarah'],
        ['value' => 'UMKM', 'label' => 'Hingga Skala Usaha Besar'],
        ['value' => 'Malang', 'label' => 'Basis Layanan Konsultasi'],
    ],

    // Dokumentasi sertifikasi halal (slider). File di public/images/testimonials/.
    // Nama usaha hanya dicantumkan jika identitasnya terbaca jelas pada foto.
    'testimonials' => [
        ['img' => 'testi1.jpeg', 'title' => 'Penyerahan Sertifikat dan Label Halal', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi penyerahan dokumen sertifikat serta label halal kepada pelaku usaha.', 'alt' => 'Penyerahan sertifikat dan label halal kepada pelaku usaha'],
        ['img' => 'testi2.jpeg', 'title' => 'Serah Terima Sertifikat Halal', 'service' => 'Sertifikasi Halal', 'caption' => 'Pendamping dan pelaku usaha menunjukkan sertifikat serta identitas halal yang telah diterima.', 'alt' => 'Serah terima sertifikat halal bersama pelaku usaha'],
        ['img' => 'testi3.jpeg', 'title' => 'Sertifikat Halal untuk Usaha Kuliner', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi penyerahan sertifikat dan label halal di lokasi usaha kuliner.', 'alt' => 'Penyerahan sertifikat halal di lokasi usaha kuliner'],
        ['img' => 'testi4.jpeg', 'title' => 'Penyerahan Sertifikat Bersama Tim Usaha', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi bersama tim usaha setelah penyerahan dokumen sertifikat dan label halal.', 'alt' => 'Penyerahan sertifikat dan label halal bersama tim usaha'],
        ['img' => 'testi5.jpeg', 'title' => 'Penyerahan Sertifikat Halal untuk BLYSS', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi penyerahan sertifikat dan identitas halal di lokasi usaha BLYSS.', 'alt' => 'Penyerahan sertifikat halal di lokasi usaha BLYSS'],
        ['img' => 'testi6.jpeg', 'title' => 'Penyerahan Sertifikat Halal Mannamadu', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi penyerahan sertifikat dan label halal kepada pelaku usaha Mannamadu.', 'alt' => 'Penyerahan sertifikat halal kepada pelaku usaha Mannamadu'],
        ['img' => 'testi7.jpeg', 'title' => 'Sertifikasi Halal Ibis Styles Malang', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi penyerahan sertifikat dan label halal bersama tim Ibis Styles Malang.', 'alt' => 'Penyerahan sertifikat halal bersama tim Ibis Styles Malang'],
        ['img' => 'testi8.jpeg', 'title' => 'Dokumentasi Sertifikasi di Lokasi Produksi', 'service' => 'Sertifikasi Halal', 'caption' => 'Penyerahan dokumen halal bersama pelaku usaha dan pihak terkait di lokasi produksi.', 'alt' => 'Penyerahan dokumen halal di lokasi produksi'],
        ['img' => 'testi9.jpeg', 'title' => 'Penyerahan Dokumen Halal kepada Pelaku Usaha', 'service' => 'Sertifikasi Halal', 'caption' => 'Pelaku usaha menerima sertifikat dan materi informasi halal di lokasi kegiatan usaha.', 'alt' => 'Pelaku usaha menerima sertifikat dan informasi halal'],
        ['img' => 'testi10.jpeg', 'title' => 'Penyerahan Sertifikat dan Informasi Halal', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi bersama pelaku usaha saat menerima sertifikat dan materi informasi halal.', 'alt' => 'Penyerahan sertifikat dan informasi halal kepada pelaku usaha'],
        ['img' => 'testi11.jpeg', 'title' => 'Hotel Santika Premiere Malang Bersertifikat Halal', 'service' => 'Sertifikasi Halal', 'caption' => 'Dokumentasi penyerahan sertifikat halal untuk Hotel Santika Premiere Malang.', 'alt' => 'Hotel Santika Premiere Malang menerima sertifikat halal'],
    ],

    'faq' => [
        ['q' => 'Apakah PT Zam Zam Khan hanya melayani sertifikat halal?', 'a' => 'Tidak. Selain sertifikat halal, PT Zam Zam Khan juga melayani legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, serta desain logo dan label kemasan.'],
        ['q' => 'Apakah UMKM bisa berkonsultasi?', 'a' => 'Ya. Layanan dapat disesuaikan dengan kebutuhan UMKM maupun pelaku usaha yang lebih besar.'],
        ['q' => 'Apakah bisa konsultasi terlebih dahulu sebelum menentukan layanan?', 'a' => 'Ya. Calon klien dapat menghubungi kontak resmi untuk menjelaskan kebutuhan usaha terlebih dahulu.'],
        ['q' => 'Apakah tersedia pendampingan untuk usaha makanan dan minuman?', 'a' => 'Ya. Layanan mencakup pendampingan untuk pelaku usaha makanan dan minuman, termasuk kebutuhan halal, BPOM, label kemasan, dan legalitas usaha.'],
        ['q' => 'Bagaimana cara menghubungi PT Zam Zam Khan?', 'a' => 'Calon klien dapat menghubungi melalui WhatsApp, email, atau datang langsung ke alamat kantor yang tercantum pada website.'],
    ],
];
