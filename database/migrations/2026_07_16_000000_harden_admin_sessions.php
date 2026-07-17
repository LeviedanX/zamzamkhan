<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('auth_version')->default(1)->after('is_active');
        });

        // Sesi lama belum membawa auth_version dan user_id admin yang benar.
        // Paksa login ulang satu kali agar seluruh sesi setelah migration memakai kontrak baru.
        DB::table('sessions')->delete();
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('auth_version');
        });
    }
};
