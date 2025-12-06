# B2B Pricing System - Comprehensive Impact Analysis

## Executive Summary

This document outlines all features, database changes, and business logic updates required to implement the B2B pricing system. This is a **major feature** that will affect multiple core modules.

---

## Integration Updates (Current Decisions)

- Public marketplace pages use Catalog v2 read APIs with no auth:
  - `routes/api.php:60-128` `GET /api/products/{id}/catalog`.
  - `routes/api.php:228-383` `POST /api/products/{id}/bundles/{bundleId}/price`.
- Admin/maintenance writes require `auth:sanctum` + `api.auth` and role checks:
  - `routes/api.php:227` variants generation; roles at `routes/api.php:152-155`.
- Company panel integration via module menus:
  - `resources/views/sections/menu.blade.php:237-239` includes enabled modules.
  - Visibility governed by `user_modules()` (`app/Helper/start.php:328-379`) and `module_enabled()` (`app/Helper/start.php:609-627`).
- Documents (estimates, orders, invoices) propagate bundle selections using `pricing_metadata` per line; no schema changes required for initial rollout.

## 1. Affected Features & Modules

### 1.1 Core Modules Affected

| Module | Impact Level | Changes Required |
|--------|-------------|------------------|
| **Invoices** | 🔴 Critical | Pricing calculation, display, PDF generation |
| **Orders** | 🔴 Critical | Cart pricing, order creation, pricing display |
| **Proposals** | 🔴 Critical | Auto-pricing, pricing breakdown display |
| **Estimates** | 🔴 Critical | Pricing calculation, display |
| **Credit Notes** | 🟡 Medium | Pricing reference, calculations |
| **Products** | 🔴 Critical | Price display, product catalog |
| **Deals** | 🟡 Medium | Pricing integration for proposals |
| **Recurring Invoices** | 🟡 Medium | Pricing in recurring items |
| **Cart System** | 🔴 Critical | Real-time pricing calculations |
| **Reports/Analytics** | 🟡 Medium | Revenue calculations, pricing reports |
| **REST API** | 🔴 Critical | Product, Invoice, Order, Estimate API endpoints |
| **Finance Reports** | 🟡 Medium | Income/expense reports, revenue calculations |
| **Affiliate Module** | 🟡 Medium | Commission calculations (if based on invoice totals) |

---

## 2. Database Migrations Required

### 2.1 New Tables (6 tables)

#### Migration 1: `create_pricing_tiers_table`
```php
Schema::create('pricing_tiers', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('company_id')->nullable()->index();
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('discount_type', ['percentage', 'fixed_amount', 'override_price']);
    $table->decimal('discount_value', 15, 2);
    $table->decimal('minimum_order_value', 15, 2)->nullable();
    $table->integer('minimum_quantity')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('priority')->default(0);
    $table->date('valid_from')->nullable();
    $table->date('valid_to')->nullable();
    $table->enum('applies_to', ['all', 'products', 'services', 'specific'])->default('all');
    $table->timestamps();
    
    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
});
```

#### Migration 2: `create_pricing_tier_items_table`
```php
Schema::create('pricing_tier_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('pricing_tier_id')->index();
    $table->unsignedInteger('product_id')->nullable()->index();
    $table->enum('item_type', ['product', 'service', 'category'])->default('product');
    $table->unsignedBigInteger('category_id')->nullable();
    $table->timestamps();
    
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});
```

#### Migration 3: `create_volume_discount_rules_table`
```php
Schema::create('volume_discount_rules', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('company_id')->nullable()->index();
    $table->unsignedBigInteger('pricing_tier_id')->nullable()->index();
    $table->string('name');
    $table->enum('discount_type', ['percentage', 'fixed_amount', 'tiered'])->default('percentage');
    $table->integer('minimum_quantity')->nullable();
    $table->integer('maximum_quantity')->nullable();
    $table->decimal('minimum_order_value', 15, 2)->nullable();
    $table->decimal('discount_value', 15, 2);
    $table->unsignedInteger('applies_to_product_id')->nullable();
    $table->unsignedBigInteger('applies_to_category_id')->nullable();
    $table->enum('applies_to_type', ['all', 'products', 'services', 'specific'])->default('all');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('set null');
});
```

