<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Riwayat lama yang menunjuk file sama tidak boleh mempertahankan
        // referensi ganda: satu record tetap valid, sisanya menjadi arsip metadata.
        $duplicates = DB::table('report_exports')
            ->select('file_path', DB::raw('MIN(id) as keeper_id'))
            ->whereNotNull('file_path')
            ->groupBy('file_path')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('report_exports')
                ->where('file_path', $duplicate->file_path)
                ->where('id', '!=', $duplicate->keeper_id)
                ->update(['file_path' => null]);
        }

        Schema::table('report_exports', function (Blueprint $table) {
            $table->unique('file_path', 'report_exports_file_path_unique');
        });
    }

    public function down(): void
    {
        Schema::table('report_exports', function (Blueprint $table) {
            $table->dropUnique('report_exports_file_path_unique');
        });
    }
};
