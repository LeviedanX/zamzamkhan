<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('process_steps');
    }

    public function down(): void
    {
        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('image_path', 255);
            $table->string('alt_text', 255)->nullable();
            $table->string('category', 100)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('service_interest', 160)->nullable();
            $table->text('message');
            $table->string('status', 30)->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }
};
