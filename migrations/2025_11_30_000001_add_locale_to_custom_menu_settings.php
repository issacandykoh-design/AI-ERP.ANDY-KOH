<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('custom_menu_settings')) {
            Schema::table('custom_menu_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_menu_settings', 'locale')) {
                    $table->string('locale', 10)->nullable()->after('menu_key')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('custom_menu_settings')) {
            Schema::table('custom_menu_settings', function (Blueprint $table) {
                if (Schema::hasColumn('custom_menu_settings', 'locale')) {
                    $table->dropColumn('locale');
                }
            });
        }
    }
};

