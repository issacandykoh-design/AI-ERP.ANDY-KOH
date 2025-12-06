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
            $table->boolean('processed_for_ai')->default(false)->after('last_updated_by');
            $table->timestamp('ai_processed_at')->nullable()->after('processed_for_ai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knowledge_base_files', function (Blueprint $table) {
            $table->dropColumn(['processed_for_ai', 'ai_processed_at']);
        });
    }
};
