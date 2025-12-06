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
        if (!Schema::hasColumn('invoices', 'invoice_payment_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('invoice_payment_id')->nullable()->after('transaction_id');
            });
        }

        try {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreign('invoice_payment_id')->references('id')->on('invoice_payment_details')->onDelete('cascade')->onUpdate('cascade');
            });
        } catch (\Exception $e) {
            // FK might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_payment_id');
        });
    }
};
