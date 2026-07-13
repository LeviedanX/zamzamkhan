<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->assertSingletonReady('site_settings');
        $this->assertSingletonReady('hero_sections');
        $this->normalizeOrder('services');
        $this->normalizeOrder('faqs');

        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique('site_settings_singleton_unique');
        });
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique('hero_sections_singleton_unique');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->index(['is_active', 'display_order'], 'services_active_order_index');
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->index(['is_active', 'display_order'], 'faqs_active_order_index');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE articles ADD CONSTRAINT articles_status_check CHECK (status IN ('draft', 'published'))");
            DB::statement("ALTER TABLE business_applications ADD CONSTRAINT applications_type_check CHECK (applicant_type IN ('company', 'individual'))");
            DB::statement("ALTER TABLE business_applications ADD CONSTRAINT applications_status_check CHECK (process_status IN ('Penawaran', 'Kontrak', 'Penyusunan SJPH', 'Audit Eksternal', 'Sidang Fatwa', 'Sertifikat Terbit', 'Ditunda', 'Batal'))");
            DB::statement("ALTER TABLE business_application_status_histories ADD CONSTRAINT application_history_new_status_check CHECK (new_status IN ('Penawaran', 'Kontrak', 'Penyusunan SJPH', 'Audit Eksternal', 'Sidang Fatwa', 'Sertifikat Terbit', 'Ditunda', 'Batal'))");
            DB::statement("ALTER TABLE business_application_status_histories ADD CONSTRAINT application_history_old_status_check CHECK (old_status IS NULL OR old_status IN ('Penawaran', 'Kontrak', 'Penyusunan SJPH', 'Audit Eksternal', 'Sidang Fatwa', 'Sertifikat Terbit', 'Ditunda', 'Batal'))");
            DB::statement("ALTER TABLE report_exports ADD CONSTRAINT report_exports_format_check CHECK (format IN ('csv', 'xlsx'))");
            DB::statement('ALTER TABLE services ADD CONSTRAINT services_display_order_check CHECK (display_order >= 1)');
            DB::statement('ALTER TABLE faqs ADD CONSTRAINT faqs_display_order_check CHECK (display_order >= 1)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            foreach ([
                ['articles', 'articles_status_check'],
                ['business_applications', 'applications_type_check'],
                ['business_applications', 'applications_status_check'],
                ['business_application_status_histories', 'application_history_new_status_check'],
                ['business_application_status_histories', 'application_history_old_status_check'],
                ['report_exports', 'report_exports_format_check'],
                ['services', 'services_display_order_check'],
                ['faqs', 'faqs_display_order_check'],
            ] as [$table, $constraint]) {
                DB::statement("ALTER TABLE {$table} DROP CHECK {$constraint}");
            }
        }

        Schema::table('services', fn (Blueprint $table) => $table->dropIndex('services_active_order_index'));
        Schema::table('faqs', fn (Blueprint $table) => $table->dropIndex('faqs_active_order_index'));
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropUnique('site_settings_singleton_unique');
            $table->dropColumn('singleton_key');
        });
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropUnique('hero_sections_singleton_unique');
            $table->dropColumn('singleton_key');
        });
    }

    private function assertSingletonReady(string $table): void
    {
        if (DB::table($table)->count() > 1) {
            throw new RuntimeException("Migration dihentikan: tabel {$table} memiliki lebih dari satu record. Konsolidasikan data secara manual sebelum deploy.");
        }
    }

    private function normalizeOrder(string $table): void
    {
        DB::table($table)->orderBy('display_order')->orderBy('id')->pluck('id')
            ->each(fn ($id, $index) => DB::table($table)->where('id', $id)->update(['display_order' => $index + 1]));
    }
};
