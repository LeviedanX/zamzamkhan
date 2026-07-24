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
                'excerpt' => 'Self Declare dan reguler memiliki kriteria serta proses pemeriksaan yang berbeda. Kenali faktor penentunya sebelum mengajukan sertifikasi halal.',
                'meta_title' => 'Perbedaan Sertifikat Halal Self Declare dan Reguler',
                'meta_description' => 'Panduan memahami perbedaan sertifikat halal self declare dan reguler untuk pelaku usaha dan UMKM di Malang.',
                'content' => <<<'TXT'
Self Declare ditujukan bagi pelaku UMK yang produk, bahan, dan proses produksinya memenuhi kriteria skema tersebut. Pelaku usaha perlu memiliki NIB, menggunakan bahan yang dipastikan halal, dan menjalankan proses produksi sederhana.

Pada jalur ini, Pendamping Proses Produk Halal melakukan verifikasi dan validasi. Data produk, bahan, pemasok, proses produksi, serta dokumen Sistem Jaminan Produk Halal perlu disiapkan sebelum pengajuan.

Jalur reguler digunakan untuk produk yang masih memerlukan pemeriksaan atau pengujian kehalalan. Prosesnya melibatkan Lembaga Pemeriksa Halal dan auditor halal.

Skema tidak ditentukan hanya dari ukuran usaha. Jenis produk, bahan, fasilitas, dan proses produksi juga perlu diperiksa. PT Zam Zam Khan dapat membantu pemeriksaan awal dokumen serta menjelaskan jalur pengajuan yang sesuai dengan kondisi usaha.
TXT,
            ],
            [
                'title' => 'Panduan Pengurusan NIB untuk UMKM di Malang',
                'slug' => 'panduan-pengurusan-nib-untuk-umkm-di-malang',
                'category' => 'legalitas-usaha',
                'excerpt' => 'NIB merupakan identitas resmi pelaku usaha. Data usaha, KBLI, dan tingkat risiko kegiatan menentukan kebutuhan izin berikutnya.',
                'meta_title' => 'Panduan Pengurusan NIB untuk UMKM di Malang',
                'meta_description' => 'Gambaran umum manfaat dan tahapan pengurusan NIB untuk UMKM serta pelaku usaha di Malang.',
                'content' => <<<'TXT'
Nomor Induk Berusaha atau NIB merupakan identitas resmi untuk memulai atau menjalankan usaha di Indonesia. Pengajuannya dilakukan melalui sistem OSS.

Sebelum mendaftar, pelaku usaha perlu menyiapkan identitas, alamat, kegiatan usaha, lokasi, dan data pendukung lain. Pemilihan KBLI harus sesuai dengan kegiatan yang benar-benar dijalankan karena data tersebut memengaruhi klasifikasi usaha.

Perizinan berusaha menggunakan pendekatan berbasis risiko. Tingkat risiko kegiatan menentukan apakah NIB sudah mencukupi atau masih ada sertifikat standar maupun izin lain yang harus dipenuhi.

Periksa kembali seluruh data sebelum pengajuan agar identitas usaha tidak keliru. PT Zam Zam Khan dapat membantu mengidentifikasi kebutuhan, menyiapkan data, dan mendampingi proses pengurusan NIB sesuai ruang lingkup layanan.
TXT,
            ],
            [
                'title' => 'Mengapa Merek Dagang dan HAKI Penting untuk Usaha Anda',
                'slug' => 'mengapa-merek-dagang-dan-haki-penting-untuk-usaha-anda',
                'category' => 'haki',
                'excerpt' => 'Pendaftaran merek memberi dasar perlindungan hukum atas identitas produk atau jasa. Pemeriksaan awal membantu mengurangi risiko penolakan.',
                'meta_title' => 'Mengapa Merek Dagang dan HAKI Penting untuk Usaha',
                'meta_description' => 'Alasan pentingnya perlindungan merek dagang dan HAKI bagi pelaku usaha, serta pendampingannya di Malang.',
                'content' => <<<'TXT'
Merek membedakan barang atau jasa sebuah usaha dari milik pihak lain. Nama, logo, atau unsur pembeda yang digunakan dalam perdagangan belum otomatis memberi hak eksklusif kepada pemiliknya.

Di Indonesia, hak eksklusif atas merek diperoleh melalui pendaftaran. Karena itu, pemeriksaan awal perlu dilakukan sebelum nama atau logo dipakai lebih luas.

Pendaftaran tetap melalui proses pemeriksaan. Persamaan dengan merek yang sudah terdaftar, pilihan kelas yang tidak tepat, atau unsur yang tidak dapat didaftarkan dapat memengaruhi hasil permohonan.

Istilah kekayaan intelektual juga mencakup objek lain, sehingga jenis perlindungannya perlu disesuaikan dengan aset yang dimiliki. PT Zam Zam Khan dapat membantu penelusuran awal, pemilihan kelas, dan penyiapan dokumen permohonan merek sesuai ruang lingkup layanan.
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