#### Migration 4: `create_company_customer_pricing_table`
```php
Schema::create('company_customer_pricing', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('company_id')->index(); // Seller
    $table->unsignedInteger('customer_company_id')->index(); // Buyer
    $table->unsignedBigInteger('pricing_tier_id')->nullable()->index();
    $table->enum('custom_discount_type', ['percentage', 'fixed_amount'])->nullable();
    $table->decimal('custom_discount_value', 15, 2)->nullable();
    $table->boolean('is_active')->default(true);
    $table->date('valid_from')->nullable();
    $table->date('valid_to')->nullable();
    $table->timestamps();
    
    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
    $table->foreign('customer_company_id')->references('id')->on('companies')->onDelete('cascade');
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('set null');
    
    $table->unique(['company_id', 'customer_company_id'], 'unique_company_customer');
});
```

#### Migration 5: `create_company_customer_product_pricing_table`
```php
Schema::create('company_customer_product_pricing', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_customer_pricing_id')->index();
    $table->unsignedInteger('product_id')->index();
    $table->decimal('custom_price', 15, 2)->nullable();
    $table->enum('custom_discount_type', ['percentage', 'fixed_amount'])->nullable();
    $table->decimal('custom_discount_value', 15, 2)->nullable();
    $table->timestamps();
    
    $table->foreign('company_customer_pricing_id')->references('id')->on('company_customer_pricing')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    
    $table->unique(['company_customer_pricing_id', 'product_id'], 'unique_customer_product');
});
```

#### Migration 6: `create_deal_proposal_pricing_table`
```php
Schema::create('deal_proposal_pricing', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('proposal_id')->index();
    $table->unsignedBigInteger('pricing_tier_id')->nullable()->index();
    $table->enum('applied_discount_type', ['percentage', 'fixed_amount', 'override_price'])->nullable();
    $table->decimal('applied_discount_value', 15, 2)->nullable();
    $table->boolean('volume_discount_applied')->default(false);
    $table->boolean('custom_pricing_applied')->default(false);
    $table->text('pricing_breakdown')->nullable(); // JSON
    $table->timestamps();
    
    $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('set null');
});
```

### 2.2 Existing Table Modifications

#### Migration 7: `add_pricing_fields_to_orders_table`
```php
Schema::table('orders', function (Blueprint $table) {
    $table->unsignedBigInteger('pricing_tier_id')->nullable()->after('discount_type')->index();
    $table->boolean('volume_discount_applied')->default(false)->after('pricing_tier_id');
    $table->boolean('corporate_pricing_applied')->default(false)->after('volume_discount_applied');
    $table->text('pricing_breakdown')->nullable()->after('corporate_pricing_applied'); // JSON
    
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('set null');
});
```

