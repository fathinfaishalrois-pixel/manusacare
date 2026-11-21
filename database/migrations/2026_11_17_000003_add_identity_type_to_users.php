<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'identity_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('identity_type', 12)->nullable()->after('identity_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'identity_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('identity_type');
            });
        }
    }
};
