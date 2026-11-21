<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }
            if (! Schema::hasColumn('reports', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Preserve workflow state for records created by the previous backend.
        DB::table('reports')
            ->whereIn('status', ['diproses', 'selesai'])
            ->whereNull('verified_at')
            ->update(['verified_at' => DB::raw('created_at')]);

        // Earlier releases could mark a report as in progress immediately after
        // admin approval, even when no officer had actually been assigned.
        // Restore the waiting state only for orphaned in-progress rows; completed history is preserved.
        DB::table('reports')
            ->where('status', 'diproses')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('1'))
                    ->from('assignments')
                    ->whereColumn('assignments.report_id', 'reports.id')
                    ->where('assignments.is_active', true);
            })
            ->update(['status' => 'dikirim']);
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'verified_by')) {
                $table->dropConstrainedForeignId('verified_by');
            }
            if (Schema::hasColumn('reports', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
        });
    }
};