#### Migration 8: `add_pricing_fields_to_invoices_table`
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->unsignedBigInteger('pricing_tier_id')->nullable()->after('discount_type')->index();
    $table->boolean('volume_discount_applied')->default(false)->after('pricing_tier_id');
    $table->boolean('corporate_pricing_applied')->default(false)->after('volume_discount_applied');
    $table->text('pricing_breakdown')->nullable()->after('corporate_pricing_applied'); // JSON
    
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('set null');
});
```

#### Migration 9: `add_pricing_fields_to_estimates_table`
```php
Schema::table('estimates', function (Blueprint $table) {
    $table->unsignedBigInteger('pricing_tier_id')->nullable()->after('discount_type')->index();
    $table->boolean('volume_discount_applied')->default(false)->after('pricing_tier_id');
    $table->boolean('corporate_pricing_applied')->default(false)->after('volume_discount_applied');
    
    $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onDelete('set null');
});
```

#### Migration 10: `add_pricing_fields_to_order_items_table`
```php
Schema::table('order_items', function (Blueprint $table) {
    $table->decimal('original_unit_price', 15, 2)->nullable()->after('unit_price');
    $table->decimal('pricing_discount', 15, 2)->nullable()->after('original_unit_price');
    $table->string('pricing_discount_type')->nullable()->after('pricing_discount');
    $table->text('pricing_details')->nullable()->after('pricing_discount_type'); // JSON
});
```

#### Migration 11: `add_pricing_fields_to_invoice_items_table`
```php
Schema::table('invoice_items', function (Blueprint $table) {
    $table->decimal('original_unit_price', 15, 2)->nullable()->after('unit_price');
    $table->decimal('pricing_discount', 15, 2)->nullable()->after('original_unit_price');
    $table->string('pricing_discount_type')->nullable()->after('pricing_discount');
    $table->text('pricing_details')->nullable()->after('pricing_discount_type'); // JSON
});
```

#### Migration 12: `add_pricing_fields_to_proposal_items_table`
```php
Schema::table('proposal_items', function (Blueprint $table) {
    $table->decimal('original_unit_price', 15, 2)->nullable()->after('unit_price');
    $table->decimal('pricing_discount', 15, 2)->nullable()->after('original_unit_price');
    $table->string('pricing_discount_type')->nullable()->after('pricing_discount');
    $table->text('pricing_details')->nullable()->after('pricing_discount_type'); // JSON
});
```

#### Migration 13: `add_pricing_fields_to_estimate_items_table`
```php
Schema::table('estimate_items', function (Blueprint $table) {
    $table->decimal('original_unit_price', 15, 2)->nullable()->after('unit_price');
    $table->decimal('pricing_discount', 15, 2)->nullable()->after('original_unit_price');
    $table->string('pricing_discount_type')->nullable()->after('pricing_discount');
    $table->text('pricing_details')->nullable()->after('pricing_discount_type'); // JSON
});
```

#### Migration 14: `add_pricing_fields_to_order_carts_table`
```php
Schema::table('order_carts', function (Blueprint $table) {
    $table->decimal('original_unit_price', 15, 2)->nullable()->after('unit_price');
    $table->decimal('pricing_discount', 15, 2)->nullable()->after('original_unit_price');
    $table->string('pricing_discount_type')->nullable()->after('pricing_discount');
    $table->text('pricing_details')->nullable()->after('pricing_discount_type'); // JSON
});
```

#### Migration 15: `add_company_id_to_deals_table` (if not exists)
```php
Schema::table('deals', function (Blueprint $table) {
    if (!Schema::hasColumn('deals', 'company_id')) {
        $table->unsignedInteger('company_id')->nullable()->after('client_id')->index();
        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
    }
});
```

---

## 3. Business Logic Changes Required

### 3.1 Invoice Module

#### Files to Modify:
- `app/Http/Controllers/InvoiceController.php`
- `app/Models/Invoice.php`
- `app/Models/InvoiceItems.php`
- `app/Observers/InvoiceObserver.php`
- `resources/views/invoices/ajax/create.blade.php`
- `resources/views/invoices/ajax/edit.blade.php`
- `resources/views/invoices/show.blade.php`
- `resources/views/invoice.blade.php` (public view)
- `resources/js/custom.js` (calculateTotal function)

#### Changes Required:

**1. InvoiceController::store()**
```php
// BEFORE: Direct price from product
$invoiceItem->unit_price = $product->price;

// AFTER: Use PricingService
$pricingService = app(PricingService::class);
$calculation = $pricingService->calculatePrice(
    $product->id,
    company()->id, // Seller
    $invoice->client->company_id ?? null, // Buyer
    $quantity
);
$invoiceItem->unit_price = $calculation['final_price'];
$invoiceItem->original_unit_price = $calculation['base_price'];
$invoiceItem->pricing_discount = $calculation['total_savings'];
$invoiceItem->pricing_details = json_encode($calculation);
```

**2. InvoiceController::addItem()**
```php
// Update to use PricingService when adding product to invoice
// Show pricing breakdown in response
```

**3. Invoice Display**
- Show original price vs. final price
- Display pricing breakdown (corporate discount, volume discount, etc.)
- Show "Member Pricing" badge if applicable

**4. Invoice PDF Generation**
- Include pricing breakdown in PDF
- Show savings amount

### 3.2 Order Module

#### Files to Modify:
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/ProductController.php` (cart methods)
- `app/Models/Order.php`
- `app/Models/OrderItems.php`
- `app/Models/OrderCart.php`
- `resources/views/orders/ajax/create.blade.php`
- `resources/views/orders/ajax/edit.blade.php`
- `resources/views/products/ajax/cart.blade.php`
- `resources/js/custom.js`

