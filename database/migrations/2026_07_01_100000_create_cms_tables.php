<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160)->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 180)->default('PT Zam Zam Khan');
            $table->string('brand_name', 180)->nullable();
            $table->string('tagline', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('email', 160)->nullable();
            $table->text('address')->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('instagram_url', 255)->nullable();
            $table->string('tiktok_url', 255)->nullable();
            $table->json('social_links')->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('favicon_path', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('subtitle')->nullable();
            $table->string('primary_button_text', 80)->nullable();
            $table->string('primary_button_url', 255)->nullable();
            $table->string('secondary_button_text', 80)->nullable();
            $table->string('secondary_button_url', 255)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('badge_text', 120)->nullable();
            $table->string('trust_text', 160)->nullable();
            $table->text('service_chips')->nullable();
            $table->string('portrait_path', 255)->nullable();
            $table->string('portrait_alt', 180)->nullable();
            $table->string('portrait_role', 80)->nullable();
            $table->string('portrait_name', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('icon', 80)->nullable();
            $table->string('summary', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('benefits')->nullable();
            $table->text('suitable_for')->nullable();
            $table->text('workflow_steps')->nullable();
            $table->text('whatsapp_message')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 255);
            $table->text('answer');
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

        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 80)->unique();
            $table->string('meta_title', 180);
            $table->string('meta_description', 255)->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->string('og_title', 180)->nullable();
            $table->string('og_description', 255)->nullable();
            $table->string('og_image_path', 255)->nullable();
            $table->string('canonical_url', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('process_steps');
        Schema::dropIfExists('services');
        Schema::dropIfExists('hero_sections');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('admins');
    }
};
