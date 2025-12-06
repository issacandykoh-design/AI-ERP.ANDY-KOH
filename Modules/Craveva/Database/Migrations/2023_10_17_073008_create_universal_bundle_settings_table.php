<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Craveva\Entities\CravevaSetting;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\Module::validateVersion(CravevaSetting::MODULE_NAME);

        if (!Schema::hasTable('craveva_settings')) {
            Schema::create('craveva_settings', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_code')->nullable();
                $table->timestamp('supported_until')->nullable();
                $table->timestamps();
            });

            CravevaSetting::create([]);
        }

        if (!Schema::hasTable('craveva_module_installs')) {
            Schema::create('craveva_module_installs', function (Blueprint $table) {
                $table->id();
                $table->string('module_name');
                $table->string('version')->nullable();
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('craveva_settings');
        Schema::dropIfExists('craveva_module_installs');
    }

};
