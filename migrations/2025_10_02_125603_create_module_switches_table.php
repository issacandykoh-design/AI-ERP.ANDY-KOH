<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('module_switches', function (Blueprint $table) {
            $table->id();
            $table->string('module_name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            
            $table->index(['module_name', 'is_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_switches');
    }
};