#### Changes Required:

**1. ProductController::addCartItem()**
```php
// BEFORE: Direct price
$product->unit_price = $productDetails->price;

// AFTER: Calculate with pricing
$pricingService = app(PricingService::class);
$calculation = $pricingService->calculatePrice(
    $productDetails->id,
    company()->id,
    user()->company_id ?? null,
    $quantity
);
$product->unit_price = $calculation['final_price'];
$product->original_unit_price = $calculation['base_price'];
$product->pricing_details = json_encode($calculation);
```

**2. OrderController::store()**
- Apply pricing when creating order from cart
- Store pricing breakdown
- Set pricing flags (volume_discount_applied, corporate_pricing_applied)

**3. Cart Display**
- Show original price (strikethrough)
- Show final price
- Display discount badges
- Real-time price updates when quantity changes

**4. Order Display**
- Show pricing breakdown
- Display applied discounts

### 3.3 Proposal Module

#### Files to Modify:
- `app/Http/Controllers/ProposalController.php`
- `app/Models/Proposal.php`
- `app/Models/ProposalItem.php`
- `resources/views/proposals/ajax/create.blade.php`
- `resources/views/proposals/ajax/edit.blade.php`
- `resources/views/proposal.blade.php` (public view)

#### Changes Required:

**1. ProposalController::store()**
- Auto-apply pricing when creating proposal from deal
- Use deal's company_id to determine buyer company
- Store pricing breakdown in deal_proposal_pricing table

**2. Proposal Display**
- Show pricing breakdown
- Display "Corporate Pricing Applied" badge
- Show savings amount

### 3.4 Estimate Module

#### Files to Modify:
- `app/Http/Controllers/EstimateController.php`
- `app/Models/Estimate.php`
- `app/Models/EstimateItem.php`
- `resources/views/estimates/ajax/create.blade.php`

#### Changes Required:
- Similar to Invoice module
- Apply pricing when adding products
- Store pricing breakdown

### 3.5 Product Module

#### Files to Modify:
- `app/Http/Controllers/ProductController.php`
- `app/Models/Product.php`
- `resources/views/products/ajax/show.blade.php`
- `app/DataTables/ProductsDataTable.php`

#### Changes Required:

**1. Product Display**
- Show different prices based on logged-in company
- Display "Member Pricing" vs "Public Pricing"
- Show applicable discounts

**2. Product List**
- Show company-specific pricing in product listings
- Display pricing tier badges

### 3.6 Deal Module

#### Files to Modify:
- `app/Models/Deal.php`
- `app/Http/Controllers/DealController.php` (if proposal creation exists)

#### Changes Required:
- Ensure deal has company_id for buyer company
- Use deal's company_id when creating proposals

### 3.7 REST API Module

#### Files to Modify:
- `Modules/RestAPI/Http/Controllers/ProductController.php`
- `Modules/RestAPI/Http/Controllers/InvoiceController.php`
- `Modules/RestAPI/Http/Controllers/EstimateController.php`
- `Modules/RestAPI/Entities/Product.php` (if exists)

#### Changes Required:

**1. ProductController API**
- Return company-specific pricing in product list/show
- Include pricing breakdown in product details
- Add pricing calculation endpoint

**2. InvoiceController API**
- Include pricing breakdown in invoice responses
- Apply pricing when creating invoices via API

**3. EstimateController API**
- Include pricing breakdown in estimate responses
- Apply pricing when creating estimates via API

### 3.8 Reports & Analytics

#### Files to Modify:
- `app/Http/Controllers/IncomeVsExpenseReportController.php`
- `app/Http/Controllers/FinanceReportController.php`
- `app/Http/Controllers/SalesReportDataTable.php`
- `app/Traits/FinanceDashboard.php`
- `app/Traits/OverviewDashboard.php`

#### Changes Required:

