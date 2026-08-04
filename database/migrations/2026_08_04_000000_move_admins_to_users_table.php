<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array<string, string>> */
    private array $references = [
        'business_applications' => [
            'created_by' => 'ba_created_by_fk',
            'updated_by' => 'ba_updated_by_fk',
        ],
        'business_application_status_histories' => [
            'changed_by' => 'ba_history_admin_fk',
        ],
        'report_exports' => [
            'generated_by' => 'report_generated_by_fk',
        ],
        'content_replacement_runs' => [
            'created_by' => 'content_replacement_runs_created_by_foreign',
            'reverted_by' => 'content_replacement_runs_reverted_by_foreign',
        ],
    ];

    public function up(): void
    {
        $this->addAdminColumnsToUsers();

        // Instalasi baru sudah langsung memakai users dari migration awal.
        if (! Schema::hasTable('admins')) {
            return;
        }

        $adminIdMap = $this->copyAdminsToUsers();
        $this->dropForeignKeys();
        $this->remapReferences($adminIdMap);
        $this->createForeignKeys('users');

        Schema::drop('admins');

        // Seluruh sesi lama masih membawa kontrak provider/model lama.
        DB::table('sessions')->delete();
    }

    public function down(): void
    {
        if (Schema::hasTable('admins')) {
            return;
        }

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160)->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('auth_version')->default(1);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')
            ->where('is_admin', true)
            ->orderBy('id')
            ->each(function (object $user): void {
                DB::table('admins')->insert([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'is_active' => $user->is_active,
                    'auth_version' => $user->auth_version,
                    'last_login_at' => $user->last_login_at,
                    'remember_token' => $user->remember_token,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            });

        $this->dropForeignKeys();
        $this->createForeignKeys('admins');
        DB::table('sessions')->delete();
    }

    private function addAdminColumnsToUsers(): void
    {
        $addIsAdmin = ! Schema::hasColumn('users', 'is_admin');
        $addIsActive = ! Schema::hasColumn('users', 'is_active');
        $addAuthVersion = ! Schema::hasColumn('users', 'auth_version');
        $addLastLoginAt = ! Schema::hasColumn('users', 'last_login_at');

        if (! $addIsAdmin && ! $addIsActive && ! $addAuthVersion && ! $addLastLoginAt) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($addIsAdmin, $addIsActive, $addAuthVersion, $addLastLoginAt): void {
            if ($addIsAdmin) {
                $table->boolean('is_admin')->default(false)->index()->after('password');
            }
            if ($addIsActive) {
                $table->boolean('is_active')->default(true)->after('is_admin');
            }
            if ($addAuthVersion) {
                $table->unsignedBigInteger('auth_version')->default(1)->after('is_active');
            }
            if ($addLastLoginAt) {
                $table->timestamp('last_login_at')->nullable()->after('auth_version');
            }
        });
    }

    /** @return array<int, int> */
    private function copyAdminsToUsers(): array
    {
        $map = [];

        DB::table('admins')->orderBy('id')->each(function (object $admin) use (&$map): void {
            $email = mb_strtolower(trim((string) $admin->email));
            $existingByEmail = DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->first();

            $attributes = [
                'name' => $admin->name,
                'email' => $email,
                'password' => $admin->password,
                'is_admin' => true,
                'is_active' => $admin->is_active,
                'auth_version' => max(1, (int) $admin->auth_version),
                'last_login_at' => $admin->last_login_at,
                'remember_token' => $admin->remember_token,
                'updated_at' => $admin->updated_at,
            ];

            if ($existingByEmail) {
                DB::table('users')->where('id', $existingByEmail->id)->update($attributes);
                $map[(int) $admin->id] = (int) $existingByEmail->id;

                return;
            }

            $idIsAvailable = ! DB::table('users')->where('id', $admin->id)->exists();
            if ($idIsAvailable) {
                DB::table('users')->insert($attributes + [
                    'id' => $admin->id,
                    'email_verified_at' => null,
                    'created_at' => $admin->created_at,
                ]);
                $map[(int) $admin->id] = (int) $admin->id;

                return;
            }

            $map[(int) $admin->id] = (int) DB::table('users')->insertGetId($attributes + [
                'email_verified_at' => null,
                'created_at' => $admin->created_at,
            ]);
        });

        return $map;
    }

    private function remapReferences(array $adminIdMap): void
    {
        foreach ($this->references as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($columns) as $column) {
                foreach ($adminIdMap as $adminId => $userId) {
                    if ($adminId !== $userId) {
                        DB::table($table)->where($column, $adminId)->update([$column => $userId]);
                    }
                }
            }
        }
    }

    private function dropForeignKeys(): void
    {
        foreach ($this->references as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
                foreach ($columns as $foreignKey) {
                    $blueprint->dropForeign($foreignKey);
                }
            });
        }
    }

    private function createForeignKeys(string $targetTable): void
    {
        foreach ($this->references as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $targetTable): void {
                foreach ($columns as $column => $foreignKey) {
                    $blueprint->foreign($column, $foreignKey)
                        ->references('id')
                        ->on($targetTable)
                        ->nullOnDelete();
                }
            });
        }
    }
};
