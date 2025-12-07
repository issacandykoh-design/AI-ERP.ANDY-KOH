<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'header_color')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('header_color')->after('logo_background_color')->default('#1D82F5');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'header_color')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('header_color');
            });
        }
    }
};

