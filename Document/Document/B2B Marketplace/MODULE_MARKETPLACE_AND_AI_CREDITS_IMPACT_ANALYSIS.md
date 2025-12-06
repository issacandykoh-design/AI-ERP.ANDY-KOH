# Module Marketplace & AI Credits System - Comprehensive Impact Analysis

## Executive Summary

This document outlines the complete impact analysis for a hybrid billing system that combines:
1. **Package System (KEPT)** - Existing packages remain, backward compatible
2. **Module Marketplace (ADD-ON)** - Customers can add individual modules to packages or purchase standalone
3. **AI Credits System** - Integrated into billing with promotional offers (e.g., buy $100 get $20 credit bonus)
4. **Unified Billing** - Packages handle all billing: base package + module add-ons + AI credits + promotions

**Key Architecture Decision:**
- **Packages remain the primary billing unit**
- **Modules are add-ons** that can be added to any package
- **AI credits** can be included in packages OR purchased separately
- **Promotional offers** for credit purchases (buy $X get $Y credit bonus)
- **All billing flows through the package subscription system**

**Risk Level:** 🔴 **CRITICAL** - This is a major enhancement to billing system while maintaining backward compatibility.

**Estimated Effort:** 14-18 weeks (3.5-4.5 months)
**Estimated Impact:** 20+ modules, 140+ files, 18+ database tables

---

## 1. Database Impact Analysis

### 1.1 New Tables Required (18 tables)

#### Module Marketplace Tables (8 tables)

**1. `marketplace_modules`**
- Store marketplace module listings
- Fields: id, module_key, name, description, version, author, price_monthly, price_annual, price_lifetime, category, tags, icon, screenshots, documentation_url, support_url, is_featured, is_active, rating, download_count, created_at, updated_at

**2. `company_module_subscriptions`**
- Track which modules each company has subscribed to (as add-ons to packages or standalone)
- Fields: id, company_id, package_id (nullable - if add-on to package), marketplace_module_id, subscription_type (monthly/annual/lifetime), status (active/inactive/expired/cancelled), subscribed_at, expires_at, cancelled_at, auto_renew, payment_gateway, gateway_subscription_id, is_addon (boolean - true if added to package), created_at, updated_at

**3. `module_subscription_items`**
- Track subscription items for payment gateways (Stripe, etc.)
- Fields: id, company_module_subscription_id, gateway_item_id, price_id, quantity, created_at, updated_at

**4. `module_purchases`**
- Track one-time module purchases (lifetime)
- Fields: id, company_id, marketplace_module_id, amount, currency_id, payment_gateway, transaction_id, status, purchased_at, created_at, updated_at

**5. `module_usage_tracking`**
- Track module usage for analytics
- Fields: id, company_id, marketplace_module_id, usage_date, feature_used, usage_count, created_at

**6. `module_reviews`**
- Customer reviews for modules
- Fields: id, company_id, marketplace_module_id, rating (1-5), review_text, is_verified_purchase, created_at, updated_at

**7. `module_categories`**
- Categories for organizing modules
- Fields: id, name, slug, description, icon, sort_order, parent_id, created_at, updated_at

**8. `module_dependencies`**
- Track module dependencies (Module A requires Module B)
- Fields: id, marketplace_module_id, required_module_id, is_optional, created_at

#### AI Credits System Tables (10 tables)

**9. `ai_credit_packages`**
- Predefined credit packages for purchase
- Fields: id, name, description, credits_amount, price, currency_id, bonus_credits, is_active, sort_order, created_at, updated_at

**9a. `ai_credit_promotions`** (NEW)
- Promotional offers for credit purchases (e.g., buy $100 get $20 credit bonus)
- Fields: id, name, description, minimum_purchase_amount, bonus_credits_amount, bonus_percentage (alternative to fixed amount), start_date, end_date, is_active, applicable_to_packages (JSON array of package IDs, null = all), max_uses_per_company, total_max_uses, created_at, updated_at

**10. `company_ai_credits`**
- Track AI credits balance per company
- Fields: id, company_id, balance, lifetime_earned, lifetime_spent, monthly_credits_from_package (credits included in package subscription), last_updated_at, created_at, updated_at

**10a. `package_ai_credits`** (NEW)
- Track AI credits included in packages (e.g., Pro Package includes 1000 credits/month)
- Fields: id, package_id, credits_per_month, credits_per_annual, is_recurring (credits reset monthly), reset_day (day of month credits reset), created_at, updated_at

**11. `ai_credit_transactions`**
- All credit transactions (purchases, usage, refunds, bonuses)
- Fields: id, company_id, transaction_type (purchase/usage/refund/bonus/expiry), amount (positive for credit, negative for debit), balance_after, reference_type (purchase_id/usage_id), reference_id, description, created_at

**12. `ai_credit_purchases`**
- Track credit purchases (standalone or as part of package billing)
- Fields: id, company_id, package_id (nullable - if purchased with package), ai_credit_package_id, promotion_id (nullable - if used promotion), credits_amount, bonus_credits_amount, price, currency_id, payment_gateway, transaction_id, status, purchased_at, invoice_id (link to global_invoices), created_at, updated_at

**13. `ai_usage_logs`**
- Detailed logs of AI API usage with module and content type tracking
- Fields: id, company_id, user_id, module_name (invoices/projects/tasks/etc), content_type (text/image/video/music), service_type (translation/chat/completion/generation/etc), model_used, tokens_input, tokens_output, credits_used, cost_usd, request_data (JSON), response_data (JSON), duration_ms, status, error_message, feature_used (e.g., "invoice_description_generation"), created_at

**14. `ai_usage_summary`**
- Daily/monthly summaries for performance with module and content type breakdown
- Fields: id, company_id, usage_date, module_name, content_type, service_type, total_requests, total_tokens, total_credits, total_cost, created_at, updated_at

**15a. `ai_usage_by_module`** (NEW)
- Aggregated usage statistics by module
- Fields: id, company_id, module_name, period (daily/weekly/monthly), total_requests, total_tokens, total_credits, total_cost, text_credits, image_credits, video_credits, music_credits, created_at, updated_at

**15b. `ai_usage_by_content_type`** (NEW)
- Aggregated usage statistics by content type
- Fields: id, company_id, content_type (text/image/video/music), period (daily/weekly/monthly), total_requests, total_tokens, total_credits, total_cost, created_at, updated_at

**16. `ai_rate_limits`**
- Rate limits per company for AI usage
- Fields: id, company_id, service_type, requests_per_minute, requests_per_hour, requests_per_day, tokens_per_minute, tokens_per_hour, tokens_per_day, created_at, updated_at

**17. `ai_credit_exhaustion_logs`** (NEW)
- Track when companies run out of credits and what they tried to do
- Fields: id, company_id, user_id, module_name, content_type, service_type, attempted_action, credits_needed, credits_available, blocked_at, notification_sent, created_at

### 1.2 Existing Table Modifications (10 tables)

**1. `companies` table**
- Add: `module_subscription_count`, `total_module_spend`, `ai_credits_balance` (denormalized for performance), `monthly_ai_credits_from_package` (credits from package subscription)
- Modify: Keep `package_id` - packages remain primary billing unit

**2. `packages` table**
- Add: `includes_ai_credits` (boolean), `ai_credits_monthly` (credits included per month), `ai_credits_annual` (credits included per year), `allows_module_addons` (boolean), `max_module_addons` (nullable - max modules that can be added)
- Modify: Packages remain primary billing unit, modules are add-ons
- Keep all existing fields for backward compatibility

**3. `global_subscriptions` table**
- Add: `subscription_type` enum (package/package_with_modules/standalone_module), `module_addons_count` (number of module add-ons), `total_monthly_ai_credits` (package credits + purchased credits)
- Modify: `package_id` remains required (packages are primary), modules are tracked separately as add-ons

**4. `modules` table**
- Add: `marketplace_module_id` (FK), `is_marketplace_enabled`, `pricing_type` (free/paid/subscription)
- Modify: Add metadata fields

