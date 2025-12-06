<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telescope_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('level')->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->string('entry_uuid')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_reports');
    }
};

