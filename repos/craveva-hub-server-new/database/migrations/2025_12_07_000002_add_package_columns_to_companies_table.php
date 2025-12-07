<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('currency_id');
            }
            if (!Schema::hasColumn('companies', 'package_type')) {
                $table->enum('package_type', ['monthly', 'annual'])->default('monthly')->after('package_id');
            }
            if (!Schema::hasColumn('companies', 'stripe_id')) {
                $table->string('stripe_id')->nullable();
            }
            if (!Schema::hasColumn('companies', 'card_brand')) {
                $table->string('card_brand')->nullable();
            }
            if (!Schema::hasColumn('companies', 'card_last_four')) {
                $table->string('card_last_four')->nullable();
            }
            if (!Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable();
            }
            if (!Schema::hasColumn('companies', 'licence_expire_on')) {
                $table->date('licence_expire_on')->nullable();
            }
            if (!Schema::hasColumn('companies', 'license_updated_at')) {
                $table->timestamp('license_updated_at')->nullable();
            }
            if (!Schema::hasColumn('companies', 'subscription_updated_at')) {
                $table->timestamp('subscription_updated_at')->nullable();
            }
        });

        // Add FK separately to avoid errors when packages table already exists
        if (!Schema::hasColumn('companies', 'package_id')) {
            // column added above in same migration
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'subscription_updated_at')) {
                $table->dropColumn('subscription_updated_at');
            }
            if (Schema::hasColumn('companies', 'license_updated_at')) {
                $table->dropColumn('license_updated_at');
            }
            if (Schema::hasColumn('companies', 'licence_expire_on')) {
                $table->dropColumn('licence_expire_on');
            }
            if (Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->dropColumn('trial_ends_at');
            }
            if (Schema::hasColumn('companies', 'card_last_four')) {
                $table->dropColumn('card_last_four');
            }
            if (Schema::hasColumn('companies', 'card_brand')) {
                $table->dropColumn('card_brand');
            }
            if (Schema::hasColumn('companies', 'stripe_id')) {
                $table->dropColumn('stripe_id');
            }
            if (Schema::hasColumn('companies', 'package_type')) {
                $table->dropColumn('package_type');
            }
            if (Schema::hasColumn('companies', 'package_id')) {
                $table->dropColumn('package_id');
            }
        });
    }
};