**5. `users` table**
- Add: `preferred_modules` (JSON), `module_preferences` (JSON)

**6. `global_invoices` table**
- Add: `invoice_type` enum (package/package_with_addons/module_addon/credits/mixed), `module_addons_total` (total cost of module add-ons), `ai_credits_total` (total cost of AI credits), `promotion_applied_id` (if credit promotion used), `line_items` (JSON - detailed breakdown of package + modules + credits)
- Modify: `package_id` remains required, invoice shows package + add-ons + credits breakdown

**7. `payment_gateway_credentials` table**
- Add: Module subscription webhook URLs, AI credits webhook URLs

**8. `settings` table**
- Add: Module marketplace settings, AI credits settings

**9. `notifications` table**
- Add: Module subscription notifications, AI credit low balance notifications

**10. `audit_logs` table**
- Add: Module subscription events, AI credit transaction events

### 1.3 Migration Strategy

**Phase 1: Add New Tables (Week 1)**
- Create all 15 new tables
- Add indexes for performance
- Set up foreign keys

**Phase 2: Modify Existing Tables (Week 2)**
- Add new columns to existing tables
- Make nullable where needed for backward compatibility
- Add indexes

**Phase 3: Data Migration (Week 3)**
- Migrate existing package subscriptions to module subscriptions
- Create marketplace module entries from existing modules
- Initialize AI credits for existing companies

**Total Migrations:** 25+ migration files

---

## 2. Module & Feature Impact Analysis

### 2.1 Core Modules Affected

| Module | Impact Level | Changes Required |
|--------|-------------|------------------|
| **Billing/Subscription** | 🔴 Critical | Complete rewrite - module subscriptions instead of packages |
| **Package Management** | 🔴 Critical | Convert to module marketplace |
| **Payment Processing** | 🔴 Critical | Support module subscriptions, AI credit purchases |
| **Company Management** | 🔴 Critical | Module subscription tracking, AI credits display |
| **Module System** | 🔴 Critical | Marketplace integration, subscription checks |
| **AI Services** | 🔴 Critical | Credit-based usage, rate limiting |
| **Invoices** | 🔴 Critical | Module subscription invoices, credit purchase invoices |
| **Reports/Analytics** | 🟡 Medium | Module usage reports, AI usage reports |
| **Notifications** | 🟡 Medium | Module subscription notifications, low credit alerts |
| **Settings** | 🟡 Medium | Module marketplace settings, AI credits settings |
| **Admin Panel** | 🔴 Critical | Module marketplace management, AI credits management |
| **User Panel** | 🔴 Critical | Module selection, credit purchase UI |
| **Public Pages** | 🟡 Medium | Module marketplace browsing (if public) |
| **REST API** | 🔴 Critical | Module subscription API, AI credits API |
| **Webhooks** | 🟡 Medium | Module subscription webhooks, credit purchase webhooks |
| **Email Templates** | 🟡 Medium | Module subscription emails, credit purchase emails |
| **Dashboard** | 🟡 Medium | Module usage widgets, AI usage widgets |
| **Permissions** | 🟡 Medium | Module access permissions based on subscriptions |
| **Multi-Tenant** | 🔴 Critical | Module isolation per company, credit isolation |
| **Localization** | 🟡 Medium | Module marketplace translations, AI credits translations |

### 2.2 Modules That May NOT Need Changes

- **Core Modules** (clients, employees, projects, tasks) - No changes if they remain free
- **File Storage** - No changes needed
- **Basic Settings** - No changes needed
- **Authentication** - No changes needed (unless module-gated)

---

## 3. File Modification Inventory

### 3.1 Controllers (25+ files)

**New Controllers:**
1. `ModuleMarketplaceController.php` - Browse, search, purchase modules (as add-ons)
2. `ModuleAddonController.php` - Manage module add-ons to packages (NEW)
3. `AICreditsController.php` - Purchase, view credits, usage history
4. `AICreditPackageController.php` - Manage credit packages (admin)
5. `AICreditPromotionController.php` - Manage promotional offers (admin) (NEW)
6. `ModuleReviewController.php` - Module reviews and ratings
7. `ModuleCategoryController.php` - Module categories management
8. `AIUsageProfileController.php` - User profile/usage dashboard (NEW)
9. `AICreditExhaustionController.php` - Handle credit exhaustion scenarios (NEW)
10. `PackageBillingController.php` - Unified billing for packages + add-ons + credits (NEW)

**Modified Controllers:**
1. `BillingController.php` - Unified billing: Package + Module Add-ons + AI Credits
2. `PackageController.php` - Add module add-on management, AI credits in packages
3. `CompanyController.php` - Show package + module add-ons + AI credits
4. `ModuleController.php` - Add marketplace integration (as add-ons)
5. `PaymentController.php` - Unified payment processing
6. `InvoiceController.php` - Unified invoices with line items (Package + Add-ons + Credits)
7. `SettingsController.php` - Add module marketplace settings, credit promotion settings
8. `DashboardController.php` - Add module usage widgets, credit balance
9. `ReportController.php` - Add module usage reports, credit usage reports
10. `NotificationController.php` - Add module add-on notifications, credit purchase notifications
11. `AiSettingController.php` - Add credit system integration
12. `AiTranslationService.php` - Add credit deduction
13. All payment gateway controllers (Stripe, PayPal, Razorpay, etc.) - Unified billing support

### 3.2 Models (20+ files)

**New Models:**
1. `MarketplaceModule.php`
2. `CompanyModuleSubscription.php` (tracks add-ons)
3. `ModuleSubscriptionItem.php`
4. `ModulePurchase.php`
5. `ModuleUsageTracking.php`
6. `ModuleReview.php`
7. `ModuleCategory.php`
8. `ModuleDependency.php`
9. `AICreditPackage.php`
10. `AICreditPromotion.php` (NEW)
11. `CompanyAICredits.php`
12. `AICreditTransaction.php`
13. `AICreditPurchase.php`
14. `PackageAICredits.php` (NEW - credits included in packages)
15. `AIUsageLog.php`
16. `AIUsageSummary.php`
17. `AIRateLimit.php`

**Modified Models:**
1. `Company.php` - Add module add-on relationships, AI credits from package
2. `Package.php` - Add AI credits fields, module add-on support
3. `GlobalSubscription.php` - Support package + module add-ons + AI credits
4. `Module.php` - Add marketplace integration (as add-ons)
5. `GlobalInvoice.php` - Unified invoice with line items (Package + Add-ons + Credits)
6. `User.php` - Add module preferences

### 3.3 Services (10+ files)

**New Services:**
1. `ModuleMarketplaceService.php` - Module marketplace logic (as add-ons)
2. `ModuleAddonService.php` - Manage module add-ons to packages (NEW)
3. `AICreditsService.php` - Credit management and deduction
4. `AIUsageTrackingService.php` - Track and log AI usage (with module and content type)
5. `ModuleDependencyService.php` - Handle module dependencies
6. `AIUsageAnalyticsService.php` - Generate usage analytics and breakdowns (NEW)
7. `AICreditExhaustionService.php` - Handle credit exhaustion scenarios (NEW)
8. `AIContentTypeCostCalculator.php` - Calculate costs by content type (NEW)
9. `AICreditPromotionService.php` - Handle promotional offers (NEW)
10. `PackageBillingService.php` - Unified billing: Package + Add-ons + Credits (NEW)
11. `PackageAICreditsService.php` - Manage AI credits included in packages (NEW)

**Modified Services:**
1. `PaymentService.php` - Add module subscription payments
2. `BillingService.php` - Add module subscription billing
3. `SubscriptionService.php` - Support both packages and modules
4. `AiTranslationService.php` - Add credit deduction

### 3.4 Views/Blade Templates (30+ files)

