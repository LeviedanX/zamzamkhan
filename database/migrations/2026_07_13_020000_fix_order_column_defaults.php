<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('display_order')->default(1)->change();
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->integer('display_order')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('display_order')->default(0)->change();
        });
        Schema::table('faqs', function (Blueprint $table) {
            $table->integer('display_order')->default(0)->change();
        });
    }
};
