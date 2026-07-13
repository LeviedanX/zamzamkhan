<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('hero_sections', 'badge_text')) {
                $table->string('badge_text', 120)->nullable()->after('image_path');
            }
            if (! Schema::hasColumn('hero_sections', 'trust_text')) {
                $table->string('trust_text', 160)->nullable()->after('badge_text');
            }
            if (! Schema::hasColumn('hero_sections', 'service_chips')) {
                $table->text('service_chips')->nullable()->after('trust_text');
            }
            if (! Schema::hasColumn('hero_sections', 'portrait_path')) {
                $table->string('portrait_path', 255)->nullable()->after('service_chips');
            }
            if (! Schema::hasColumn('hero_sections', 'portrait_alt')) {
                $table->string('portrait_alt', 180)->nullable()->after('portrait_path');
            }
            if (! Schema::hasColumn('hero_sections', 'portrait_role')) {
                $table->string('portrait_role', 80)->nullable()->after('portrait_alt');
            }
            if (! Schema::hasColumn('hero_sections', 'portrait_name')) {
                $table->string('portrait_name', 120)->nullable()->after('portrait_role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            foreach (['portrait_name', 'portrait_role', 'portrait_alt', 'portrait_path', 'service_chips', 'trust_text', 'badge_text'] as $column) {
                if (Schema::hasColumn('hero_sections', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
