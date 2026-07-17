<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_STATUSES = [
        ['name' => 'Penawaran', 'type' => 'ongoing', 'display_order' => 1, 'is_default' => true],
        ['name' => 'Kontrak', 'type' => 'ongoing', 'display_order' => 2, 'is_default' => false],
        ['name' => 'Penyusunan SJPH', 'type' => 'ongoing', 'display_order' => 3, 'is_default' => false],
        ['name' => 'Audit Eksternal', 'type' => 'ongoing', 'display_order' => 4, 'is_default' => false],
        ['name' => 'Sidang Fatwa', 'type' => 'ongoing', 'display_order' => 5, 'is_default' => false],
        ['name' => 'Sertifikat Terbit', 'type' => 'issued', 'display_order' => 6, 'is_default' => false],
        ['name' => 'Ditunda', 'type' => 'ongoing', 'display_order' => 7, 'is_default' => false],
        ['name' => 'Batal', 'type' => 'cancelled', 'display_order' => 8, 'is_default' => false],
    ];

    public function up(): void
    {
        Schema::create('business_process_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('type', 20)->default('ongoing');
            $table->unsignedSmallInteger('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'display_order']);
            $table->index('type');
        });

        $now = now();
        DB::table('business_process_statuses')->insert(array_map(
            fn (array $status) => $status + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            self::LEGACY_STATUSES,
        ));

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE business_applications DROP CHECK applications_status_check');
            DB::statement('ALTER TABLE business_application_status_histories DROP CHECK application_history_new_status_check');
            DB::statement('ALTER TABLE business_application_status_histories DROP CHECK application_history_old_status_check');
        }

        Schema::table('business_applications', function (Blueprint $table) {
            $table->foreign('process_status', 'ba_process_status_fk')
                ->references('name')->on('business_process_statuses')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
        Schema::table('business_application_status_histories', function (Blueprint $table) {
            $table->foreign('new_status', 'ba_history_new_status_fk')
                ->references('name')->on('business_process_statuses')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('old_status', 'ba_history_old_status_fk')
                ->references('name')->on('business_process_statuses')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $legacyNames = array_column(self::LEGACY_STATUSES, 'name');
        $usedNames = DB::table('business_applications')->pluck('process_status')
            ->merge(DB::table('business_application_status_histories')->pluck('new_status'))
            ->merge(DB::table('business_application_status_histories')->whereNotNull('old_status')->pluck('old_status'))
            ->unique();

        if ($usedNames->diff($legacyNames)->isNotEmpty()) {
            throw new RuntimeException('Rollback dibatalkan: masih ada pengajuan atau riwayat yang memakai status proses kustom.');
        }

        Schema::table('business_application_status_histories', function (Blueprint $table) {
            $table->dropForeign('ba_history_new_status_fk');
            $table->dropForeign('ba_history_old_status_fk');
        });
        Schema::table('business_applications', fn (Blueprint $table) => $table->dropForeign('ba_process_status_fk'));
        Schema::dropIfExists('business_process_statuses');

        if (DB::connection()->getDriverName() === 'mysql') {
            $allowed = "'".implode("', '", $legacyNames)."'";
            DB::statement("ALTER TABLE business_applications ADD CONSTRAINT applications_status_check CHECK (process_status IN ({$allowed}))");
            DB::statement("ALTER TABLE business_application_status_histories ADD CONSTRAINT application_history_new_status_check CHECK (new_status IN ({$allowed}))");
            DB::statement("ALTER TABLE business_application_status_histories ADD CONSTRAINT application_history_old_status_check CHECK (old_status IS NULL OR old_status IN ({$allowed}))");
        }
    }
};
