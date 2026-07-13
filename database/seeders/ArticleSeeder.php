<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Aktivitas Perusahaan' => 'aktivitas-perusahaan',
            'Sertifikasi Halal' => 'sertifikasi-halal',
            'Legalitas Usaha' => 'legalitas-usaha',
            'BPOM' => 'bpom',
            'HAKI' => 'haki',
            'Perpajakan' => 'perpajakan',
            'Branding & Kemasan' => 'branding-kemasan',
        ];

        $ids = [];
        foreach ($categories as $name => $slug) {
            $ids[$slug] = ArticleCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name],
            )->id;
        }

        $articles = [
            [
                'title' => 'Perbedaan Sertifikat Halal Self Declare dan Reguler',
                'slug' => 'perbedaan-sertifikat-halal-self-declare-dan-reguler',
                'category' => 'sertifikasi-halal',
                'excerpt' => 'Kenali perbedaan mendasar antara jalur sertifikasi halal self declare dan reguler agar pelaku usaha dapat memilih skema yang paling sesuai dengan kondisi produknya.',
                'meta_title' => 'Perbedaan Sertifikat Halal Self Declare dan Reguler',
                'meta_description' => 'Panduan memahami perbedaan sertifikat halal self declare dan reguler untuk pelaku usaha dan UMKM di Malang.',
                'content' => <<<'TXT'
Sertifikasi halal menjadi salah satu kebutuhan penting bagi pelaku usaha, khususnya di sektor makanan dan minuman. Secara umum, terdapat dua jalur yang sering ditemui, yaitu jalur self declare dan jalur reguler. Memahami perbedaan keduanya membantu pelaku usaha memilih skema yang paling sesuai dengan kondisi produk dan proses produksinya.

Jalur self declare umumnya ditujukan untuk produk dengan proses sederhana dan bahan yang sudah jelas kehalalannya. Skema ini menekankan pernyataan pelaku usaha atas kehalalan produk dengan pendampingan pihak yang berwenang. Karena karakteristiknya, jalur ini biasanya lebih ringkas untuk usaha berskala kecil.

Sementara itu, jalur reguler umumnya diperuntukkan bagi produk dengan proses yang lebih kompleks, seperti restoran, katering, kafe, hingga produksi skala besar. Pada jalur ini, pemeriksaan terhadap bahan dan proses produksi dilakukan secara lebih menyeluruh sesuai ketentuan yang berlaku.

Pemilihan jalur yang tepat sebaiknya disesuaikan dengan jenis produk, bahan yang digunakan, serta kapasitas usaha. Konsultasi awal dapat membantu pelaku usaha mengidentifikasi skema yang paling relevan sebelum memulai proses.

PT Zam Zam Khan mendampingi pelaku usaha di Malang untuk memahami kebutuhan sertifikasi halal dan menyiapkan dokumen yang diperlukan sesuai ruang lingkup layanan.
TXT,
            ],
            [
                'title' => 'Panduan Pengurusan NIB untuk UMKM di Malang',
                'slug' => 'panduan-pengurusan-nib-untuk-umkm-di-malang',
                'category' => 'legalitas-usaha',
                'excerpt' => 'Nomor Induk Berusaha (NIB) adalah identitas resmi pelaku usaha. Simak gambaran umum manfaat dan tahapan pendampingannya untuk UMKM.',
                'meta_title' => 'Panduan Pengurusan NIB untuk UMKM di Malang',
                'meta_description' => 'Gambaran umum manfaat dan tahapan pengurusan NIB untuk UMKM serta pelaku usaha di Malang.',
                'content' => <<<'TXT'
Nomor Induk Berusaha atau NIB merupakan identitas resmi bagi pelaku usaha sekaligus dasar legalitas dalam menjalankan kegiatan usaha. Bagi UMKM, keberadaan NIB memberikan kejelasan status usaha dan mempermudah akses terhadap berbagai kebutuhan administrasi selanjutnya.

Secara umum, pengurusan NIB dimulai dari penyiapan data usaha dan dokumen dasar pelaku usaha. Kelengkapan data ini penting agar identitas usaha yang tercatat sesuai dengan kondisi sebenarnya. Setelah data siap, proses pengajuan dapat dilanjutkan sesuai ketentuan yang berlaku.

Bagi pelaku usaha yang baru memulai, tahap identifikasi kebutuhan menjadi langkah awal yang membantu menentukan bentuk usaha dan ruang lingkup kegiatan. Dengan begitu, legalitas yang dimiliki dapat menyesuaikan rencana pengembangan usaha ke depan.

Memiliki NIB juga menjadi bagian dari upaya membuat usaha lebih tertib secara administratif. Hal ini dapat mendukung kepercayaan mitra maupun pelanggan terhadap usaha yang dijalankan.

PT Zam Zam Khan membantu pendampingan pengurusan NIB bagi UMKM dan pelaku usaha di Malang, mulai dari identifikasi kebutuhan hingga penyiapan dokumen sesuai ruang lingkup layanan.
TXT,
            ],
            [
                'title' => 'Mengapa Merek Dagang dan HAKI Penting untuk Usaha Anda',
                'slug' => 'mengapa-merek-dagang-dan-haki-penting-untuk-usaha-anda',
                'category' => 'haki',
                'excerpt' => 'Perlindungan merek dan hak kekayaan intelektual membantu menjaga identitas serta nilai usaha Anda dalam jangka panjang.',
                'meta_title' => 'Mengapa Merek Dagang dan HAKI Penting untuk Usaha',
                'meta_description' => 'Alasan pentingnya perlindungan merek dagang dan HAKI bagi pelaku usaha, serta pendampingannya di Malang.',
                'content' => <<<'TXT'
Merek dagang merupakan salah satu identitas yang membedakan produk atau layanan sebuah usaha dari yang lain. Seiring pertumbuhan usaha, merek menjadi aset yang memiliki nilai tersendiri karena melekat pada reputasi dan kepercayaan pelanggan.

Perlindungan terhadap merek melalui hak kekayaan intelektual (HAKI) membantu menjaga identitas usaha agar tidak digunakan pihak lain tanpa hak. Langkah ini penting sebagai bagian dari strategi menjaga keberlanjutan dan nilai usaha dalam jangka panjang.

Selain merek, HAKI juga dapat mencakup perlindungan atas karya, desain, maupun aset intelektual lain yang dimiliki usaha. Dengan memahami cakupan ini, pelaku usaha dapat menentukan aset mana yang perlu diprioritaskan untuk dilindungi.

Proses pendaftaran umumnya diawali dengan identifikasi aset yang akan dilindungi dan penyiapan dokumen pendukung. Konsultasi awal membantu pelaku usaha memahami langkah yang paling sesuai dengan kebutuhannya.

PT Zam Zam Khan mendampingi pelaku usaha di Malang dalam memahami kebutuhan perlindungan merek dan HAKI serta menyiapkan dokumen sesuai ruang lingkup layanan.
TXT,
            ],
        ];

        foreach ($articles as $i => $a) {
            Article::updateOrCreate(
                ['slug' => $a['slug']],
                [
                    'article_category_id' => $ids[$a['category']] ?? null,
                    'title' => $a['title'],
                    'excerpt' => $a['excerpt'],
                    'content' => $a['content'],
                    'status' => 'published',
                    'published_at' => now()->subDays($i * 3),
                    'meta_title' => $a['meta_title'],
                    'meta_description' => $a['meta_description'],
                ],
            );
        }
    }
}
