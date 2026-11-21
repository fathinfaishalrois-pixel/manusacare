<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: tolerate databases where this column was added manually
        // or by a partially-applied deployment.
        if (! Schema::hasTable('report_updates') || Schema::hasColumn('report_updates', 'repair_stage')) {
            return;
        }

        Schema::table('report_updates', function (Blueprint $table): void {
            $table->string('repair_stage', 40)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('report_updates') || ! Schema::hasColumn('report_updates', 'repair_stage')) {
            return;
        }

        Schema::table('report_updates', function (Blueprint $table): void {
            $table->dropIndex(['repair_stage']);
            $table->dropColumn('repair_stage');
        });
    }
};
