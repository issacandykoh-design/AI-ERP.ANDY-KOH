<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        if (!Schema::hasColumn('global_settings', 'auth_theme_text')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->enum('auth_theme_text', ['dark', 'light'])->after('auth_theme')->default('dark');
            });
        }

        if (!Schema::hasColumn('companies', 'auth_theme_text')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->enum('auth_theme_text', ['dark', 'light'])->after('auth_theme')->default('dark');
            });
        }

        cache()->forget('global_settings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('auth_theme_text');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('auth_theme_text');
        });

        cache()->forget('global_settings');
    }

};
