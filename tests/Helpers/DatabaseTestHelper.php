<?php

namespace Tests\Helpers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseTestHelper
{
    /**
     * Create only the tables needed for our new features
     * This bypasses old migrations that have MySQL-specific code
     */
    public static function createTestTables(): void
    {
        $driver = config('database.default');
        
        if ($driver === 'sqlite') {
            // For SQLite, create tables directly
            self::createSQLiteTables();
        } else {
            // For MySQL, run migrations normally
            \Artisan::call('migrate', ['--force' => true]);
        }
    }

    protected static function createSQLiteTables(): void
    {
        // Ensure global_settings table exists FIRST (required by CompanyObserver)
        if (!Schema::hasTable('global_settings')) {
            Schema::create('global_settings', function ($table) {
                $table->id();
                $table->string('global_app_name')->default('Craveva');
                $table->string('company_name')->nullable();
                $table->string('company_email')->nullable();
                $table->string('logo_background_color')->default('#ffffff');
                $table->string('header_color')->nullable();
                $table->string('login_background')->nullable();
                $table->string('sidebar_logo_style')->nullable();
                $table->string('auth_theme')->default('light');
                $table->string('auth_theme_text')->nullable();
                $table->string('favicon')->nullable();
                $table->integer('datatable_row_limit')->default(25);
                $table->boolean('company_need_approval')->default(false);
                $table->string('timezone')->default('UTC');
                $table->string('date_format')->default('Y-m-d');
                $table->string('time_format')->default('H:i:s');
                $table->string('locale')->default('en');
                $table->string('logo')->nullable();
                $table->timestamps();
            });

            // Create a default global setting record IMMEDIATELY
            DB::table('global_settings')->insert([
                'global_app_name' => 'Craveva',
                'logo_background_color' => '#ffffff',
                'auth_theme' => 'light',
                'datatable_row_limit' => 25,
                'company_need_approval' => false,
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
                'locale' => 'en',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Module Categories
        if (!Schema::hasTable('module_categories')) {
            Schema::create('module_categories', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->timestamps();
            });
        }

        // Marketplace Modules
        if (!Schema::hasTable('marketplace_modules')) {
            Schema::create('marketplace_modules', function ($table) {
                $table->id();
                $table->string('module_key')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('version')->default('1.0.0');
                $table->string('author')->nullable();
                $table->decimal('price_monthly', 15, 2)->nullable();
                $table->decimal('price_annual', 15, 2)->nullable();
                $table->decimal('price_lifetime', 15, 2)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->text('tags')->nullable(); // JSON as text for SQLite
                $table->string('icon')->nullable();
                $table->text('screenshots')->nullable(); // JSON as text
                $table->string('documentation_url')->nullable();
                $table->string('support_url')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->decimal('rating', 3, 2)->default(0);
                $table->integer('download_count')->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Company Module Subscriptions
        if (!Schema::hasTable('company_module_subscriptions')) {
            Schema::create('company_module_subscriptions', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->unsignedBigInteger('marketplace_module_id');
                $table->string('subscription_type')->default('monthly');
                $table->string('status')->default('active');
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->boolean('auto_renew')->default(true);
                $table->string('payment_gateway')->nullable();
                $table->string('gateway_subscription_id')->nullable();
                $table->boolean('is_addon')->default(false);
                $table->timestamps();
            });
        }

        // Module Dependencies
        if (!Schema::hasTable('module_dependencies')) {
            Schema::create('module_dependencies', function ($table) {
                $table->id();
                $table->unsignedBigInteger('marketplace_module_id');
                $table->unsignedBigInteger('required_module_id');
                $table->boolean('is_optional')->default(false);
                $table->timestamps();
            });
        }

        // AI Credit Packages
        if (!Schema::hasTable('ai_credit_packages')) {
            Schema::create('ai_credit_packages', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('credits_amount');
                $table->decimal('price', 15, 2);
                $table->unsignedInteger('currency_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // AI Credit Promotions
        if (!Schema::hasTable('ai_credit_promotions')) {
            Schema::create('ai_credit_promotions', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('minimum_purchase_amount', 15, 2)->nullable();
                $table->integer('bonus_credits_amount')->nullable();
                $table->decimal('bonus_percentage', 5, 2)->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('applicable_to_packages')->nullable(); // JSON as text
                $table->integer('max_uses_per_company')->nullable();
                $table->integer('total_max_uses')->nullable();
                $table->timestamps();
            });
        }

        // Company AI Credits
        if (!Schema::hasTable('company_ai_credits')) {
            Schema::create('company_ai_credits', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id')->unique();
                $table->decimal('balance', 15, 2)->default(0);
                $table->decimal('lifetime_earned', 15, 2)->default(0);
                $table->decimal('lifetime_spent', 15, 2)->default(0);
                $table->integer('monthly_credits_from_package')->default(0);
                $table->timestamp('last_updated_at')->nullable();
                $table->timestamps();
            });
        }

        // AI Credit Transactions
        if (!Schema::hasTable('ai_credit_transactions')) {
            Schema::create('ai_credit_transactions', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('transaction_type');
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_after', 15, 2);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // AI Credit Purchases
        if (!Schema::hasTable('ai_credit_purchases')) {
            Schema::create('ai_credit_purchases', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->unsignedBigInteger('ai_credit_package_id');
                $table->unsignedBigInteger('promotion_id')->nullable();
                $table->integer('credits_amount');
                $table->integer('bonus_credits_amount')->default(0);
                $table->decimal('price', 15, 2);
                $table->unsignedInteger('currency_id')->nullable();
                $table->string('payment_gateway')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('status')->default('pending');
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->timestamp('purchased_at')->nullable();
                $table->timestamps();
            });
        }

        // AI Usage Logs
        if (!Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('module_name');
                $table->string('content_type');
                $table->string('service_type');
                $table->string('model_used');
                $table->integer('tokens_input')->default(0);
                $table->integer('tokens_output')->default(0);
                $table->decimal('credits_used', 10, 4)->default(0);
                $table->decimal('cost_usd', 10, 4)->default(0);
                $table->text('request_data')->nullable(); // JSON as text
                $table->text('response_data')->nullable(); // JSON as text
                $table->integer('duration_ms')->default(0);
                $table->string('status')->default('success');
                $table->text('error_message')->nullable();
                $table->string('feature_used')->nullable();
                $table->timestamps();
            });
        }

        // Package AI Credits
        if (!Schema::hasTable('package_ai_credits')) {
            Schema::create('package_ai_credits', function ($table) {
                $table->id();
                $table->unsignedBigInteger('package_id');
                $table->integer('credits_per_month')->default(0);
                $table->integer('credits_per_annual')->default(0);
                $table->boolean('is_recurring')->default(true);
                $table->integer('reset_day')->nullable();
                $table->timestamps();
            });
        }

        // AI Credit Exhaustion Logs
        if (!Schema::hasTable('ai_credit_exhaustion_logs')) {
            Schema::create('ai_credit_exhaustion_logs', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->decimal('required_credits', 15, 2);
                $table->decimal('available_credits', 15, 2);
                $table->decimal('shortfall', 15, 2);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // AI Usage By Module
        if (!Schema::hasTable('ai_usage_by_module')) {
            Schema::create('ai_usage_by_module', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('module_name');
                $table->date('date');
                $table->integer('usage_count')->default(0);
                $table->decimal('credits_used', 15, 2)->default(0);
                $table->decimal('cost_usd', 15, 4)->default(0);
                $table->timestamps();
            });
        }

        // AI Usage By Content Type
        if (!Schema::hasTable('ai_usage_by_content_type')) {
            Schema::create('ai_usage_by_content_type', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('content_type');
                $table->date('date');
                $table->integer('usage_count')->default(0);
                $table->decimal('credits_used', 15, 2)->default(0);
                $table->decimal('cost_usd', 15, 4)->default(0);
                $table->timestamps();
            });
        }

        // AI Usage Summary
        if (!Schema::hasTable('ai_usage_summary')) {
            Schema::create('ai_usage_summary', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->date('date');
                $table->integer('total_usage_count')->default(0);
                $table->decimal('total_credits_used', 15, 2)->default(0);
                $table->decimal('total_cost_usd', 15, 4)->default(0);
                $table->timestamps();
            });
        }

        // Ensure companies table exists (minimal structure)
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function ($table) {
                $table->id();
                $table->string('company_name');
                $table->string('app_name');
                $table->string('company_email');
                $table->string('company_phone')->nullable();
                $table->text('address')->nullable();
                $table->string('website')->nullable();
                $table->string('timezone')->default('UTC');
                $table->string('date_format')->default('d-m-Y');
                $table->string('time_format')->default('H:i');
                $table->string('locale')->default('en');
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('date_picker_format')->nullable();
                $table->string('moment_format')->nullable();
                $table->string('hash')->nullable();
                $table->string('logo_background_color')->default('#ffffff');
                $table->string('header_color')->nullable();
                $table->string('login_background')->nullable();
                $table->string('sidebar_logo_style')->nullable();
                $table->string('auth_theme')->default('light');
                $table->string('auth_theme_text')->nullable();
                $table->string('favicon')->nullable();
                $table->integer('datatable_row_limit')->default(25);
                $table->string('logo')->nullable();
                $table->text('headers')->nullable();
                $table->string('leaves_start_from')->default('year_start');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->unsignedInteger('currency_id')->nullable();
                $table->string('package_type')->default('monthly');
                $table->text('module_in_package')->nullable();
                $table->decimal('ai_credits_balance', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // Ensure packages table exists (minimal structure)
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('monthly_price', 15, 2)->default(0);
                $table->decimal('annual_price', 15, 2)->default(0);
                $table->integer('max_employees')->default(0);
                $table->integer('max_storage_size')->default(0);
                $table->integer('max_file_size')->default(0);
                $table->integer('billing_cycle')->default(1);
                $table->boolean('is_free')->default(false);
                $table->boolean('is_private')->default(false);
                $table->boolean('is_recommended')->default(false);
                $table->boolean('is_auto_renew')->default(true);
                $table->text('module_in_package')->nullable();
                $table->boolean('is_module_addon')->default(false);
                $table->unsignedInteger('currency_id')->nullable();
                $table->string('default')->nullable();
                $table->timestamps();
            });

            // Create a default package for CompanyObserver
            DB::table('packages')->insert([
                'name' => 'Default Package',
                'description' => 'Default package for testing',
                'monthly_price' => 0,
                'annual_price' => 0,
                'max_employees' => 10,
                'max_storage_size' => 1000,
                'max_file_size' => 10,
                'billing_cycle' => 1,
                'is_free' => true,
                'is_private' => false,
                'is_recommended' => false,
                'is_auto_renew' => true,
                'module_in_package' => json_encode([]),
                'is_module_addon' => false,
                'default' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure users table exists (minimal structure)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->unsignedInteger('company_id')->nullable();
                $table->timestamps();
            });
        }

        // Ensure global_currencies table exists (required by CompanyObserver)
        if (!Schema::hasTable('global_currencies')) {
            Schema::create('global_currencies', function ($table) {
                $table->id();
                $table->string('currency_name');
                $table->string('currency_symbol');
                $table->string('currency_code');
                $table->decimal('exchange_rate', 15, 4)->default(1);
                $table->string('currency_position')->default('left');
                $table->integer('no_of_decimal')->default(2);
                $table->string('thousand_separator')->default(',');
                $table->string('decimal_separator')->default('.');
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
            });

            // Create a default currency
            DB::table('global_currencies')->insert([
                'currency_name' => 'US Dollar',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'exchange_rate' => 1.0000,
                'currency_position' => 'left',
                'no_of_decimal' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure currencies table exists
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function ($table) {
                $table->id();
                $table->string('currency_name');
                $table->string('currency_symbol');
                $table->string('currency_code');
                $table->decimal('exchange_rate', 15, 4)->default(1);
                $table->string('currency_position')->default('left');
                $table->integer('no_of_decimal')->default(2);
                $table->string('thousand_separator')->default(',');
                $table->string('decimal_separator')->default('.');
                $table->unsignedInteger('company_id')->nullable();
                $table->timestamps();
            });
        }

        // Ensure company_addresses table exists (required by CompanyObserver)
        if (!Schema::hasTable('company_addresses')) {
            Schema::create('company_addresses', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->text('address')->nullable();
                $table->string('location')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('zip_code')->nullable();
                $table->string('country')->nullable();
                $table->unsignedInteger('country_id')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('tax_number')->nullable();
                $table->string('tax_name')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // Ensure global_invoices table exists (minimal)
        if (!Schema::hasTable('global_invoices')) {
            Schema::create('global_invoices', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->string('subscription_type')->nullable();
                $table->unsignedInteger('currency_id')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('status')->default('pending');
                $table->string('payment_gateway')->nullable();
                $table->string('gateway_transaction_id')->nullable();
                $table->timestamp('paid_on')->nullable();
                $table->text('invoice_items')->nullable();
                $table->timestamps();
            });
        }

        // Ensure global_subscriptions table exists (minimal)
        if (!Schema::hasTable('global_subscriptions')) {
            Schema::create('global_subscriptions', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->string('package_type')->nullable();
                $table->string('subscription_type')->nullable();
                $table->string('status')->default('active');
                $table->string('payment_gateway')->nullable();
                $table->timestamps();
            });
        }

        // Ensure package_settings table exists (minimal - required by Company model)
        if (!Schema::hasTable('package_settings')) {
            Schema::create('package_settings', function ($table) {
                $table->id();
                $table->string('status')->default('active');
                $table->timestamps();
            });

            // Create a default package setting record
            DB::table('package_settings')->insert([
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create minimal tables required by CompanyObserver
        self::createCompanyObserverTables();
    }

    /**
     * Create all minimal tables required by CompanyObserver when a company is created
     */
    protected static function createCompanyObserverTables(): void
    {
        // Roles table
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('display_name')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Employee shifts
        if (!Schema::hasTable('employee_shifts')) {
            Schema::create('employee_shifts', function ($table) {
                $table->id();
                $table->string('shift_name');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Attendance settings
        if (!Schema::hasTable('attendance_settings')) {
            Schema::create('attendance_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Custom field groups
        if (!Schema::hasTable('custom_field_groups')) {
            Schema::create('custom_field_groups', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('model');
                $table->unsignedInteger('company_id')->nullable();
                $table->timestamps();
            });
        }

        // Dashboard widgets
        if (!Schema::hasTable('dashboard_widgets')) {
            Schema::create('dashboard_widgets', function ($table) {
                $table->id();
                $table->string('widget_name');
                $table->string('status')->default('active');
                $table->unsignedInteger('company_id');
                $table->unsignedInteger('dashboard_type')->default(1);
                $table->timestamps();
            });
        }

        // Discussion categories
        if (!Schema::hasTable('discussion_categories')) {
            Schema::create('discussion_categories', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('color')->nullable();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Email notification settings
        if (!Schema::hasTable('email_notification_settings')) {
            Schema::create('email_notification_settings', function ($table) {
                $table->id();
                $table->string('setting_name');
                $table->string('send_email')->default('no');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Invoice settings
        if (!Schema::hasTable('invoice_settings')) {
            Schema::create('invoice_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Lead custom forms
        if (!Schema::hasTable('lead_custom_forms')) {
            Schema::create('lead_custom_forms', function ($table) {
                $table->id();
                $table->string('field_display_name');
                $table->string('field_name');
                $table->string('field_type');
                $table->unsignedInteger('field_order')->default(0);
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Lead sources
        if (!Schema::hasTable('lead_sources')) {
            Schema::create('lead_sources', function ($table) {
                $table->id();
                $table->string('type');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Leave types
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function ($table) {
                $table->id();
                $table->string('type_name');
                $table->string('color')->nullable();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Log time for
        if (!Schema::hasTable('log_time_for')) {
            Schema::create('log_time_for', function ($table) {
                $table->id();
                $table->string('log_time_for');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Message settings
        if (!Schema::hasTable('message_settings')) {
            Schema::create('message_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Project settings
        if (!Schema::hasTable('project_settings')) {
            Schema::create('project_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Slack settings
        if (!Schema::hasTable('slack_settings')) {
            Schema::create('slack_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Ticket channels
        if (!Schema::hasTable('ticket_channels')) {
            Schema::create('ticket_channels', function ($table) {
                $table->id();
                $table->string('channel_name');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Ticket types
        if (!Schema::hasTable('ticket_types')) {
            Schema::create('ticket_types', function ($table) {
                $table->id();
                $table->string('type');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Ticket setting for agents
        if (!Schema::hasTable('ticket_setting_for_agents')) {
            Schema::create('ticket_setting_for_agents', function ($table) {
                $table->id();
                $table->string('ticket_scope');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Ticket custom forms
        if (!Schema::hasTable('ticket_custom_forms')) {
            Schema::create('ticket_custom_forms', function ($table) {
                $table->id();
                $table->string('field_display_name');
                $table->string('field_name');
                $table->string('field_type');
                $table->unsignedInteger('field_order')->default(0);
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Taskboard columns
        if (!Schema::hasTable('taskboard_columns')) {
            Schema::create('taskboard_columns', function ($table) {
                $table->id();
                $table->string('column_name');
                $table->string('slug');
                $table->string('label_color')->nullable();
                $table->unsignedInteger('priority')->default(0);
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Project status settings
        if (!Schema::hasTable('project_status_settings')) {
            Schema::create('project_status_settings', function ($table) {
                $table->id();
                $table->string('status_name');
                $table->string('color')->nullable();
                $table->string('status')->default('active');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Payment gateway credentials
        if (!Schema::hasTable('payment_gateway_credentials')) {
            Schema::create('payment_gateway_credentials', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Task settings
        if (!Schema::hasTable('task_settings')) {
            Schema::create('task_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Leave settings
        if (!Schema::hasTable('leave_settings')) {
            Schema::create('leave_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Module settings
        if (!Schema::hasTable('module_settings')) {
            Schema::create('module_settings', function ($table) {
                $table->id();
                $table->string('module_name');
                $table->string('type');
                $table->string('status')->default('active');
                $table->boolean('is_allowed')->default(true);
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Theme settings
        if (!Schema::hasTable('theme_settings')) {
            Schema::create('theme_settings', function ($table) {
                $table->id();
                $table->string('panel');
                $table->string('header_color')->nullable();
                $table->string('sidebar_color')->nullable();
                $table->string('sidebar_text_color')->nullable();
                $table->unsignedInteger('company_id')->nullable();
                $table->timestamps();
            });
        }

        // Ticket email settings
        if (!Schema::hasTable('ticket_email_settings')) {
            Schema::create('ticket_email_settings', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Google calendar modules
        if (!Schema::hasTable('google_calendar_modules')) {
            Schema::create('google_calendar_modules', function ($table) {
                $table->id();
                $table->string('module_name');
                $table->string('status')->default('active');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Unit types
        if (!Schema::hasTable('unit_types')) {
            Schema::create('unit_types', function ($table) {
                $table->id();
                $table->string('unit_type');
                $table->unsignedInteger('company_id');
                $table->timestamps();
            });
        }

        // Lead pipelines
        if (!Schema::hasTable('lead_pipelines')) {
            Schema::create('lead_pipelines', function ($table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('company_id');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        // Pipeline stages
        if (!Schema::hasTable('pipeline_stages')) {
            Schema::create('pipeline_stages', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->string('color')->nullable();
                $table->unsignedInteger('pipeline_id');
                $table->unsignedInteger('company_id');
                $table->unsignedInteger('priority')->default(0);
                $table->timestamps();
            });
        }

        // Lead status
        if (!Schema::hasTable('lead_status')) {
            Schema::create('lead_status', function ($table) {
                $table->id();
                $table->string('type');
                $table->string('label_color')->nullable();
                $table->unsignedInteger('company_id');
                $table->unsignedInteger('priority')->default(0);
                $table->timestamps();
            });
        }

        // Modules table (for module settings)
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function ($table) {
                $table->id();
                $table->string('module_name');
                $table->string('description')->nullable();
                $table->boolean('is_superadmin')->default(false);
                $table->timestamps();
            });
        }

        // Countries table (for company addresses)
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('iso')->nullable();
                $table->string('phonecode')->nullable();
                $table->timestamps();
            });
        }

        // Permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('display_name')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('module_id')->nullable();
                $table->timestamps();
            });
        }

        // Permission role pivot table
        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function ($table) {
                $table->id();
                $table->unsignedInteger('permission_id');
                $table->unsignedInteger('role_id');
                $table->timestamps();
            });
        }

        // Role user pivot table
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function ($table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('company_id')->nullable();
                $table->timestamps();
            });
        }

        // Package update notify
        if (!Schema::hasTable('package_update_notifies')) {
            Schema::create('package_update_notifies', function ($table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->timestamps();
            });
        }
    }
}

