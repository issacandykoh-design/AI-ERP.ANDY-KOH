<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_superadmin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_superadmin')->default(0)->after('rtl');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_superadmin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_superadmin');
            });
        }
    }
};

