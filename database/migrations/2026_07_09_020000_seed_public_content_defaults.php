<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        if (! DB::table('advantages')->exists()) {
            $items = [
                ['clipboard','Pendampingan dari Awal','Dibantu sejak konsultasi kebutuhan, pengecekan dokumen, hingga arahan proses lanjutan.'],
                ['chat','Informasi Jelas & Terarah','Setiap layanan dijelaskan dengan bahasa yang mudah dipahami oleh pelaku usaha.'],
                ['users','Ramah untuk UMKM','Cocok untuk UMKM, restoran, catering, cafe, produsen makanan, dan badan usaha.'],
                ['shield','Legalitas Lebih Tertata','Membantu usaha menjadi lebih rapi secara administrasi, legalitas, dan sertifikasi.'],
                ['star','Nilai Produk Lebih Kuat','Mendukung kepercayaan pelanggan melalui legalitas, halal, HAKI, BPOM, dan identitas kemasan.'],
                ['pin','Berbasis di Kota Malang','Memberikan konsultasi yang dekat, terarah, dan relevan bagi pelaku usaha.'],
            ];
            foreach ($items as $i => [$icon,$title,$description]) DB::table('advantages')->insert(compact('icon','title','description') + ['display_order'=>$i+1,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }
        if (! DB::table('statistics')->exists()) {
            foreach ([['8+','Jenis Layanan Pendampingan'],['100%','Pendampingan Terarah'],['UMKM','Hingga Skala Usaha Besar'],['Malang','Basis Layanan Konsultasi']] as $i => [$value,$label]) {
                DB::table('statistics')->insert(compact('value','label') + ['display_order'=>$i+1,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
            }
        }
        if (! DB::table('clients')->exists()) {
            $items = [['Madu Manna','mannamadu.jpg'],['Aria Hotel','ariahotel.jpg'],['Ibis Styles','ibisstylehotel.jpg'],['Blyss Cafe','blysscafe.jpg'],['The Aliante Hotel','aliantehotel.jpg'],['The 101 Hotel Malang OJ','101hotel.jpg'],['Ayam Geprek Express Saiki','saikigeprek.jpg'],['Hotel Santika Premiere Malang','hotelsantika.jpg']];
            foreach ($items as $i => [$name,$file]) DB::table('clients')->insert(['name'=>$name,'logo_path'=>'images/Logo/'.$file,'display_order'=>$i+1,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }
        if (! DB::table('testimonials')->exists()) {
            for ($i=1; $i<=11; $i++) DB::table('testimonials')->insert(['client_name'=>'Dokumentasi Klien '.$i,'service_name'=>'Sertifikasi Halal','content'=>'Dokumentasi pendampingan dan penyerahan sertifikat halal bersama PT Zam Zam Khan.','image_path'=>'images/testimonials/testi'.$i.'.jpeg','image_alt'=>'Dokumentasi pendampingan klien '.$i,'display_order'=>$i,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }
        foreach (['Makanan dan Minuman','Hospitality','Perdagangan','Jasa','Industri','Sertifikasi Halal','Legalitas Usaha'] as $name) {
            DB::table('business_categories')->insertOrIgnore(['name'=>$name,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }
    }

    public function down(): void
    {
        // Data awal sengaja tidak dihapus untuk mencegah kehilangan data yang mungkin sudah diedit admin.
    }
};
