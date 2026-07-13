<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive: menambah field profil/kontak untuk menjadikan SiteSetting
 * sebagai control center konten dasar public website.
 * Hanya menambah kolom nullable — tidak menghapus/rename/ubah kolom lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('site_settings', 'consultant_name')) {
                $table->string('consultant_name', 180)->nullable()->after('brand_name');
            }
            if (! Schema::hasColumn('site_settings', 'company_description')) {
                $table->text('company_description')->nullable()->after('tagline');
            }
            if (! Schema::hasColumn('site_settings', 'vision')) {
                $table->text('vision')->nullable()->after('company_description');
            }
            if (! Schema::hasColumn('site_settings', 'mission')) {
                $table->text('mission')->nullable()->after('vision');
            }
            if (! Schema::hasColumn('site_settings', 'operating_hours')) {
                $table->string('operating_hours', 255)->nullable()->after('address');
            }
            if (! Schema::hasColumn('site_settings', 'maps_url')) {
                $table->string('maps_url', 500)->nullable()->after('operating_hours');
            }
            if (! Schema::hasColumn('site_settings', 'maps_embed_url')) {
                $table->string('maps_embed_url', 500)->nullable()->after('maps_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            foreach (['consultant_name', 'company_description', 'vision', 'mission', 'operating_hours', 'maps_url', 'maps_embed_url'] as $col) {
                if (Schema::hasColumn('site_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
