<?php

use App\Models\UserAuth;
use App\Scopes\ActiveScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!iscravevaSaas()){
            return true;
        }

        Schema::table('global_invoices', function (Blueprint $table) {
            $table->double('sub_total')->nullable()->change();
            $table->double('total')->nullable()->change();
        });

        Schema::table('front_details', function (Blueprint $table) {
            if (!Schema::hasColumn('front_details', 'homepage_background')) {
                $table->enum('homepage_background', ['default', 'color', 'image', 'image_and_color'])->default('default');
            }
            if (!Schema::hasColumn('front_details', 'background_color')) {
                $table->string('background_color')->nullable()->default('#CDDCDC');
            }
            if (!Schema::hasColumn('front_details', 'background_image')) {
                $table->string('background_image')->nullable();
            }
        });


        Schema::table('packages', function (Blueprint $table) {
            $table->double('annual_price')->nullable()->change();
            $table->double('monthly_price')->nullable()->change();
        });

        // Delete the users that are not in users table
        UserAuth::withoutGlobalScope(ActiveScope::class)
            ->doesntHave('users')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

};