**New Templates:**
1. `marketplace/index.blade.php` - Module marketplace listing (as add-ons)
2. `marketplace/show.blade.php` - Module details page
3. `packages/add-modules.blade.php` - Add modules to package (NEW)
4. `packages/billing-summary.blade.php` - Unified billing summary (Package + Add-ons + Credits) (NEW)
5. `modules/addons/index.blade.php` - My module add-ons
6. `modules/addons/show.blade.php` - Module add-on details
7. `ai-credits/index.blade.php` - AI credits dashboard
8. `ai-credits/purchase.blade.php` - Purchase credits page (with promotions)
9. `ai-credits/promotions.blade.php` - Available promotions (NEW)
10. `ai-credits/usage-history.blade.php` - Usage history
11. `ai-credits/profile.blade.php` - User profile/usage dashboard (NEW)
12. `ai-credits/module-breakdown.blade.php` - Module-based usage breakdown (NEW)
13. `ai-credits/content-type-breakdown.blade.php` - Content type usage breakdown (NEW)
14. `ai-credits/exhaustion-modal.blade.php` - Credit exhaustion modal (NEW)
15. `admin/module-marketplace/index.blade.php` - Admin marketplace management
16. `admin/ai-credits/packages.blade.php` - Credit packages management
17. `admin/ai-credits/promotions.blade.php` - Manage promotions (NEW)
18. `admin/packages/ai-credits.blade.php` - Configure AI credits in packages (NEW)
19. `partials/ai-credits-balance-header.blade.php` - Balance display in header (NEW)
20. `invoices/unified-invoice.blade.php` - Invoice showing Package + Add-ons + Credits (NEW)

**Modified Templates:**
1. All billing templates - Add module subscription options
2. Company settings - Add module subscriptions section
3. Dashboard - Add module usage widgets
4. Settings pages - Add module marketplace settings
5. Invoice templates - Support module subscription invoices
6. Payment pages - Support module subscription payments

### 3.5 JavaScript Files (10+ files)

**New JavaScript:**
1. `module-marketplace.js` - Marketplace interactions
2. `module-subscription.js` - Subscription management
3. `ai-credits.js` - Credit purchase and display
4. `ai-usage-tracker.js` - Real-time usage tracking
5. `ai-usage-profile.js` - User profile/usage dashboard interactions (NEW)
6. `ai-credit-exhaustion.js` - Handle credit exhaustion UI (NEW)
7. `ai-usage-charts.js` - Chart rendering for usage analytics (NEW)

**Modified JavaScript:**
1. `custom.js` - Add module subscription handlers
2. `billing.js` - Add module subscription payment flows
3. `dashboard.js` - Add module usage widgets

### 3.6 API Controllers (5+ files)

**New API Controllers:**
1. `ModuleMarketplaceApiController.php`
2. `ModuleSubscriptionApiController.php`
3. `AICreditsApiController.php`
4. `AIUsageProfileApiController.php` - Usage analytics API (NEW)
5. `AICreditExhaustionApiController.php` - Credit exhaustion handling API (NEW)

**Modified API Controllers:**
1. `BillingApiController.php` - Add module subscriptions
2. `CompanyApiController.php` - Add module subscription data

### 3.7 Observers & Events (8+ files)

**New Observers:**
1. `ModuleSubscriptionObserver.php`
2. `AICreditTransactionObserver.php`
3. `AIUsageLogObserver.php`

**New Events:**
1. `ModuleSubscribed.php`
2. `ModuleUnsubscribed.php`
3. `AICreditsPurchased.php`
4. `AICreditsLowBalance.php` (20%, 10%, 5%)
5. `AICreditsExhausted.php` - When credits run out (NEW)
6. `AICreditsBlocked.php` - When API call blocked due to no credits (NEW)
7. `AIUsageLogged.php` - When AI usage is logged (with module/content type) (NEW)

### 3.8 Middleware (3+ files)

**New Middleware:**
1. `CheckModuleSubscription.php` - Verify module access
2. `CheckAICredits.php` - Verify AI credits available
3. `ModuleRateLimit.php` - Rate limiting for modules

### 3.9 Routes (5+ files)

**New Route Groups:**
1. Module marketplace routes
2. Module subscription routes
3. AI credits routes
4. API routes for modules and credits

**Total Files to Modify/Create:** 140+ files (increased due to module/content type tracking and usage profile)

---

## 4. Business Logic Changes

### 4.1 Subscription System Transformation (Hybrid Approach)

**Current Flow:**
1. Company selects package
2. Package contains multiple modules (JSON array)
3. Company subscribes to entire package
4. All modules in package are enabled

**New Flow (Hybrid - Packages + Modules):**
1. Company selects base package (packages remain primary)
2. Package includes base modules (as before)
3. Company can browse module marketplace for add-ons
4. Company can add individual modules as add-ons to their package
5. Modules can be:
   - Included in package (existing behavior)
   - Added as add-ons to package (new)
   - Purchased standalone (new - for companies without packages)
6. Each module add-on has its own pricing (monthly/annual/lifetime)
7. Modules can have dependencies (Module A requires Module B)
8. Company subscribes to: Base Package + Module Add-ons
9. Payment processed: Single invoice with line items (Package + Module Add-ons + AI Credits)
10. All billing flows through package subscription system

**Key Business Rules:**
- **Packages remain primary billing unit** - All subscriptions tied to a package
- **Modules are add-ons** - Can be added to any package
- **Package can include AI credits** - e.g., "Pro Package includes 1000 AI credits/month"
- **AI credits can be purchased separately** - In addition to package credits
- **Promotional offers** - Buy $100 get $20 credit bonus
- **Unified billing** - One invoice shows: Package + Module Add-ons + AI Credits
- **Proration** - When adding/removing module add-ons mid-cycle
- **Auto-renewal** - Package + all add-ons renew together

**Key Business Rules:**
- Modules can be free or paid
- Modules can have dependencies
- Subscription types: monthly, annual, lifetime
- Auto-renewal per module
- Proration when adding/removing modules mid-cycle
- Grace period for expired subscriptions
- Module access revoked when subscription expires
- Trial periods for modules (optional)

### 4.2 AI Credits System

**Current Flow:**
1. AI API calls made directly
2. No usage tracking or billing

**New Flow:**
1. Company purchases AI credit packages
2. Credits added to company balance
3. Every AI API call deducts credits
4. Credit cost based on model, tokens, and content type
5. Real-time balance checking before API calls
6. Low balance warnings
7. Usage tracking and analytics (by module and content type)
8. Rate limiting per company
9. Credit exhaustion handling

**Key Business Rules:**

**Credit Packages:**
- Credit packages with different amounts and prices
- Bonus credits for larger purchases
- Credit expiration (optional, e.g., 12 months)
- Minimum credit balance for API calls

**AI Credits in Packages:**
- Packages can include monthly/annual AI credits
- Credits reset monthly (if recurring)
- Credits accumulate if not used (if non-recurring)
- Example: "Pro Package includes 1000 AI credits/month"
- Credits from package + purchased credits = total balance

**Promotional Offers:**
- Buy $X get $Y credit bonus
- Percentage-based: Buy $100 get 20% bonus credits
- Fixed amount: Buy $100 get $20 credit bonus
- Time-limited promotions
- Package-specific promotions (e.g., only for Pro Package)
- Usage limits per company
- Total usage limits (first 100 customers)

**Credit Cost Calculation:**
- Base cost per token (varies by model)
- Input tokens vs output tokens (different rates)
- Content type multipliers:
  - Text: 1x (base rate)
  - Image: 2-5x (depending on resolution/complexity)
  - Video: 5-10x (depending on duration/resolution)
  - Music: 3-8x (depending on duration/quality)
- Currency conversion

**Module-Based Tracking:**
- Track credits used per module (invoices, projects, tasks, etc.)
- Show breakdown: "Invoice module used 150 credits this month"
- Module-specific usage analytics
- Module-specific cost allocation

**Content Type Tracking:**
- Track credits by content type: text, image, video, music
- Show breakdown: "Text: 200 credits, Image: 150 credits, Video: 50 credits"
- Content type-specific analytics
- Different pricing per content type

**Rate Limits:**
- Requests per minute/hour/day
- Tokens per minute/hour/day
- Per content type limits (optional)

