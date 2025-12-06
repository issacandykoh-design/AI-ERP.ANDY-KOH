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
        $tableGateway = 'global_payment_gateway_credentials';
        if (Schema::hasColumn($tableGateway, 'razorpay_webhook_secret') && !Schema::hasColumn($tableGateway, 'live_razorpay_webhook_secret')) {
            Schema::table($tableGateway, function (Blueprint $table) {
                $table->renameColumn('razorpay_webhook_secret', 'live_razorpay_webhook_secret');
            });
        }

        if (!Schema::hasColumn($tableGateway, 'test_razorpay_webhook_secret')) {
            Schema::table($tableGateway, function (Blueprint $table) {
                // Check if live_razorpay_webhook_secret exists to determine position, otherwise add without after
                if (Schema::hasColumn('global_payment_gateway_credentials', 'live_razorpay_webhook_secret')) {
                     $table->string('test_razorpay_webhook_secret')->nullable()->after('live_razorpay_webhook_secret');
                } else {
                     $table->string('test_razorpay_webhook_secret')->nullable();
                }
            });
        }

        if (!Schema::hasColumn($tableGateway, 'live_razorpay_webhook_secret')) {
            Schema::table($tableGateway, function (Blueprint $table) {
                if (Schema::hasColumn('global_payment_gateway_credentials', 'test_razorpay_webhook_secret')) {
                    $table->string('live_razorpay_webhook_secret')->nullable()->after('test_razorpay_webhook_secret');
                } else {
                    $table->string('live_razorpay_webhook_secret')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
