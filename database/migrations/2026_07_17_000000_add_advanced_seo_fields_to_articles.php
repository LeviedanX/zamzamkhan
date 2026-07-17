<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('canonical_url')->nullable()->after('meta_description');
            $table->string('seo_robots', 40)->default('index, follow')->after('canonical_url');
            $table->string('og_title', 120)->nullable()->after('seo_robots');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image_path', 255)->nullable()->after('og_description');
            $table->boolean('exclude_from_sitemap')->default(false)->after('og_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'canonical_url',
                'seo_robots',
                'og_title',
                'og_description',
                'og_image_path',
                'exclude_from_sitemap',
            ]);
        });
    }
};
