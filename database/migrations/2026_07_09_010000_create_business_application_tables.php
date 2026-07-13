<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_categories', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('business_applications', function (Blueprint $table) {
            $table->id(); $table->string('applicant_type', 20); $table->string('business_name')->nullable(); $table->string('brand_name')->nullable();
            $table->string('owner_name')->nullable(); $table->text('address')->nullable(); $table->string('registration_number', 100)->nullable();
            $table->foreignId('business_category_id')->nullable(); $table->string('process_status', 50);
            $table->text('notes')->nullable(); $table->date('submitted_at')->nullable(); $table->date('certificate_issued_at')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable(); $table->timestamps();
            $table->foreign('business_category_id', 'ba_category_fk')->references('id')->on('business_categories')->nullOnDelete();
            $table->foreign('created_by', 'ba_created_by_fk')->references('id')->on('admins')->nullOnDelete();
            $table->foreign('updated_by', 'ba_updated_by_fk')->references('id')->on('admins')->nullOnDelete();
            $table->index(['process_status', 'submitted_at']); $table->index(['applicant_type', 'business_category_id']); $table->index('registration_number');
        });
        Schema::create('business_application_status_histories', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_application_id'); $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50); $table->text('note')->nullable(); $table->foreignId('changed_by')->nullable(); $table->timestamps();
            $table->foreign('business_application_id', 'ba_history_app_fk')->references('id')->on('business_applications')->cascadeOnDelete();
            $table->foreign('changed_by', 'ba_history_admin_fk')->references('id')->on('admins')->nullOnDelete();
        });
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('report_type', 60); $table->json('filters_json')->nullable();
            $table->json('columns_json')->nullable(); $table->string('format', 10); $table->string('file_path')->nullable();
            $table->foreignId('generated_by')->nullable(); $table->timestamp('generated_at')->nullable(); $table->timestamps();
            $table->foreign('generated_by', 'report_generated_by_fk')->references('id')->on('admins')->nullOnDelete();
            $table->index(['format', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports'); Schema::dropIfExists('business_application_status_histories');
        Schema::dropIfExists('business_applications'); Schema::dropIfExists('business_categories');
    }
};
