<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'suitable_for')) {
                $table->text('suitable_for')->nullable()->after('benefits');
            }

            if (! Schema::hasColumn('services', 'workflow_steps')) {
                $table->text('workflow_steps')->nullable()->after('suitable_for');
            }

            if (! Schema::hasColumn('services', 'whatsapp_message')) {
                $table->text('whatsapp_message')->nullable()->after('workflow_steps');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            foreach (['whatsapp_message', 'workflow_steps', 'suitable_for'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