**Usage Tracking:**
- Per module (invoices, projects, tasks, etc.)
- Per content type (text, image, video, music)
- Per service type (translation, chat, generation, etc.)
- Per model used
- Per company/user
- Daily/monthly summaries
- Real-time usage dashboard

**Credit Exhaustion Handling:**
- When credits run out:
  1. Block the AI API call immediately
  2. Show user-friendly error message
  3. Log the attempted action (module, content type, credits needed)
  4. Send notification to company admin
  5. Show "Purchase Credits" prompt with quick purchase options
  6. Offer "Buy Now" button that redirects to credit purchase
  7. Show estimated cost for the action they tried
  8. Option to set up auto-purchase when credits low
  9. Grace period (optional): Allow 1-2 more requests with warning
  10. Queue the request (optional): Save request, execute when credits added

### 4.3 Payment Processing Changes (Unified Billing)

**Package + Module Add-ons + AI Credits:**
- **Single subscription** for Package + Module Add-ons
- **Unified invoice** showing:
  - Base Package: $99/month
  - Module Add-ons: $20/month (Invoice AI), $15/month (Project AI)
  - AI Credits: $50 (one-time purchase with $10 bonus)
  - **Total: $184/month + $50 one-time**
- **Recurring billing** for Package + Module Add-ons
- **One-time purchases** for AI credits (or recurring if part of package)
- **Proration calculations** when adding/removing module add-ons
- **Upgrade/downgrade handling** for packages and add-ons
- **Cancellation and refunds** - Can cancel package or individual add-ons
- **Multiple payment gateways** - All through package subscription

**Promotional Offers:**
- Apply promotion at checkout
- Calculate bonus credits automatically
- Show promotion details on invoice
- Track promotion usage
- Validate promotion eligibility (dates, package, limits)

**Billing Scenarios:**
1. **New Subscription:**
   - Select package ($99/month)
   - Add module add-ons ($20 + $15 = $35/month)
   - Purchase AI credits ($50 with $10 bonus)
   - Total: $134/month recurring + $50 one-time

2. **Existing Subscription - Add Module:**
   - Current: Package ($99/month)
   - Add module add-on ($20/month)
   - Prorate for remaining days
   - Next invoice: $99 + $20 = $119/month

3. **Existing Subscription - Purchase Credits:**
   - Current: Package ($99/month) with 1000 credits/month included
   - Purchase additional credits ($100 with $20 bonus)
   - Credits added instantly
   - One-time charge on next invoice

4. **Promotional Offer:**
   - Purchase $100 AI credits
   - Promotion: "Buy $100 get $20 credit bonus"
   - Receive 120 credits total
   - Promotion shown on invoice

### 4.4 Access Control Changes

