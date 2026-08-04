<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_replacement_runs', function (Blueprint $table) {
            $table->id();
            $table->string('cluster', 50);
            $table->text('search_text');
            $table->text('replacement_text')->nullable();
            $table->boolean('case_sensitive')->default(true);
            $table->unsignedInteger('affected_records')->default(0);
            $table->unsignedInteger('affected_fields')->default(0);
            $table->unsignedInteger('occurrence_count')->default(0);
            $table->string('status', 30)->default('completed');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at');
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reverted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'executed_at']);
        });

        Schema::create('content_replacement_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_replacement_run_id')
                ->constrained('content_replacement_runs')
                ->cascadeOnDelete();
            $table->string('source_key', 50);
            $table->unsignedBigInteger('record_id');
            $table->string('column_name', 80);
            $table->longText('before_text')->nullable();
            $table->longText('after_text')->nullable();
            $table->timestamp('reverted_at')->nullable();
            $table->timestamps();

            $table->index(['source_key', 'record_id']);
            $table->index(['content_replacement_run_id', 'reverted_at'], 'crc_run_reverted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_replacement_changes');
        Schema::dropIfExists('content_replacement_runs');
    }
};
