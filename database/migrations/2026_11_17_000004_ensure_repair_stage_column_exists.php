<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repairs schema drift on installations where migration 000002 is already
     * recorded as completed but report_updates.repair_stage is absent.
     */
    public function up(): void
    {
        if (! Schema::hasTable('report_updates') || Schema::hasColumn('report_updates', 'repair_stage')) {
            return;
        }

        Schema::table('report_updates', function (Blueprint $table): void {
            // No index is needed for this small workflow field.
            $table->string('repair_stage', 40)->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('report_updates') || ! Schema::hasColumn('report_updates', 'repair_stage')) {
            return;
        }

        Schema::table('report_updates', function (Blueprint $table): void {
            $table->dropColumn('repair_stage');
        });
    }
};
