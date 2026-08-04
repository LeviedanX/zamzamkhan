<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $references = [
        'business_applications' => ['created_by', 'updated_by'],
        'business_application_status_histories' => ['changed_by'],
        'report_exports' => ['generated_by'],
        'content_replacement_runs' => ['created_by', 'reverted_by'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $adminIds = DB::table('users')
            ->where('is_admin', true)
            ->orderBy('id')
            ->pluck('id');

        // Pada fresh install, admin baru akan dibuat langsung dengan ID 1.
        if ($adminIds->isEmpty() || (int) $adminIds->first() === 1) {
            return;
        }

        if ($adminIds->count() !== 1) {
            throw new RuntimeException('Normalisasi ID dibatalkan karena terdapat lebih dari satu akun admin.');
        }

        if (DB::table('users')->where('id', 1)->exists()) {
            throw new RuntimeException('Normalisasi ID admin dibatalkan karena ID 1 sudah digunakan akun lain.');
        }

        $oldId = (int) $adminIds->first();
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'sqlite'], true)) {
            throw new RuntimeException("Normalisasi ID admin belum mendukung driver database {$driver}.");
        }

        $this->setForeignKeyChecks($driver, false);

        try {
            DB::transaction(function () use ($oldId): void {
                foreach ($this->references as $table => $columns) {
                    if (! Schema::hasTable($table)) {
                        continue;
                    }

                    foreach ($columns as $column) {
                        if (Schema::hasColumn($table, $column)) {
                            DB::table($table)->where($column, $oldId)->update([$column => 1]);
                        }
                    }
                }

                if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                    DB::table('sessions')->where('user_id', $oldId)->update(['user_id' => 1]);
                }

                DB::table('users')->where('id', $oldId)->update(['id' => 1]);
            });
        } finally {
            $this->setForeignKeyChecks($driver, true);
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `users` AUTO_INCREMENT = 2');
        }

        // ID sesi berubah dan sesi lama tidak perlu dipertahankan saat migrasi identitas.
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->delete();
        }
    }

    public function down(): void
    {
        // ID 63 adalah artefak data lama, bukan kontrak yang perlu dipulihkan.
    }

    private function setForeignKeyChecks(string $driver, bool $enabled): void
    {
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS='.(int) $enabled);

            return;
        }

        DB::statement('PRAGMA foreign_keys = '.($enabled ? 'ON' : 'OFF'));
    }
};
