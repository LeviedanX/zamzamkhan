<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hero_sections')
            ->where('trust_text', 'Dipercaya 100++ pelaku usaha dan badan usaha.')
            ->update(['trust_text' => 'Dipercaya 500++ pelaku usaha dan badan usaha.']);
    }

    public function down(): void
    {
        DB::table('hero_sections')
            ->where('trust_text', 'Dipercaya 500++ pelaku usaha dan badan usaha.')
            ->update(['trust_text' => 'Dipercaya 100++ pelaku usaha dan badan usaha.']);
    }
};
