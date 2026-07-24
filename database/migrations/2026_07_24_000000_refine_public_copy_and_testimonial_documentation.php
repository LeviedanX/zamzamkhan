<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')
                ->where('company_description', $this->oldCompanyDescription())
                ->update([
                    'company_description' => $this->newCompanyDescription(),
                    'updated_at' => $now,
                ]);

            DB::table('site_settings')
                ->where('vision', $this->oldVision())
                ->update([
                    'vision' => $this->newVision(),
                    'updated_at' => $now,
                ]);

            DB::table('site_settings')
                ->where('mission', $this->oldMission())
                ->update([
                    'mission' => $this->newMission(),
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('hero_sections')) {
            DB::table('hero_sections')
                ->where('subtitle', $this->oldHeroSubtitle())
                ->update([
                    'subtitle' => $this->newHeroSubtitle(),
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('advantages')) {
            foreach ($this->advantages() as $advantage) {
                DB::table('advantages')
                    ->where('title', $advantage['old_title'])
                    ->where('description', $advantage['old_description'])
                    ->update([
                        'title' => $advantage['new_title'],
                        'description' => $advantage['new_description'],
                        'updated_at' => $now,
                    ]);
            }
        }

        if (Schema::hasTable('testimonials')) {
            foreach ($this->testimonials() as $index => $testimonial) {
                $number = $index + 1;

                DB::table('testimonials')
                    ->where('client_name', 'Dokumentasi Klien '.$number)
                    ->where('content', $this->oldTestimonialContent())
                    ->where('image_path', 'images/testimonials/testi'.$number.'.jpeg')
                    ->update([
                        'client_name' => $testimonial['title'],
                        'service_name' => 'Sertifikasi Halal',
                        'content' => $testimonial['caption'],
                        'image_alt' => $testimonial['alt'],
                        'updated_at' => $now,
                    ]);
            }
        }

        if (Schema::hasTable('articles')) {
            foreach ($this->articles() as $article) {
                DB::table('articles')
                    ->where('slug', $article['slug'])
                    ->where('excerpt', $article['old_excerpt'])
                    ->where('content', $article['old_content'])
                    ->update([
                        'excerpt' => $article['new_excerpt'],
                        'content' => $article['new_content'],
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        $now = now();

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')
                ->where('company_description', $this->newCompanyDescription())
                ->update([
                    'company_description' => $this->oldCompanyDescription(),
                    'updated_at' => $now,
                ]);

            DB::table('site_settings')
                ->where('vision', $this->newVision())
                ->update([
                    'vision' => $this->oldVision(),
                    'updated_at' => $now,
                ]);

            DB::table('site_settings')
                ->where('mission', $this->newMission())
                ->update([
                    'mission' => $this->oldMission(),
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('hero_sections')) {
            DB::table('hero_sections')
                ->where('subtitle', $this->newHeroSubtitle())
                ->update([
                    'subtitle' => $this->oldHeroSubtitle(),
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('advantages')) {
            foreach ($this->advantages() as $advantage) {
                DB::table('advantages')
                    ->where('title', $advantage['new_title'])
                    ->where('description', $advantage['new_description'])
                    ->update([
                        'title' => $advantage['old_title'],
                        'description' => $advantage['old_description'],
                        'updated_at' => $now,
                    ]);
            }
        }

        if (Schema::hasTable('testimonials')) {
            foreach ($this->testimonials() as $index => $testimonial) {
                $number = $index + 1;

                DB::table('testimonials')
                    ->where('client_name', $testimonial['title'])
                    ->where('content', $testimonial['caption'])
                    ->where('image_path', 'images/testimonials/testi'.$number.'.jpeg')
                    ->update([
                        'client_name' => 'Dokumentasi Klien '.$number,
                        'service_name' => 'Sertifikasi Halal',
                        'content' => $this->oldTestimonialContent(),
                        'image_alt' => 'Dokumentasi pendampingan klien '.$number,
                        'updated_at' => $now,
                    ]);
            }
        }

        if (Schema::hasTable('articles')) {
            foreach ($this->articles() as $article) {
                DB::table('articles')
                    ->where('slug', $article['slug'])
                    ->where('excerpt', $article['new_excerpt'])
                    ->where('content', $article['new_content'])
                    ->update([
                        'excerpt' => $article['old_excerpt'],
                        'content' => $article['old_content'],
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    private function oldCompanyDescription(): string
    {
        return "PT Zam Zam Khan hadir sebagai mitra pendamping bagi pelaku usaha yang ingin menata legalitas, sertifikasi, dan identitas produknya secara lebih profesional. Kami membantu proses sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, perpajakan, hingga desain logo dan label kemasan.\nDengan pendekatan yang terarah, kami mendampingi UMKM, restoran, catering, produsen makanan, dan badan usaha agar memiliki dokumen usaha yang lebih tertata, legal, dan siap bersaing di pasar.";
    }

    private function newCompanyDescription(): string
    {
        return "PT Zam Zam Khan menangani sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, perpajakan, serta desain logo dan label kemasan.\nLayanan tersedia bagi UMKM, usaha kuliner, produsen, dan badan usaha melalui konsultasi di kantor maupun WhatsApp.";
    }

    private function oldVision(): string
    {
        return 'Jadikan bisnis Anda lebih berkembang dan berkah dengan layanan konsultasi bisnis halal dari PT Zam Zam Khan. Kami hadir untuk memberikan solusi strategis sesuai prinsip syariah agar setiap langkah bisnis berjalan tepat, aman, halal, dan berkelanjutan.';
    }

    private function newVision(): string
    {
        return 'Menjadi mitra konsultasi bisnis halal yang membantu pelaku usaha membangun usaha yang legal, aman, dan berkelanjutan sesuai prinsip syariah.';
    }

    private function oldMission(): string
    {
        return "Membantu pelaku usaha, baik UMK maupun non-UMK, agar berkembang secara legal dan mampu bersaing di dunia usaha.\nMemberikan pendampingan mulai dari tahap perencanaan jenis usaha dan pengembangan branding.\nMembantu proses perizinan dan kebutuhan legalitas usaha secara terarah agar usaha dapat tumbuh dan bersaing.";
    }

    private function newMission(): string
    {
        return "Mendampingi UMK dan non-UMK dalam menata legalitas usaha.\nMembantu perencanaan jenis usaha serta pengembangan merek dan kemasan.\nMendukung pengurusan izin, sertifikasi, dan administrasi usaha sesuai kebutuhan.";
    }

    private function oldHeroSubtitle(): string
    {
        return 'PT Zam Zam Khan membantu pelaku usaha dalam pendampingan sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, serta desain logo dan label kemasan produk.';
    }

    private function newHeroSubtitle(): string
    {
        return 'Layanan sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, serta desain logo dan label kemasan untuk pelaku usaha.';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function advantages(): array
    {
        return [
            [
                'old_title' => 'Pendampingan dari Awal',
                'old_description' => 'Dibantu sejak konsultasi kebutuhan, pengecekan dokumen, hingga arahan proses lanjutan.',
                'new_title' => 'Alur Kerja Sejak Awal',
                'new_description' => 'Kebutuhan dan dokumen diperiksa sebelum proses layanan dimulai.',
            ],
            [
                'old_title' => 'Informasi Jelas & Terarah',
                'old_description' => 'Setiap layanan dijelaskan dengan bahasa yang mudah dipahami oleh pelaku usaha.',
                'new_title' => 'Informasi Mudah Dipahami',
                'new_description' => 'Persyaratan, tahapan, dan tindak lanjut dijelaskan dengan bahasa yang jelas.',
            ],
            [
                'old_title' => 'Ramah untuk UMKM',
                'old_description' => 'Cocok untuk UMKM, restoran, catering, cafe, produsen makanan, dan badan usaha.',
                'new_title' => 'UMKM dan Badan Usaha',
                'new_description' => 'Layanan tersedia untuk usaha kuliner, produsen, UMKM, dan badan usaha.',
            ],
            [
                'old_title' => 'Legalitas Lebih Tertata',
                'old_description' => 'Membantu usaha menjadi lebih rapi secara administrasi, legalitas, dan sertifikasi.',
                'new_title' => 'Dokumen Lebih Tertib',
                'new_description' => 'Administrasi legalitas dan sertifikasi disiapkan sesuai layanan yang dipilih.',
            ],
            [
                'old_title' => 'Nilai Produk Lebih Kuat',
                'old_description' => 'Mendukung kepercayaan pelanggan melalui legalitas, halal, HAKI, BPOM, dan identitas kemasan.',
                'new_title' => 'Mendukung Kesiapan Produk',
                'new_description' => 'Halal, BPOM, HAKI, dan identitas kemasan membantu memperkuat kesiapan produk.',
            ],
            [
                'old_title' => 'Berbasis di Kota Malang',
                'old_description' => 'Memberikan konsultasi yang dekat, terarah, dan relevan bagi pelaku usaha.',
                'new_title' => 'Kantor di Kota Malang',
                'new_description' => 'Konsultasi dapat dilakukan melalui WhatsApp atau kunjungan ke kantor di Dinoyo.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function testimonials(): array
    {
        return [
            [
                'title' => 'Penyerahan Sertifikat dan Label Halal',
                'caption' => 'Dokumentasi penyerahan dokumen sertifikat serta label halal kepada pelaku usaha.',
                'alt' => 'Penyerahan sertifikat dan label halal kepada pelaku usaha',
            ],
            [
                'title' => 'Serah Terima Sertifikat Halal',
                'caption' => 'Pendamping dan pelaku usaha menunjukkan sertifikat serta identitas halal yang telah diterima.',
                'alt' => 'Serah terima sertifikat halal bersama pelaku usaha',
            ],
            [
                'title' => 'Sertifikat Halal untuk Usaha Kuliner',
                'caption' => 'Dokumentasi penyerahan sertifikat dan label halal di lokasi usaha kuliner.',
                'alt' => 'Penyerahan sertifikat halal di lokasi usaha kuliner',
            ],
            [
                'title' => 'Penyerahan Sertifikat Bersama Tim Usaha',
                'caption' => 'Dokumentasi bersama tim usaha setelah penyerahan dokumen sertifikat dan label halal.',
                'alt' => 'Penyerahan sertifikat dan label halal bersama tim usaha',
            ],
            [
                'title' => 'Penyerahan Sertifikat Halal untuk BLYSS',
                'caption' => 'Dokumentasi penyerahan sertifikat dan identitas halal di lokasi usaha BLYSS.',
                'alt' => 'Penyerahan sertifikat halal di lokasi usaha BLYSS',
            ],
            [
                'title' => 'Penyerahan Sertifikat Halal Mannamadu',
                'caption' => 'Dokumentasi penyerahan sertifikat dan label halal kepada pelaku usaha Mannamadu.',
                'alt' => 'Penyerahan sertifikat halal kepada pelaku usaha Mannamadu',
            ],
            [
                'title' => 'Sertifikasi Halal Ibis Styles Malang',
                'caption' => 'Dokumentasi penyerahan sertifikat dan label halal bersama tim Ibis Styles Malang.',
                'alt' => 'Penyerahan sertifikat halal bersama tim Ibis Styles Malang',
            ],
            [
                'title' => 'Dokumentasi Sertifikasi di Lokasi Produksi',
                'caption' => 'Penyerahan dokumen halal bersama pelaku usaha dan pihak terkait di lokasi produksi.',
                'alt' => 'Penyerahan dokumen halal di lokasi produksi',
            ],
            [
                'title' => 'Penyerahan Dokumen Halal kepada Pelaku Usaha',
                'caption' => 'Pelaku usaha menerima sertifikat dan materi informasi halal di lokasi kegiatan usaha.',
                'alt' => 'Pelaku usaha menerima sertifikat dan informasi halal',
            ],
            [
                'title' => 'Penyerahan Sertifikat dan Informasi Halal',
                'caption' => 'Dokumentasi bersama pelaku usaha saat menerima sertifikat dan materi informasi halal.',
                'alt' => 'Penyerahan sertifikat dan informasi halal kepada pelaku usaha',
            ],
            [
                'title' => 'Hotel Santika Premiere Malang Bersertifikat Halal',
                'caption' => 'Dokumentasi penyerahan sertifikat halal untuk Hotel Santika Premiere Malang.',
                'alt' => 'Hotel Santika Premiere Malang menerima sertifikat halal',
            ],
        ];
    }

    private function oldTestimonialContent(): string
    {
        return 'Dokumentasi pendampingan dan penyerahan sertifikat halal bersama PT Zam Zam Khan.';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function articles(): array
    {
        return [
            [
                'slug' => 'perbedaan-sertifikat-halal-self-declare-dan-reguler',
                'old_excerpt' => 'Kenali perbedaan mendasar antara jalur sertifikasi halal self declare dan reguler agar pelaku usaha dapat memilih skema yang paling sesuai dengan kondisi produknya.',
                'new_excerpt' => 'Self Declare dan reguler memiliki kriteria serta proses pemeriksaan yang berbeda. Kenali faktor penentunya sebelum mengajukan sertifikasi halal.',
                'old_content' => <<<'TXT'
Sertifikasi halal menjadi salah satu kebutuhan penting bagi pelaku usaha, khususnya di sektor makanan dan minuman. Secara umum, terdapat dua jalur yang sering ditemui, yaitu jalur self declare dan jalur reguler. Memahami perbedaan keduanya membantu pelaku usaha memilih skema yang paling sesuai dengan kondisi produk dan proses produksinya.

Jalur self declare umumnya ditujukan untuk produk dengan proses sederhana dan bahan yang sudah jelas kehalalannya. Skema ini menekankan pernyataan pelaku usaha atas kehalalan produk dengan pendampingan pihak yang berwenang. Karena karakteristiknya, jalur ini biasanya lebih ringkas untuk usaha berskala kecil.

Sementara itu, jalur reguler umumnya diperuntukkan bagi produk dengan proses yang lebih kompleks, seperti restoran, katering, kafe, hingga produksi skala besar. Pada jalur ini, pemeriksaan terhadap bahan dan proses produksi dilakukan secara lebih menyeluruh sesuai ketentuan yang berlaku.

Pemilihan jalur yang tepat sebaiknya disesuaikan dengan jenis produk, bahan yang digunakan, serta kapasitas usaha. Konsultasi awal dapat membantu pelaku usaha mengidentifikasi skema yang paling relevan sebelum memulai proses.

PT Zam Zam Khan mendampingi pelaku usaha di Malang untuk memahami kebutuhan sertifikasi halal dan menyiapkan dokumen yang diperlukan sesuai ruang lingkup layanan.
TXT,
                'new_content' => <<<'TXT'
Self Declare ditujukan bagi pelaku UMK yang produk, bahan, dan proses produksinya memenuhi kriteria skema tersebut. Pelaku usaha perlu memiliki NIB, menggunakan bahan yang dipastikan halal, dan menjalankan proses produksi sederhana.

Pada jalur ini, Pendamping Proses Produk Halal melakukan verifikasi dan validasi. Data produk, bahan, pemasok, proses produksi, serta dokumen Sistem Jaminan Produk Halal perlu disiapkan sebelum pengajuan.

Jalur reguler digunakan untuk produk yang masih memerlukan pemeriksaan atau pengujian kehalalan. Prosesnya melibatkan Lembaga Pemeriksa Halal dan auditor halal.

Skema tidak ditentukan hanya dari ukuran usaha. Jenis produk, bahan, fasilitas, dan proses produksi juga perlu diperiksa. PT Zam Zam Khan dapat membantu pemeriksaan awal dokumen serta menjelaskan jalur pengajuan yang sesuai dengan kondisi usaha.
TXT,
            ],
            [
                'slug' => 'panduan-pengurusan-nib-untuk-umkm-di-malang',
                'old_excerpt' => 'Nomor Induk Berusaha (NIB) adalah identitas resmi pelaku usaha. Simak gambaran umum manfaat dan tahapan pendampingannya untuk UMKM.',
                'new_excerpt' => 'NIB merupakan identitas resmi pelaku usaha. Data usaha, KBLI, dan tingkat risiko kegiatan menentukan kebutuhan izin berikutnya.',
                'old_content' => <<<'TXT'
Nomor Induk Berusaha atau NIB merupakan identitas resmi bagi pelaku usaha sekaligus dasar legalitas dalam menjalankan kegiatan usaha. Bagi UMKM, keberadaan NIB memberikan kejelasan status usaha dan mempermudah akses terhadap berbagai kebutuhan administrasi selanjutnya.

Secara umum, pengurusan NIB dimulai dari penyiapan data usaha dan dokumen dasar pelaku usaha. Kelengkapan data ini penting agar identitas usaha yang tercatat sesuai dengan kondisi sebenarnya. Setelah data siap, proses pengajuan dapat dilanjutkan sesuai ketentuan yang berlaku.

Bagi pelaku usaha yang baru memulai, tahap identifikasi kebutuhan menjadi langkah awal yang membantu menentukan bentuk usaha dan ruang lingkup kegiatan. Dengan begitu, legalitas yang dimiliki dapat menyesuaikan rencana pengembangan usaha ke depan.

Memiliki NIB juga menjadi bagian dari upaya membuat usaha lebih tertib secara administratif. Hal ini dapat mendukung kepercayaan mitra maupun pelanggan terhadap usaha yang dijalankan.

PT Zam Zam Khan membantu pendampingan pengurusan NIB bagi UMKM dan pelaku usaha di Malang, mulai dari identifikasi kebutuhan hingga penyiapan dokumen sesuai ruang lingkup layanan.
TXT,
                'new_content' => <<<'TXT'
Nomor Induk Berusaha atau NIB merupakan identitas resmi untuk memulai atau menjalankan usaha di Indonesia. Pengajuannya dilakukan melalui sistem OSS.

Sebelum mendaftar, pelaku usaha perlu menyiapkan identitas, alamat, kegiatan usaha, lokasi, dan data pendukung lain. Pemilihan KBLI harus sesuai dengan kegiatan yang benar-benar dijalankan karena data tersebut memengaruhi klasifikasi usaha.

Perizinan berusaha menggunakan pendekatan berbasis risiko. Tingkat risiko kegiatan menentukan apakah NIB sudah mencukupi atau masih ada sertifikat standar maupun izin lain yang harus dipenuhi.

Periksa kembali seluruh data sebelum pengajuan agar identitas usaha tidak keliru. PT Zam Zam Khan dapat membantu mengidentifikasi kebutuhan, menyiapkan data, dan mendampingi proses pengurusan NIB sesuai ruang lingkup layanan.
TXT,
            ],
            [
                'slug' => 'mengapa-merek-dagang-dan-haki-penting-untuk-usaha-anda',
                'old_excerpt' => 'Perlindungan merek dan hak kekayaan intelektual membantu menjaga identitas serta nilai usaha Anda dalam jangka panjang.',
                'new_excerpt' => 'Pendaftaran merek memberi dasar perlindungan hukum atas identitas produk atau jasa. Pemeriksaan awal membantu mengurangi risiko penolakan.',
                'old_content' => <<<'TXT'
Merek dagang merupakan salah satu identitas yang membedakan produk atau layanan sebuah usaha dari yang lain. Seiring pertumbuhan usaha, merek menjadi aset yang memiliki nilai tersendiri karena melekat pada reputasi dan kepercayaan pelanggan.

Perlindungan terhadap merek melalui hak kekayaan intelektual (HAKI) membantu menjaga identitas usaha agar tidak digunakan pihak lain tanpa hak. Langkah ini penting sebagai bagian dari strategi menjaga keberlanjutan dan nilai usaha dalam jangka panjang.

Selain merek, HAKI juga dapat mencakup perlindungan atas karya, desain, maupun aset intelektual lain yang dimiliki usaha. Dengan memahami cakupan ini, pelaku usaha dapat menentukan aset mana yang perlu diprioritaskan untuk dilindungi.

Proses pendaftaran umumnya diawali dengan identifikasi aset yang akan dilindungi dan penyiapan dokumen pendukung. Konsultasi awal membantu pelaku usaha memahami langkah yang paling sesuai dengan kebutuhannya.

PT Zam Zam Khan mendampingi pelaku usaha di Malang dalam memahami kebutuhan perlindungan merek dan HAKI serta menyiapkan dokumen sesuai ruang lingkup layanan.
TXT,
                'new_content' => <<<'TXT'
Merek membedakan barang atau jasa sebuah usaha dari milik pihak lain. Nama, logo, atau unsur pembeda yang digunakan dalam perdagangan belum otomatis memberi hak eksklusif kepada pemiliknya.

Di Indonesia, hak eksklusif atas merek diperoleh melalui pendaftaran. Karena itu, pemeriksaan awal perlu dilakukan sebelum nama atau logo dipakai lebih luas.

Pendaftaran tetap melalui proses pemeriksaan. Persamaan dengan merek yang sudah terdaftar, pilihan kelas yang tidak tepat, atau unsur yang tidak dapat didaftarkan dapat memengaruhi hasil permohonan.

Istilah kekayaan intelektual juga mencakup objek lain, sehingga jenis perlindungannya perlu disesuaikan dengan aset yang dimiliki. PT Zam Zam Khan dapat membantu penelusuran awal, pemilihan kelas, dan penyiapan dokumen permohonan merek sesuai ruang lingkup layanan.
TXT,
            ],
        ];
    }
};
