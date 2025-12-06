<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('custom_menu_settings')) {
            Schema::create('custom_menu_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('company_id')->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->string('menu_key', 100);
                $table->string('custom_name')->nullable();
                $table->integer('menu_order')->default(999);
                $table->boolean('is_visible')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'menu_key']);
                $table->index(['company_id', 'menu_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_menu_settings');
    }
};

