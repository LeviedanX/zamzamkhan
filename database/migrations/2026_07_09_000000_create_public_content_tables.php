<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advantages', function (Blueprint $table) {
            $table->id(); $table->string('icon', 80)->nullable(); $table->string('title', 160); $table->text('description');
            $table->unsignedInteger('display_order')->default(1); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['is_active', 'display_order']);
        });
        Schema::create('statistics', function (Blueprint $table) {
            $table->id(); $table->string('value', 40); $table->string('label', 160); $table->string('description')->nullable();
            $table->unsignedInteger('display_order')->default(1); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['is_active', 'display_order']);
        });
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); $table->string('name', 160); $table->string('logo_path'); $table->string('website_url', 500)->nullable(); $table->string('industry', 100)->nullable();
            $table->unsignedInteger('display_order')->default(1); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['is_active', 'display_order']);
        });
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id(); $table->string('client_name', 160); $table->string('service_name', 160)->nullable(); $table->text('content');
            $table->string('image_path')->nullable(); $table->string('image_alt')->nullable(); $table->unsignedInteger('display_order')->default(1);
            $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['is_active', 'display_order']);
        });
        Schema::create('agendas', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->string('summary', 500)->nullable(); $table->longText('description')->nullable();
            $table->string('venue')->nullable(); $table->dateTime('starts_at'); $table->dateTime('ends_at')->nullable(); $table->string('registration_url', 500)->nullable();
            $table->string('image_path')->nullable(); $table->unsignedInteger('display_order')->default(1); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['is_active', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas'); Schema::dropIfExists('testimonials'); Schema::dropIfExists('clients');
        Schema::dropIfExists('statistics'); Schema::dropIfExists('advantages');
    }
};