**1. Revenue Calculations**
- Reports currently use `invoice->total` or `payment->amount`
- These values already reflect final pricing (after discounts)
- **No changes needed** - pricing is already applied before totals are calculated
- However, may want to add reports showing:
  - Revenue by pricing tier
  - Discount amounts given
  - Corporate pricing usage

**2. Affiliate Module**
- `Modules/Affiliate/Observers/GlobalInvoiceObserver.php`
- Commission calculations use `$invoice->total`
- **No changes needed** - commission based on final invoice total (which includes pricing)

### 3.9 Recurring Invoices

#### Files to Modify:
- `app/Http/Controllers/RecurringInvoiceController.php`
- `app/Models/RecurringInvoice.php`

#### Changes Required:
- Apply pricing when generating recurring invoices
- Store pricing breakdown in recurring invoice items
- Ensure pricing is recalculated for each recurring invoice generation

---

## 4. Frontend Changes Required

### 4.1 JavaScript Functions to Update

#### `resources/js/custom.js`

**1. calculateTotal() function**
```javascript
// BEFORE: Simple calculation
var amount = (quantity * perItemCost);

// AFTER: Consider pricing discounts
// Need to call API endpoint to get pricing calculation
// Or calculate client-side if pricing details are available
```

**2. New Function: updatePricing()**
```javascript
function updatePricing(productId, quantity, companyId) {
    $.ajax({
        url: '/api/pricing/calculate',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity,
            company_id: companyId
        },
        success: function(response) {
            // Update price display
            // Show original price, final price, discounts
        }
    });
}
```

### 4.2 Blade Templates to Update

**1. Product Display Templates**
- Show pricing breakdown
- Display discount badges
- Show "Member Pricing" indicator

**2. Cart Templates**
- Show original vs. final price
- Display discount breakdown
- Real-time price updates

**3. Invoice/Order/Proposal Templates**
- Show pricing breakdown section
- Display applied discounts
- Show savings amount

**4. PDF Templates**
- Include pricing breakdown in PDFs
- Show original vs. final prices

---

## 5. API Endpoints Required

### 5.1 New API Routes

```php
// Pricing calculation endpoint
Route::post('/api/pricing/calculate', [PricingController::class, 'calculate']);

// Get pricing tiers for company
Route::get('/api/pricing/tiers', [PricingController::class, 'getTiers']);

// Get product pricing for company
Route::get('/api/pricing/product/{productId}', [PricingController::class, 'getProductPricing']);
```

### 5.2 Existing API Updates

**REST API Module Files:**
- `Modules/RestAPI/Http/Controllers/ProductController.php`
- `Modules/RestAPI/Http/Controllers/InvoiceController.php`
- `Modules/RestAPI/Http/Controllers/EstimateController.php`
- `Modules/RestAPI/Entities/Product.php` (if exists)

**Changes Required:**
- Update product API to return company-specific pricing
- Update cart API to include pricing calculations
- Update order/invoice APIs to include pricing breakdown
- Add pricing fields to API responses
- Include pricing breakdown in API responses

---

## 6. Service Classes Required

### 6.1 New Services

1. **PricingService** (Main pricing calculation service)
2. **PricingTierService** (Manage pricing tiers)
3. **VolumeDiscountService** (Handle volume discounts)
4. **CorporatePricingService** (Manage corporate pricing)

### 6.2 Existing Services to Update

- Any service that calculates totals or prices
- Report services that aggregate revenue

---

## 7. Observers & Events

### 7.1 New Observers

- `PricingTierObserver` - Handle pricing tier changes
- `CompanyCustomerPricingObserver` - Handle corporate pricing changes

### 7.2 Existing Observers to Update

- `InvoiceObserver` - May need to recalculate pricing on updates
- `OrderObserver` - May need to recalculate pricing on updates

---

## 8. Testing Requirements

### 8.1 Unit Tests

- PricingService calculation tests
- Pricing tier application tests
- Volume discount calculation tests
- Corporate pricing tests

### 8.2 Feature Tests

- Invoice creation with pricing
- Order creation with pricing
- Proposal creation with pricing
- Cart pricing calculations
- Product display with different pricing

### 8.3 Integration Tests

