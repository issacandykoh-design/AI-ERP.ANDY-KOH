<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('lead_agents', 'lead_category_id')) {
            Schema::table('lead_agents', function (Blueprint $table) {
                $table->unsignedInteger('lead_category_id')->after('user_id')->nullable();
                $table->foreign('lead_category_id')->references('id')->on('lead_category')->onDelete('CASCADE')->onUpdate('CASCADE');
            });
        }

        if (!Schema::hasColumn('deals', 'category_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->unsignedInteger('category_id')->after('agent_id')->nullable();
                $table->foreign('category_id')->references('id')->on('lead_category')->onUpdate('CASCADE')->onDelete('SET NULL');
            });
        }

        // Move data migration logic outside of schema check or check if needed
        // Ideally, we should check if data needs update, but this is simple enough to run if columns exist
        if (Schema::hasColumn('deals', 'category_id')) {
             $deals = \App\Models\Deal::get();

            foreach ($deals as $deal) {
                if (!is_null($deal->contact) && !is_null($deal->contact->category) && is_null($deal->category_id)) {
                    $deal->category_id = $deal->contact->category->id;
                    $deal->save();
                }
            }
        }


        // Split schema operations to handle foreign key errors individually
        try {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropForeign(['agent_id']);
            });
        } catch (\Exception $e) {
            // Ignore if FK doesn't exist
        }

        try {
            Schema::table('deals', function (Blueprint $table) {
                $table->foreign('agent_id')->references('id')->on('lead_agents')->onDelete('SET NULL')->onUpdate('CASCADE');
            });
        } catch (\Exception $e) {
            // Ignore if FK addition fails (e.g. already exists or constraint violation)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            //
        });
    }

};
