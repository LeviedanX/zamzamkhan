<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_visits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_key', 64)->index();
            $table->string('path', 500);
            $table->string('route_name', 160)->nullable();
            $table->string('referrer_host', 255)->nullable();
            $table->string('device_type', 20)->default('desktop');
            $table->timestamp('visited_at')->index();
            $table->index(['visited_at', 'visitor_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_visits');
    }
};
