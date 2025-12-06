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
        Schema::table('knowledge_base_files', function (Blueprint $table) {
            $table->text('processing_error')->nullable()->after('ai_processed_at');
            $table->timestamp('last_processing_attempt')->nullable()->after('processing_error');
            $table->integer('processing_attempts')->default(0)->after('last_processing_attempt');
            $table->string('processing_status')->default('pending')->after('processing_attempts'); // pending, processing, completed, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knowledge_base_files', function (Blueprint $table) {
            $table->dropColumn(['processing_error', 'last_processing_attempt', 'processing_attempts', 'processing_status']);
        });
    }
};
