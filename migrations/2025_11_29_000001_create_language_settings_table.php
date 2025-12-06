<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (! Schema::hasTable('language_settings')) {
            Schema::create('language_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('language_code');
                $table->string('flag_code')->nullable();
                $table->string('language_name');
                $table->enum('status', ['enabled','disabled'])->default('enabled');
                $table->boolean('is_rtl')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('language_settings');
    }
};