**Module Access:**
- Check module subscription before allowing access
- Grace period for expired subscriptions
- Dependency checking (can't use Module A without Module B)
- Permission-based access within modules

**AI Access:**
- Check credit balance before API calls
- Block API calls if insufficient credits
- Show credit balance in UI
- Low balance warnings

---

## 5. Frontend Changes Required (UI/UX & User Experience)

### 5.1 Module Marketplace UI

**User Workflows:**
1. **Browse Modules:**
   - Category filtering
   - Search functionality
   - Sort by price, rating, popularity
   - Featured modules section
   - Recently added modules
   - Module cards with preview

2. **Module Details Page:**
   - Module description
   - Screenshots/demo
   - Pricing options (monthly/annual/lifetime)
   - Reviews and ratings
   - Dependencies list
   - Installation instructions
   - Support links
   - "Add to Cart" or "Subscribe Now"

3. **Module Cart/Checkout:**
   - Selected modules list
   - Total price calculation
   - Dependency warnings
   - Payment method selection
   - Subscription confirmation

4. **My Modules:**
   - Active subscriptions list
   - Expiring soon warnings
   - Usage statistics
   - Renewal settings
   - Cancel subscription

**UI/UX Considerations:**
- Responsive design (mobile, tablet, desktop)
- Loading states for module installation
- Empty states (no modules subscribed)
- Error handling (payment failures, installation errors)
- Success feedback (subscription confirmed)
- Search/filter UX (quick filters, saved searches)
- Module comparison (compare features/pricing)
- Wishlist functionality
- Module recommendations

### 5.2 AI Credits UI & User Profile/Usage Dashboard

**User Workflows:**

1. **Credits Dashboard:**
   - Current balance display (prominent)
   - Usage statistics (today, this month, this year)
   - Credit packages available
   - Quick purchase buttons
   - Usage history link
   - Low balance warnings

2. **User Profile/Usage Dashboard (NEW):**
   - **Overview Section:**
     - Total credits used (lifetime)
     - Total credits purchased (lifetime)
     - Current balance
     - Credits used this month
     - Credits remaining this month
     - Average daily usage
     - Projected credits needed for month
   
   - **Module Breakdown Section:**
     - Credits used per module (pie chart/bar chart)
     - Top modules by usage
     - Module-specific usage trends
     - Example: "Invoice module: 450 credits (30% of total)"
     - Click to see detailed module usage
   
   - **Content Type Breakdown Section:**
     - Credits used by content type (pie chart/bar chart)
     - Text: X credits (X%)
     - Image: X credits (X%)
     - Video: X credits (X%)
     - Music: X credits (X%)
     - Content type usage trends
     - Cost per content type
   
   - **Combined Module + Content Type Matrix:**
     - Table showing: Module × Content Type breakdown
     - Example table:
       ```
       | Module    | Text | Image | Video | Music | Total |
       |-----------|------|-------|-------|-------|-------|
       | Invoices  | 200  | 150   | 0     | 0     | 350   |
       | Projects  | 100  | 50    | 30    | 0     | 180   |
       | Tasks     | 50   | 0     | 0     | 0     | 50    |
       | Total     | 350  | 200   | 30    | 0     | 580   |
       ```
   
   - **Time Period Filters:**
     - Today, This Week, This Month, This Year, Custom Range
     - Compare periods (e.g., this month vs last month)
   
   - **Usage Trends:**
     - Line chart showing credits used over time
     - Daily/weekly/monthly views
     - Module-specific trends
     - Content type-specific trends
   
   - **Top Features Used:**
     - List of most-used AI features
     - Credits per feature
     - Usage frequency
   
   - **Cost Analysis:**
     - Total cost (in USD/local currency)
     - Cost per module
     - Cost per content type
     - Average cost per request
     - Cost trends over time

3. **Purchase Credits:**
   - Credit packages grid
   - Bonus credits highlighted
   - Price comparison
   - Payment method selection
   - Instant credit addition confirmation
   - "Recommended package" based on usage

4. **Usage History (Detailed):**
   - Detailed usage logs with filters:
     - By module (invoices, projects, etc.)
     - By content type (text, image, video, music)
     - By date range
     - By service type
     - By model
     - By user (if multi-user)
   - Export functionality (CSV, Excel, PDF)
   - Cost breakdown
   - Usage trends charts
   - Search functionality

5. **Credit Exhaustion Handling UI:**
   - **When Credits Run Out:**
     - Modal/Toast notification: "You've run out of credits"
     - Show what they tried to do: "Attempted to generate invoice description (needed 5 credits)"
     - Current balance: 0 credits
     - Quick actions:
       - "Purchase Credits" button (primary)
       - "View Usage" button (secondary)
       - "Set Auto-Purchase" link
     - Show recommended package based on usage
     - Estimated cost for the action they tried
   
   - **Low Balance Warnings:**
     - In-app notifications (when < 20%, 10%, 5%)
     - Email alerts
     - Auto-purchase suggestions
     - Quick top-up buttons in header/navbar
     - Balance indicator in header (always visible)

6. **Real-Time Usage Display:**
   - Balance in header/navbar (always visible)
   - Usage indicator (credits used today/this month)
   - Quick access to purchase credits
   - Low balance indicator (red when < 10%)

**UI/UX Considerations:**
- Real-time balance updates (WebSocket/Polling)
- Usage progress bars (daily/monthly)
- Cost estimation before API calls
- Rate limit indicators
- Usage analytics visualization (charts, graphs)
- Mobile-friendly credit purchase
- Quick top-up buttons
- Export functionality for reports
- Print-friendly usage reports
- Dark mode support
- Responsive design (mobile, tablet, desktop)
- Loading states for data fetching
- Empty states (no usage yet)
- Error handling (API failures, data loading errors)
- Tooltips explaining credit costs
- Help/documentation links

### 5.3 Integration Points

**Settings Pages:**
- Module subscriptions section
- AI credits settings
- Auto-renewal preferences
- Notification preferences

**Dashboard Widgets:**
- Active modules count
- AI credits balance
- Module usage statistics
- AI usage statistics

**Navigation:**
- "Marketplace" menu item
- "My Modules" menu item
- "AI Credits" menu item
- Module badges/indicators

---

## 6. Panel Features & User Roles Impact

### 6.1 Admin Panel (Super Admin)

**New Features:**
1. **Module Marketplace Management:**
   - Create/edit module listings
   - Set pricing (monthly/annual/lifetime)
   - Upload screenshots/icons
   - Manage categories
   - Approve/reject module submissions (if third-party)
   - View module analytics

2. **AI Credits Management:**
   - Create/edit credit packages
   - Set pricing and bonuses
   - View all credit transactions
   - Manage rate limits
   - View usage analytics
   - Adjust credit balances (manual adjustments)

3. **Subscription Management:**
   - View all module subscriptions
   - Manual subscription management
   - Refund handling
   - Subscription analytics

**Menu/Navigation:**
- "Module Marketplace" → "Manage Modules"
- "Module Marketplace" → "Categories"
- "AI Credits" → "Credit Packages"
- "AI Credits" → "Transactions"
- "Subscriptions" → "Module Subscriptions"

**Dashboard Widgets:**
- Total modules in marketplace
- Total active subscriptions
- Revenue from modules
- AI credits sold
- Top modules by revenue
- AI usage statistics

### 6.2 User Panel (Company Admin)

**New Features:**
1. **Module Marketplace:**
   - Browse available modules
   - Search and filter
   - View module details
   - Subscribe to modules
   - Manage subscriptions

2. **My Modules:**
   - View active subscriptions
   - Renew/cancel subscriptions
   - View usage statistics
   - Manage auto-renewal

3. **AI Credits:**
   - View balance
   - Purchase credits
   - View usage history
   - Set low balance alerts

**Menu/Navigation:**
- "Marketplace" (new top-level menu)
- "Settings" → "Module Subscriptions"
- "Settings" → "AI Credits"

**Dashboard Widgets:**
- Active modules count
- AI credits balance
- Module usage this month
- AI usage this month

### 6.3 Regular Users

**Access:**
- View subscribed modules (read-only)
- Use modules (if permissions allow)
- View AI credits balance (read-only)
- Use AI features (if credits available)

**Restrictions:**
- Cannot purchase modules (admin only)
- Cannot purchase credits (admin only)
- Cannot manage subscriptions (admin only)

### 6.4 Public/Guest Users

**Access (if marketplace is public):**
- Browse module marketplace
- View module details
- See pricing
- Cannot purchase (must be logged in)

---

## 7. Billing, Subscription & Payment Impact

### 7.1 Subscription Changes

**Current:**
- One subscription per company (package-based)
- Monthly or annual billing
- All modules included in package

**New:**
- Multiple subscriptions per company (one per module)
- Mixed billing cycles (some monthly, some annual)
- Individual module pricing
- Proration when adding/removing modules
- Bundle discounts (optional)

### 7.2 Payment Processing

**Module Subscriptions:**
- Recurring subscriptions per module (Stripe, PayPal, etc.)
- One-time purchases for lifetime modules
- Proration calculations
- Upgrade/downgrade handling
- Cancellation and refunds
- Multiple payment methods per company

**AI Credit Purchases:**
- One-time payments
- Instant credit addition
- No recurring billing
- Refund policy (if credits unused)

### 7.3 Invoice Generation

**New Invoice Types:**
1. Module subscription invoices (recurring)
2. Module purchase invoices (one-time)
3. AI credit purchase invoices
4. Mixed invoices (modules + credits)

**Invoice Details:**
- Line items per module
- Subscription period
- Proration details
- Credit package details
- Payment method
- Auto-renewal status

### 7.4 Billing History

**Track:**
- All module subscription payments
- All credit purchases
- Refunds
- Prorations
- Failed payments
- Payment retries

### 7.5 Currency Handling

- Multi-currency support for modules
- Multi-currency support for credits
- Exchange rate conversion
- Currency-specific pricing

---

## 8. User Notifications & Communications

### 8.1 Email Notifications

**Module Subscriptions:**
1. Subscription confirmed
2. Subscription renewed
3. Subscription expiring soon (7 days, 3 days, 1 day)
4. Subscription expired
5. Subscription cancelled
6. Payment failed
7. Module installed
8. Module uninstalled

**AI Credits:**
1. Credits purchased
2. Low balance warning (20%, 10%, 5%)
3. Credits depleted
4. Credits exhausted (blocked action) - NEW
5. Usage limit reached
6. Rate limit exceeded
7. Module usage summary (weekly/monthly) - NEW
8. Content type usage summary (weekly/monthly) - NEW

**Templates Needed:**
- 15+ new email templates
- Update existing billing emails

### 8.2 In-App Notifications

**Module Subscriptions:**
- Subscription status changes
- New modules available
- Module updates available
- Expiring subscriptions

**AI Credits:**
- Low balance alerts (20%, 10%, 5%)
- Credit purchase confirmations
- Credits exhausted (blocked action) - NEW
- Usage limit warnings
- Rate limit warnings
- Module usage highlights (top modules) - NEW
- Content type usage highlights - NEW

### 8.3 SMS Notifications (Optional)

- Critical: Subscription expired
- Critical: Credits depleted
- Important: Payment failed

---

## 9. Multi-Tenant & Company Isolation

### 9.1 Module Isolation

- Each company has independent module subscriptions
- Module data isolated per company
- Module settings per company
- Module usage tracking per company

### 9.2 Credit Isolation

- Each company has independent credit balance
- Credit transactions isolated per company
- Usage tracking per company
- Rate limits per company

### 9.3 Company Limits

- Maximum modules per company (optional)
- Maximum credits per purchase (optional)
- Module usage quotas (optional)

---

## 10. Localization & Translation Impact

### 10.1 Translation Keys Needed

**Module Marketplace:**
- Module descriptions
- Category names
- Pricing labels
- Subscription statuses
- Error messages

**AI Credits:**
- Credit package descriptions
- Usage labels
- Balance messages
- Error messages

**Total:** 200+ new translation keys

### 10.2 RTL Support

- Module marketplace layouts
- Credit dashboard layouts
- Subscription management pages

### 10.3 Currency Formatting

- Module pricing display
- Credit package pricing
- Usage cost display

---

## 11. Analytics, Tracking & Reporting

### 11.1 Module Analytics

**Track:**
- Module views
- Module purchases
- Subscription conversions
- Churn rate
- Revenue per module
- Popular modules
- Module usage statistics

### 11.2 AI Credits Analytics

**Track:**
- Credit purchases
- Credit usage
- Cost per API call
- Popular models
- Usage trends
- Revenue from credits
- **Usage by module** (invoices, projects, tasks, etc.) - NEW
- **Usage by content type** (text, image, video, music) - NEW
- **Module × Content Type matrix** - NEW
- **Credit exhaustion events** (when/why credits ran out) - NEW
- **Top features by module** - NEW
- **Cost per module** - NEW
- **Cost per content type** - NEW

### 11.3 Reports

**New Reports:**
1. Module subscription report
2. Module revenue report
3. Module usage report
4. AI credits purchase report
5. AI usage report (overall)
6. AI usage by module report - NEW
7. AI usage by content type report - NEW
8. AI module × content type matrix report - NEW
9. AI cost analysis report
10. AI cost by module report - NEW
11. AI cost by content type report - NEW
12. Credit exhaustion report (when/why credits ran out) - NEW
13. User profile/usage dashboard export - NEW

**Dashboard Widgets:**
- Module revenue chart
- AI credits sold chart
- Top modules by revenue
- AI usage trends
- AI usage by module (pie/bar chart) - NEW
- AI usage by content type (pie/bar chart) - NEW
- AI credits balance (current) - NEW
- AI usage this month - NEW
- Top AI features used - NEW

---

## 12. Third-Party Integrations Impact

### 12.1 Payment Gateways

**All Payment Gateways Need Updates:**
- Stripe - Module subscription support
- PayPal - Module subscription support
- Razorpay - Module subscription support
- Paystack - Module subscription support
- Mollie - Module subscription support
- Authorize.net - Module subscription support
- Payfast - Module subscription support

**Changes Required:**
- Webhook handlers for module subscriptions
- Subscription item management
- Proration handling
- Refund handling

### 12.2 AI Services

**OpenRouter API:**
- Already integrated
- Add credit deduction
- Add usage tracking
- Add rate limiting

**Future AI Services:**
- OpenAI direct integration
- Anthropic Claude integration
- Other AI providers

### 12.3 Analytics Services

- Google Analytics - Track module marketplace events
- Mixpanel - Track module subscriptions
- Custom analytics - Module usage tracking

---

## 13. Mobile & Responsive Considerations

### 13.1 Module Marketplace

- Mobile-friendly module browsing
- Touch-optimized module cards
- Mobile checkout flow
- Responsive module details page

### 13.2 AI Credits

- Mobile credit purchase
- Mobile usage viewing
- Touch-friendly balance display

### 13.3 Subscription Management

- Mobile subscription management
- Mobile renewal/cancellation
- Mobile payment updates

---

## 14. Real-World User Workflows

### 14.1 Happy Path: Company Adds Module to Package

1. Company admin has active package subscription
2. Navigates to "My Package" or "Add Modules"
3. Browses available module add-ons
4. Filters by category, searches modules
5. Clicks on module to view details
6. Reviews pricing, features, dependencies
7. Clicks "Add to Package" (monthly/annual)
8. System shows updated billing:
   - Base Package: $99/month
   - Module Add-on: $20/month
   - **New Total: $119/month**
9. Proration calculated for remaining days
10. Selects payment method
11. Confirms subscription
12. Payment processed
13. Module add-on activated
14. Unified invoice generated (Package + Add-on)
15. Confirmation email sent
16. Module appears in "My Package" → "Add-ons"
17. Company can now use the module

### 14.2 Happy Path: Company Purchases AI Credits (with Promotion)

1. Company admin needs AI credits
2. Navigates to AI Credits page
3. Views current balance (includes credits from package if any)
4. Sees available promotions: "Buy $100 get $20 credit bonus"
5. Selects credit package ($100)
6. System shows: "You'll receive 120 credits ($100 + $20 bonus)"
7. Promotion automatically applied
8. Clicks "Purchase"
9. Selects payment method
10. Confirms purchase
11. Payment processed
12. Credits added instantly (120 credits)
13. Unified invoice generated (shows promotion)
14. Confirmation email sent
15. Balance updated in UI
16. Company can now use AI features

### 14.2c Happy Path: Package Includes AI Credits

1. Company subscribes to "Pro Package" ($99/month)
2. Package includes: "1000 AI credits/month"
3. Credits added to balance on subscription
4. Credits reset monthly (or accumulate if non-recurring)
5. Company can see: "1000 credits from package + 500 purchased = 1500 total"
6. Usage tracked normally
7. Monthly credits reset on billing cycle

### 14.2a Happy Path: User Views Usage Profile

1. User navigates to "My Profile" or "Usage Dashboard"
2. Views overview: total credits used, current balance, monthly usage
3. Views module breakdown: sees "Invoice module used 450 credits (30%)"
4. Views content type breakdown: sees "Text: 350 credits, Image: 200 credits"
5. Views combined matrix: sees module × content type table
6. Filters by time period: selects "This Month"
7. Views usage trends: sees line chart of daily usage
8. Exports report: downloads CSV/Excel of usage data
9. Sets up alerts: configures low balance notifications

### 14.2b Happy Path: AI Usage with Module/Content Type Tracking

1. User uses AI feature in Invoice module (e.g., generate invoice description)
2. System identifies: module="invoices", content_type="text"
3. System calculates cost: 5 credits (text generation)
4. System checks balance: sufficient credits available
5. API call made to AI service
6. Credits deducted: 5 credits from balance
7. Usage logged: module="invoices", content_type="text", credits=5
8. User sees updated balance in header
9. Usage appears in profile dashboard under "Invoices" module
10. Usage appears in content type breakdown under "Text"

### 14.3 Error Scenarios

**Module Subscription:**
- Payment fails → Show error, retry option
- Module installation fails → Rollback, refund
- Dependency missing → Show warning, suggest required module
- Subscription expired → Show renewal prompt

**AI Credits:**
- Insufficient credits → Block API call, show purchase prompt with module/content type info
- Credits exhausted → Show exhaustion modal with:
  - What they tried to do (module, content type, credits needed)
  - Current balance (0)
  - Quick purchase options
  - Link to usage profile
  - Option to set auto-purchase
- Payment fails → Show error, retry option
- Rate limit exceeded → Show warning, wait message
- API call fails → Don't deduct credits, show error
- Module tracking fails → Log error, still allow API call (graceful degradation)

### 14.4 Edge Cases

- Company subscribes to module with dependency → Auto-subscribe to dependency
- Company cancels module with dependent modules → Warn about dependent modules
- Credits expire → Show expiration date, auto-purchase option
- Multiple modules in cart → Calculate total, apply bundle discount
- Proration mid-cycle → Calculate prorated amount
- Module tracking missing → Default to "unknown" module, log for review
- Content type detection fails → Default to "text", log for review
- Usage profile data missing → Show "No data available", guide user to use AI features
- Credit exhaustion during batch operation → Block remaining operations, show summary

---

## 15. API Impact Analysis

### 15.1 New API Endpoints

**Module Marketplace:**
- `GET /api/marketplace/modules` - List modules
- `GET /api/marketplace/modules/{id}` - Module details
- `POST /api/marketplace/modules/{id}/subscribe` - Subscribe to module
- `DELETE /api/marketplace/modules/{id}/unsubscribe` - Unsubscribe
- `GET /api/marketplace/categories` - List categories

**Module Subscriptions:**
- `GET /api/modules/subscriptions` - My subscriptions
- `GET /api/modules/subscriptions/{id}` - Subscription details
- `PUT /api/modules/subscriptions/{id}/renew` - Renew subscription
- `DELETE /api/modules/subscriptions/{id}` - Cancel subscription

**AI Credits:**
- `GET /api/ai-credits/balance` - Get balance (includes package credits + purchased)
- `POST /api/ai-credits/purchase` - Purchase credits (with promotion support)
- `GET /api/ai-credits/usage` - Usage history
- `GET /api/ai-credits/packages` - Available credit packages
- `GET /api/ai-credits/promotions` - Available promotions (NEW)
- `POST /api/ai-credits/apply-promotion` - Apply promotion to purchase (NEW)
- `GET /api/ai-credits/from-package` - Get credits included in package (NEW)
- `POST /api/ai-credits/deduct` - Deduct credits (internal)
- `GET /api/ai-credits/profile` - Get usage profile/analytics (NEW)
- `GET /api/ai-credits/usage-by-module` - Get usage breakdown by module (NEW)
- `GET /api/ai-credits/usage-by-content-type` - Get usage breakdown by content type (NEW)
- `GET /api/ai-credits/usage-matrix` - Get module × content type matrix (NEW)
- `POST /api/ai-credits/check-balance` - Check if enough credits before API call (NEW)
- `POST /api/ai-credits/exhaustion-log` - Log credit exhaustion attempt (NEW)

**Unified Billing:**
- `GET /api/billing/summary` - Get billing summary (Package + Add-ons + Credits) (NEW)
- `POST /api/billing/update` - Update package subscription with add-ons (NEW)
- `GET /api/billing/invoice-preview` - Preview invoice before payment (NEW)

### 15.2 Modified API Endpoints

- `GET /api/company` - Add module subscriptions data
- `GET /api/billing` - Add module subscription billing
- `POST /api/payment` - Support module subscription payments

### 15.3 API Versioning

- Version API endpoints for backward compatibility
- Support old package-based API during transition

---

## 16. Testing Requirements

### 16.1 Unit Tests

**Module Marketplace:**
- Module listing logic
- Subscription creation
- Dependency checking
- Pricing calculations
- Proration calculations

**AI Credits:**
- Credit deduction logic
- Balance checking
- Usage tracking (by module and content type)
- Rate limiting
- Cost calculations (by content type)
- Module identification
- Content type detection
- Credit exhaustion handling
- Usage analytics generation

### 16.2 Feature Tests

**Module Subscriptions:**
- Subscribe to module
- Unsubscribe from module
- Renew subscription
- Payment processing
- Module installation
- Dependency handling

**AI Credits:**
- Purchase credits
- Use AI feature (deduct credits with module/content type tracking)
- Low balance warning
- Credits exhausted (block action, show modal)
- Rate limiting
- Usage tracking (by module and content type)
- View usage profile/dashboard
- Export usage reports

### 16.3 Integration Tests

- End-to-end module subscription flow
- End-to-end credit purchase flow
- Payment gateway integration
- Webhook handling
- Email notifications
- Multi-tenant isolation

### 16.4 Performance Tests

- Module marketplace page load
- Subscription creation performance
- Credit deduction performance
- Usage tracking performance
- Database query optimization

---

## 17. Performance Considerations

### 17.1 Database Optimization

**Indexes Required:**
- `company_module_subscriptions.company_id`
- `company_module_subscriptions.marketplace_module_id`
- `company_module_subscriptions.status`
- `ai_credit_transactions.company_id`
- `ai_usage_logs.company_id`
- `ai_usage_logs.created_at`

**Query Optimization:**
- Eager load module subscriptions
- Cache module marketplace data
- Cache credit balances
- Optimize usage summary queries

### 17.2 Caching Strategy

**Cache:**
- Module marketplace listings (1 hour)
- Module details (1 hour)
- Credit packages (1 hour)
- Company credit balance (5 minutes)
- Module subscription status (5 minutes)

**Cache Keys:**
- `marketplace:modules:list`
- `marketplace:module:{id}`
- `ai:credits:packages`
- `company:{id}:credits:balance`
- `company:{id}:modules:subscriptions`

### 17.3 Background Jobs

**Queue Jobs:**
- Module installation (after payment)
- Subscription renewal reminders
- Credit expiration checks
- Usage summary generation
- Low balance notifications

---

## 18. Backward Compatibility & Data Migration

### 18.1 Backward Compatibility

**Keep Package System:**
- Maintain `packages` table
- Maintain `global_subscriptions` with package_id
- Support both package and module subscriptions during transition
- Feature flag to switch between systems

**Migration Period:**
- 3-6 months transition period
- Gradual migration of companies
- Support both systems simultaneously

### 18.2 Data Migration

**Package to Module Migration:**
1. Identify modules in each package
2. Create marketplace module entries
3. Create module subscriptions for companies
4. Migrate subscription data
5. Update company records

**AI Credits Initialization:**
1. Set initial balance for existing companies (optional bonus)
2. Create credit transaction records
3. Initialize usage tracking

---

## 19. Security & Authorization

### 19.1 Authorization

**Module Access:**
- Check subscription before module access
- Check permissions within module
- Grace period handling
- Dependency verification

**AI Credits:**
- Verify credit balance before API calls
- Rate limit enforcement
- Usage tracking authorization

### 19.2 Data Validation

- Module subscription data validation
- Credit purchase validation
- Payment data validation
- Usage data validation

### 19.3 Audit Logging

- Log all module subscriptions
- Log all credit transactions
- Log all AI usage
- Log payment transactions

---

## 20. Risk Assessment

### 20.1 Risk Level: 🔴 HIGH

**Risks:**
1. **Breaking Changes:** Major architectural change
2. **Data Migration:** Complex migration from packages to modules
3. **Payment Integration:** Multiple payment gateways need updates
4. **User Experience:** Users need to learn new system
5. **Revenue Impact:** Pricing changes may affect revenue
6. **Performance:** Additional database queries for subscription checks
7. **Dependencies:** Module dependencies can be complex

### 20.2 Risk Mitigation

1. **Phased Rollout:** Gradual migration, feature flags
2. **Backward Compatibility:** Keep package system during transition
3. **Testing:** Comprehensive testing before launch
4. **Monitoring:** Real-time monitoring of subscriptions and credits
5. **Rollback Plan:** Ability to revert to package system if needed
6. **Documentation:** Clear user documentation
7. **Support:** Enhanced support during transition

---

## 21. Rollout Strategy

### Phase 1: Foundation (Weeks 1-4)
- Database migrations
- Core models and services
- Basic marketplace UI
- Basic credit system

### Phase 2: Payment Integration (Weeks 5-8)
- Payment gateway integration
- Subscription management
- Invoice generation
- Webhook handling

### Phase 3: UI/UX Polish (Weeks 9-12)
- Complete marketplace UI
- Credit dashboard
- Subscription management
- Mobile optimization

### Phase 4: Testing & Refinement (Weeks 13-14)
- Comprehensive testing
- Bug fixes
- Performance optimization
- User acceptance testing

### Phase 5: Migration & Launch (Weeks 15-16)
- Data migration
- Gradual rollout
- Monitoring
- Support

---

## 22. Documentation Requirements

### 22.1 User Documentation

- Module marketplace user guide
- AI credits user guide
- Subscription management guide
- Migration guide (for existing customers)

### 22.2 Admin Documentation

- Module marketplace administration
- AI credits administration
- Subscription management
- Reporting and analytics

### 22.3 Developer Documentation

- API documentation
- Module development guide
- Integration guide
- Webhook documentation

### 22.4 Migration Documentation

- Package to module migration guide
- Data migration scripts
- Rollback procedures

---

## 23. Summary Checklist

### Database
- [ ] Create 15 new tables
- [ ] Modify 10 existing tables
- [ ] Create 25+ migrations
- [ ] Add indexes for performance
- [ ] Set up foreign keys

### Backend
- [ ] Create 20+ new models
- [ ] Create 10+ new services
- [ ] Update 25+ controllers
- [ ] Create 8+ observers/events
- [ ] Create 3+ middleware
- [ ] Update payment gateways

### Frontend
- [ ] Create 30+ Blade templates
- [ ] Create 10+ JavaScript files
- [ ] Update existing templates
- [ ] Mobile optimization
- [ ] Responsive design

### API
- [ ] Create 15+ new API endpoints
- [ ] Update existing endpoints
- [ ] API versioning
- [ ] Webhook handlers

### Testing
- [ ] Unit tests (50+)
- [ ] Feature tests (30+)
- [ ] Integration tests (20+)
- [ ] Performance tests

### Documentation
- [ ] User guides
- [ ] Admin guides
- [ ] API documentation
- [ ] Migration guides

---

## 24. Approval & Next Steps

**This is a CRITICAL architectural change requiring:**
- ✅ User approval before implementation
- ✅ Stakeholder sign-off
- ✅ Phased implementation plan
- ✅ Comprehensive testing strategy
- ✅ Rollback plan
- ✅ Migration strategy

**Recommended Next Steps:**
1. Review this impact analysis
2. Discuss modifications/concerns
3. Prioritize features for Phase 1
4. Set up development environment
5. Begin Phase 1 implementation

---

**Total Estimated Impact:**
- **20 new database tables** (includes promotions and package AI credits)
- **10 existing table modifications**
- **150+ files to create/modify**
- **35+ controllers**
- **30+ models**
- **45+ views**
- **25+ API endpoints**
- **14-18 weeks estimated effort**

---

## 28. Key Architecture Decisions

### 28.1 Hybrid Approach: Packages + Modules + Credits

**Decision:** Keep packages as primary, add modules as add-ons, integrate AI credits into billing.

**Rationale:**
- Maintains backward compatibility
- Packages remain familiar to users
- Modules provide flexibility
- Unified billing simplifies management
- Promotional offers drive credit sales

### 28.2 Unified Billing Through Packages

**Decision:** All billing (package + modules + credits) flows through package subscription system.

**Benefits:**
- Single invoice for everything
- Simplified payment processing
- Easier accounting
- Better user experience
- Centralized billing management

### 28.3 AI Credits: Package Inclusion + Separate Purchase

**Decision:** AI credits can be included in packages OR purchased separately, with promotional offers.

**Benefits:**
- Flexibility for different customer needs
- Promotions drive sales
- Package credits provide value
- Separate purchases for power users

This is a **MAJOR FEATURE** that will fundamentally change how the application handles modules and AI usage. Careful planning and phased implementation are essential.

---

## 25. Key Features Summary

### 25.1 Module-Based Credit Tracking

**Every AI usage is tracked by module:**
- When AI is used in Invoice module → Tracked as "invoices" module usage
- When AI is used in Projects module → Tracked as "projects" module usage
- When AI is used in Tasks module → Tracked as "tasks" module usage
- And so on for all modules

**Benefits:**
- Users can see which modules consume the most credits
- Cost allocation per module
- Module-specific usage analytics
- Identify expensive modules

**Example Display:**
```
Module Breakdown:
- Invoice Module: 450 credits (30% of total)
- Projects Module: 300 credits (20% of total)
- Tasks Module: 200 credits (13% of total)
- Other Modules: 550 credits (37% of total)
```

### 25.2 Content Type Breakdown

**Every AI usage is tracked by content type:**
- **Text**: AI-generated text (descriptions, translations, summaries)
- **Image**: AI-generated images (invoices, graphics, thumbnails)
- **Video**: AI-generated videos (presentations, demos)
- **Music**: AI-generated music/audio (background music, voiceovers)

**Benefits:**
- Users can see which content types consume the most credits
- Different pricing per content type (image/video more expensive)
- Content type-specific analytics
- Cost optimization opportunities

**Example Display:**
```
Content Type Breakdown:
- Text: 600 credits (40% of total)
- Image: 500 credits (33% of total)
- Video: 300 credits (20% of total)
- Music: 100 credits (7% of total)
```

### 25.3 Combined Module × Content Type Matrix

**Users can see both dimensions together:**
- Which modules use which content types
- Complete usage picture
- Identify expensive combinations

**Example Display:**
```
Module × Content Type Matrix:
┌───────────┬──────┬───────┬───────┬───────┬────────┐
│ Module    │ Text │ Image │ Video │ Music │ Total  │
├───────────┼──────┼───────┼───────┼───────┼────────┤
│ Invoices  │ 200  │ 150   │ 0     │ 0     │ 350    │
│ Projects  │ 100  │ 50    │ 30    │ 0     │ 180    │
│ Tasks     │ 50   │ 0     │ 0     │ 0     │ 50     │
│ Marketing │ 50   │ 200   │ 100   │ 50    │ 400    │
├───────────┼──────┼───────┼───────┼───────┼────────┤
│ Total     │ 400  │ 400   │ 130   │ 50    │ 980    │
└───────────┴──────┴───────┴───────┴───────┴────────┘
```

### 25.4 Credit Exhaustion Handling

**When credits run out, the system:**
1. **Immediately blocks** the AI API call
2. **Shows user-friendly modal** with:
   - What they tried to do (e.g., "Generate invoice description")
   - Module name (e.g., "Invoice Module")
   - Content type (e.g., "Text")
   - Credits needed (e.g., "5 credits")
   - Current balance (0 credits)
3. **Provides quick actions**:
   - "Purchase Credits" button (primary)
   - "View Usage Profile" link
   - "Set Auto-Purchase" option
4. **Logs the event** for analytics
5. **Sends notification** to company admin
6. **Shows recommended package** based on usage history

**Grace Period (Optional):**
- Allow 1-2 more requests with strong warning
- Or queue the request for when credits are added

### 25.5 User Profile/Usage Dashboard

**Comprehensive usage profile showing:**

1. **Overview Section:**
   - Total credits used (lifetime)
   - Total credits purchased (lifetime)
   - Current balance
   - Credits used this month
   - Average daily usage
   - Projected monthly usage

2. **Module Breakdown:**
   - Pie chart/bar chart of credits per module
   - Top modules by usage
   - Module-specific trends over time
   - Click to see detailed module usage

3. **Content Type Breakdown:**
   - Pie chart/bar chart of credits per content type
   - Content type trends over time
   - Cost per content type

4. **Combined Matrix:**
   - Table showing Module × Content Type
   - Interactive filters
   - Export functionality

5. **Time Period Filters:**
   - Today, This Week, This Month, This Year, Custom Range
   - Compare periods (this month vs last month)

6. **Usage Trends:**
   - Line charts showing usage over time
   - Daily/weekly/monthly views
   - Module-specific trends
   - Content type-specific trends

7. **Export & Reports:**
   - Export to CSV, Excel, PDF
   - Print-friendly reports
   - Scheduled reports (optional)

**Access Points:**
- "My Profile" → "Usage Dashboard"
- "Settings" → "AI Credits" → "Usage Profile"
- Direct link from credits balance in header
- Quick access from credit exhaustion modal

---

## 26. Implementation Priority

### Phase 1: Core Credit System (Weeks 1-4)
- Basic credit purchase
- Credit balance tracking
- Simple usage logging
- Basic credit exhaustion handling

### Phase 2: Module & Content Type Tracking (Weeks 5-8)
- Module identification in AI calls
- Content type detection
- Enhanced usage logging
- Module/content type breakdowns

### Phase 3: Usage Dashboard (Weeks 9-12)
- User profile/usage dashboard
- Analytics and charts
- Export functionality
- Time period filters

### Phase 4: Advanced Features (Weeks 13-16)
- Credit exhaustion advanced handling
- Auto-purchase options
- Usage predictions
- Advanced analytics

---

**This comprehensive system provides complete visibility into AI usage, enabling users to understand their consumption patterns and optimize their credit spending.**

---

## 27. Hybrid Architecture Summary

### 27.1 Packages Remain Primary

**Key Principle:** Packages are the foundation of all billing.

**What Stays:**
- All existing packages continue to work
- Package subscriptions remain the primary billing unit
- Package includes base modules (as before)
- Package can include AI credits (new feature)

**Example:**
```
Pro Package: $99/month
- Includes: Projects, Tasks, Invoices modules
- Includes: 1000 AI credits/month
```

### 27.2 Modules as Add-ons

**Key Principle:** Modules are add-ons that enhance packages.

**How It Works:**
- Company subscribes to base package
- Company can browse module marketplace
- Company can add modules as add-ons to their package
- Each add-on has its own pricing (monthly/annual)
- All billing flows through package subscription

**Example:**
```
Base Package: Pro Package ($99/month)
+ Module Add-on: Invoice AI ($20/month)
+ Module Add-on: Project Analytics ($15/month)
= Total: $134/month (unified billing)
```

### 27.3 AI Credits Integration

**Three Ways to Get AI Credits:**

1. **Included in Package:**
   - Package includes monthly/annual credits
   - Credits reset monthly (or accumulate)
   - Example: "Pro Package includes 1000 credits/month"

2. **Purchased Separately:**
   - One-time credit purchases
   - Instant credit addition
   - Can purchase anytime

3. **Promotional Offers:**
   - Buy $100 get $20 credit bonus
   - Percentage-based: Buy $100 get 20% bonus
   - Time-limited promotions
   - Package-specific promotions

**Example:**
```
Package Credits: 1000/month (from Pro Package)
+ Purchased Credits: 500 (one-time)
+ Bonus Credits: 100 (from promotion)
= Total Balance: 1600 credits
```

### 27.4 Unified Billing

**Single Invoice Shows:**
```
Invoice #12345
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ usage dashboard

