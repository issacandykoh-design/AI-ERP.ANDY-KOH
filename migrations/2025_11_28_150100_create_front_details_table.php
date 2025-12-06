<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('front_details')) {
            Schema::create('front_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('get_started_show')->default('yes');
                $table->string('sign_in_show')->default('yes');
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('image')->nullable();
                $table->string('background_image')->nullable();
                $table->text('social_links')->nullable();
                $table->string('primary_color')->nullable();
                $table->text('custom_css')->nullable();
                $table->text('custom_css_theme_two')->nullable();
                $table->string('locale')->nullable();
                $table->longText('contact_html')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('front_details');
    }
};

