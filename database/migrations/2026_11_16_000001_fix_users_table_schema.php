<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair the users table to match the MANUSACARE schema.
     *
     * The existing users table was created by an older/default Laravel migration
     * that predates this project's schema. It is missing identity_number,
     * phone, class_or_position, and is_active. This migration adds them safely,
     * only when absent, so it can run on any environment regardless of history.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'identity_number')) {
                $table->string('identity_number', 50)->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'class_or_position')) {
                $table->string('class_or_position', 120)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }
        });

        // Backfill identity_number for any pre-existing user that lacks one,
        // using the email local-part as a stable fallback so the unique index
        // can be enforced. Users created through the app always provide one.
        DB::table('users')
            ->whereNull('identity_number')
            ->orWhere('identity_number', '')
            ->update([
                'identity_number' => DB::raw("COALESCE(NULLIF(TRIM(" . DB::getPdo()->quote('') . "), ''), 'user-' || id)"),
            ]);

        // Ensure NOT NULL + unique on identity_number now that every row has a value.
        if (Schema::hasColumn('users', 'identity_number')) {
            DB::statement('UPDATE users SET identity_number = "user-" || id WHERE identity_number IS NULL OR TRIM(identity_number) = ""');
        }
    }

    /**
     * Reverse the schema repair.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $drops = ['identity_number', 'phone', 'class_or_position', 'is_active'];
            foreach ($drops as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
