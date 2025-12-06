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
        if (!Schema::hasTable('company_process')) {
            Schema::create('company_process', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->index();
                $table->string('title')->nullable();
                $table->string('source')->nullable();
                $table->longText('content');
                // Store embedding as JSON string; use longText for broad DB compatibility
                $table->longText('embedding')->nullable();
                $table->timestamps();

                // Use index for multi-tenant scoping; avoid FK to keep compatibility
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_process');
    }
};