- End-to-end order flow with pricing
- Invoice generation with pricing
- Proposal to invoice conversion with pricing

---

## 9. Data Migration Strategy

### 9.1 Existing Data

- **Existing Invoices/Orders**: Keep as-is (historical data)
- **Existing Products**: Keep base prices
- **Existing Discounts**: May need to convert to pricing tiers

### 9.2 Migration Scripts

1. Create default pricing tiers for existing companies
2. Migrate existing discount logic to pricing tiers (if applicable)
3. Update existing orders/invoices with pricing flags (optional)

---

## 10. Performance Considerations

### 10.1 Caching

- Cache pricing calculations per company/product combination
- Cache pricing tiers
- Use Redis for frequently accessed pricing data

### 10.2 Database Indexes

- Index `company_id` in pricing tables
- Index `product_id` in pricing tables
- Index `customer_company_id` in corporate pricing tables

### 10.3 Query Optimization

- Eager load pricing relationships
- Use database views for complex pricing queries
- Optimize pricing calculation queries

---

## 11. Security Considerations

### 11.1 Authorization

- Only authorized users can create/edit pricing tiers
- Companies can only set pricing for their customers
- Validate company ownership before applying pricing

### 11.2 Data Validation

- Validate pricing rules don't conflict
- Ensure pricing values are within acceptable ranges
- Prevent negative prices

---

## 12. Rollout Strategy

### Phase 1: Database & Models (Week 1)
- Create all migrations
- Create models and relationships
- Basic service structure

### Phase 2: Core Pricing Service (Week 2)
- Implement PricingService
- Test pricing calculations
- Create API endpoints

### Phase 3: Invoice Integration (Week 3)
- Update InvoiceController
- Update invoice views
- Test invoice creation/editing

### Phase 4: Order Integration (Week 4)
- Update OrderController
- Update cart system
- Update order views

### Phase 5: Proposal Integration (Week 5)
- Update ProposalController
- Integrate with deals
- Update proposal views

### Phase 6: Frontend Updates (Week 6)
- Update JavaScript functions
- Update Blade templates
- Update PDF templates

### Phase 7: Testing & Refinement (Week 7-8)
- Comprehensive testing
- Performance optimization
- Bug fixes

---

## 13. Risk Mitigation

### 13.1 Backward Compatibility

- Keep existing discount fields functional
- Support both old and new pricing during transition
- Gradual migration of existing data

### 13.2 Data Integrity

- Validate pricing rules before saving
- Ensure pricing calculations are consistent
- Handle edge cases (expired tiers, inactive products)

### 13.3 Performance

- Monitor pricing calculation performance
- Optimize slow queries
- Implement caching where needed

---

## 14. Documentation Required

1. **API Documentation** - Pricing API endpoints
2. **User Guide** - How to use pricing tiers
3. **Admin Guide** - How to manage pricing
4. **Developer Guide** - How pricing system works
5. **Migration Guide** - How to migrate existing data

---

## 15. Summary Checklist

### Database
- [ ] Create 6 new pricing tables
- [ ] Modify 8 existing tables (add pricing fields)
- [ ] Create indexes for performance
- [ ] Add foreign key constraints

### Backend
- [ ] Create 4 new models
- [ ] Create PricingService
- [ ] Update 10+ controllers
- [ ] Update 5+ models
- [ ] Update REST API controllers (3 files)
- [ ] Create API endpoints
- [ ] Update observers
- [ ] Review report controllers (may not need changes)

### Frontend
- [ ] Update JavaScript pricing calculations
- [ ] Update 15+ Blade templates
- [ ] Update PDF templates
- [ ] Add pricing display components

### Testing
- [ ] Write unit tests
- [ ] Write feature tests
- [ ] Write integration tests
- [ ] Performance testing

### Documentation
- [ ] API documentation
- [ ] User guides
- [ ] Developer documentation

---

**Total Estimated Impact**: 
- **6 new database tables**
- **8 existing table modifications**
- **15+ files to modify**
- **10+ controllers to update**
- **5+ models to update**
- **20+ views to update**
- **4 new service classes**
- **Multiple API endpoints**

This is a **major feature** that requires careful planning and phased implementation.

