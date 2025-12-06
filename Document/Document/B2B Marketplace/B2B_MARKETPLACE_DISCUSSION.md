# B2B Marketplace with Public Access - Discussion Document

## Overview

This document discusses expanding the B2B pricing system into a **full B2B marketplace** where:
1. **Companies can list themselves as merchants** (opt-in marketplace participation)
2. **Public users can browse and purchase** from merchant companies
3. **B2B customers** (companies) get special pricing (tiers, volume discounts, corporate pricing)
4. **Public customers** (individuals) see standard/public pricing
5. **Merchant settings** allow companies to control their marketplace presence

---

## Latest Decisions & Integration Points

- Catalog v2 is API-driven for marketplace pages.
  - Read: `routes/api.php:60-128` `GET /api/products/{id}/catalog` aggregates product, bundles, options, variants, attributes.
  - Pricing: `routes/api.php:228-383` `POST /api/products/{id}/bundles/{bundleId}/price` returns validated `final_price` plus breakdowns.
  - Admin-only writes: `routes/api.php:227` `POST /products/{id}/variants/generate` with `auth:sanctum` + `api.auth` and role gates at `routes/api.php:152-155`.
- Company panel module integration follows existing menu system.
  - Sidebar injection: `resources/views/sections/menu.blade.php:237-239` includes `::sections.sidebar` for enabled modules from `craveva_plugins()`.
  - Role/module gating: `app/Helper/start.php:328-379` `user_modules()` decides visibility; controllers may also guard via `module_enabled('B2BMarketplace')` at `app/Helper/start.php:609-627`.
- Operating modes (visibility control):
  - Marketplace-only: hide client-facing ordering UIs; company manages orders and finance; marketplace catalog is public via API.
  - Hybrid: selected ERP customers retain portal access; marketplace operates alongside.
- Orders/Invoices alignment:
  - Current order-to-invoice conversion uses simple itemization; to preserve clarity, propagate a single `pricing_metadata` JSON per line for bundle selections without schema changes.
  - Estimates call the pricing endpoint and carry the same metadata downstream.

## 1. Key Concepts & Architecture

### 1.1 User Types

```
┌─────────────────────────────────────────────────────────┐
│                    User Types                           │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. Public/Guest Users                                  │
│     - Not logged in                                     │
│     - Can browse marketplace                            │
│     - Can purchase at public prices                     │
│     - May need to create account for checkout           │
│                                                          │
│  2. Individual Customers                                │
│     - Logged in as individual user                      │
│     - Not associated with a company                     │
│     - See public pricing                                │
│     - Can purchase from any merchant                    │
│                                                          │
│  3. Company Users (B2B Customers)                      │
│     - Logged in as company user                         │
│     - Associated with a company                         │
│     - See B2B pricing (tiers, discounts)                │
│     - Can set up corporate pricing                      │
│                                                          │
│  4. Merchant Companies                                  │
│     - Companies that sell products/services             │
│     - Can control marketplace visibility                │
│     - Set public vs B2B pricing                          │
│     - Manage their product catalog                      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Marketplace Structure

```
┌─────────────────────────────────────────────────────────┐
│              B2B Marketplace Platform                    │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────────┐  ┌──────────────────┐          │
│  │  Marketplace     │  │  Merchant         │          │
│  │  Directory       │  │  Storefronts      │          │
│  │  (Public View)   │  │  (Per Company)    │          │
│  └──────────────────┘  └──────────────────┘          │
│         │                      │                        │
│         └──────────┬───────────┘                        │
│                    │                                     │
│         ┌──────────▼───────────┐                        │
│         │  Product Catalog     │                        │
│         │  (Multi-Merchant)    │                        │
│         └──────────┬───────────┘                        │
│                    │                                     │
│         ┌──────────▼───────────┐                        │
│         │  Shopping Cart      │                        │
│         │  (Multi-Merchant)   │                        │
│         └──────────┬───────────┘                        │
│                    │                                     │
│         ┌──────────▼───────────┐                        │
│         │  Checkout & Orders  │                        │
│         └─────────────────────┘                        │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Merchant Settings - Key Features

### 2.1 Marketplace Participation Settings

**Company can control:**
- ✅ **Enable/Disable Marketplace Listing** - Opt-in to be listed as merchant
- ✅ **Public Visibility** - Show/hide company in marketplace directory
- ✅ **Storefront URL** - Custom URL slug (e.g., `/marketplace/acme-corp`)
- ✅ **Company Profile** - Public-facing company information
- ✅ **Company Logo/Banner** - For marketplace display
- ✅ **Company Description** - What they sell, specialties
- ✅ **Contact Information** - Public contact details
- ✅ **Business Hours** - When they're available
- ✅ **Location/Address** - Physical location (if applicable)
- ✅ **Categories** - What product/service categories they're in
- ✅ **Verification Badge** - Verified merchant status

### 2.2 Product Visibility Settings

**Per Product, Company can control:**
- ✅ **List in Marketplace** - Show product in public marketplace
- ✅ **Public Price** - Price shown to public/individual customers
- ✅ **B2B Price** - Price shown to company customers (uses pricing tiers)
- ✅ **Product Visibility** - Public, B2B only, or Hidden
- ✅ **Minimum Order Quantity** - For B2B customers
- ✅ **Stock Availability** - Show stock status publicly

### 2.3 Pricing Strategy Settings

**Company can set:**
- ✅ **Public Pricing** - Standard prices for everyone
- ✅ **B2B Pricing** - Use pricing tiers, volume discounts
- ✅ **Show "Member Pricing"** - Display B2B prices to logged-in companies
- ✅ **Hide Public Pricing** - Only show prices to logged-in B2B customers
- ✅ **Price Display Rules** - When to show which prices

### 2.4 Order Management Settings

**Company can control:**
- ✅ **Accept Public Orders** - Allow public/individual customers to order
- ✅ **Accept B2B Orders** - Allow company customers to order
- ✅ **Minimum Order Value** - For public vs B2B
- ✅ **Payment Methods** - Which payment methods to accept
- ✅ **Shipping Options** - Shipping methods available
- ✅ **Order Approval** - Auto-approve or manual approval
- ✅ **Order Notifications** - Email notifications for new orders

---

## 3. Database Schema Requirements

### 3.1 New Tables Needed

#### Table 1: `merchant_settings`
```sql
merchant_settings
├── id
├── company_id (unique)
├── is_marketplace_enabled (boolean) - Opt-in to marketplace
├── is_publicly_listed (boolean) - Show in marketplace directory
├── storefront_slug (string, unique) - URL slug
├── company_profile (text) - Public description
├── company_banner_image (string) - Banner image
├── public_contact_email (string)
├── public_contact_phone (string)
├── business_hours (json) - Opening hours
├── location_address (text)
├── location_latitude (decimal)
├── location_longitude (decimal)
├── is_verified (boolean) - Verified merchant badge
├── verification_date (datetime)
├── categories (json) - Product/service categories
├── accept_public_orders (boolean)
├── accept_b2b_orders (boolean)
├── minimum_order_value_public (decimal)
├── minimum_order_value_b2b (decimal)
├── auto_approve_orders (boolean)
├── show_b2b_pricing_to_public (boolean) - Show "Member Pricing" badge
├── hide_public_pricing (boolean) - Hide prices from public
└── timestamps
```

#### Table 2: `product_marketplace_settings`
```sql
product_marketplace_settings
├── id
├── product_id (unique)
├── is_listed_in_marketplace (boolean)
├── visibility_type (enum: public, b2b_only, hidden)
├── public_price (decimal) - Override product price for public
├── show_public_price (boolean)
├── show_b2b_price (boolean)
├── minimum_order_quantity_b2b (integer)
├── stock_visible_public (boolean)
└── timestamps
```

#### Table 3: `marketplace_orders` (or extend existing orders)
```sql
-- Add to existing orders table:
├── buyer_type (enum: public, individual, company)
├── buyer_user_id (nullable) - For individual customers
├── buyer_company_id (nullable) - For B2B customers
├── seller_company_id (index) - Merchant company
├── marketplace_order_number (string, unique)
├── is_marketplace_order (boolean)
└── marketplace_fee (decimal) - Platform fee (if applicable)
```

#### Table 4: `public_users` (or extend users table)
```sql
-- Option 1: Extend users table
├── user_type (enum: employee, client, public_customer)
├── is_public_customer (boolean)
├── company_id (nullable) - NULL for public customers

-- Option 2: Separate table (if needed)
public_customers
├── id
├── user_id (foreign key to users)
├── shipping_address (text)
├── billing_address (text)
├── phone_number (string)
└── timestamps
```

---

## 4. Pricing Logic Updates

### 4.1 Pricing Resolution Order (Updated)

```
1. Check if user is logged in
   ↓
2. If Public/Guest:
   → Use Public Price (from product_marketplace_settings or product.price)
   → No discounts (or simple public discounts)
   
3. If Individual Customer (logged in, no company):
   → Use Public Price
   → May apply individual customer discounts
   
4. If Company User (B2B):
   → Check Company Customer Product Pricing (most specific)
   → Check Company Customer Pricing (corporate pricing)
   → Check Volume Discount Rules
   → Check Pricing Tier
   → Use B2B Price
```

### 4.2 Price Display Logic

```php
function getDisplayPrice($product, $user = null) {
    // Public/Guest users
    if (!$user) {
        return $product->public_price ?? $product->price;
    }
    
    // Individual customers (not in company)
    if ($user && !$user->company_id) {
        return $product->public_price ?? $product->price;
    }
    
    // B2B customers (in company)
    if ($user && $user->company_id) {
        // Use PricingService to calculate B2B price
        return PricingService::calculatePrice(...);
    }
}
```

---

## 5. User Interface Requirements

### 5.1 Public Marketplace Pages

**New Routes Needed:**
- `/marketplace` - Marketplace directory/homepage
- `/marketplace/merchants` - List all merchants
- `/marketplace/merchant/{slug}` - Merchant storefront
- `/marketplace/products` - All products from all merchants
- `/marketplace/product/{id}` - Product detail page
- `/marketplace/cart` - Shopping cart
- `/marketplace/checkout` - Checkout process

**Features:**
- Browse merchants by category
- Search products across all merchants
- Filter by merchant, category, price range
- View merchant profiles
- Add products to cart from multiple merchants
- Checkout as guest or logged-in user

### 5.2 Merchant Settings Pages

**New Routes Needed:**
- `/settings/marketplace` - Marketplace settings
- `/settings/marketplace/profile` - Company profile
- `/settings/marketplace/products` - Product visibility settings
- `/settings/marketplace/pricing` - Pricing strategy
- `/settings/marketplace/orders` - Order management settings

**Features:**
- Enable/disable marketplace participation
- Edit company profile for marketplace
- Control product visibility
- Set public vs B2B pricing
- Configure order settings
- View marketplace analytics

### 5.3 Product Management Updates

**Updates Needed:**
- Add "Marketplace Settings" tab to product edit page
- Toggle "List in Marketplace"
- Set public price
- Set visibility (public, B2B only, hidden)
- Preview how product appears in marketplace

---

## 6. Business Logic Changes

### 6.1 Product Display Logic

**Current:** Products shown only within company context
**New:** Products shown in marketplace context

**Changes:**
- Products need to be queryable across companies
- Filter by merchant company
- Show merchant information with product
- Handle multi-merchant cart

### 6.2 Order Creation Logic

**Current:** Orders created within company context
**New:** Orders can be created by public users or B2B customers

**Changes:**
- Identify buyer type (public, individual, company)
- Identify seller company (merchant)
- Handle multi-merchant orders (split by merchant)
- Apply appropriate pricing based on buyer type
- Handle guest checkout

### 6.3 Cart System Updates

**Current:** Cart is company-scoped
**New:** Cart can contain products from multiple merchants

**Changes:**
- Cart needs to track merchant per item
- Group cart items by merchant
- Calculate shipping per merchant
- Handle checkout for multiple merchants (split orders or single order)

### 6.4 Pricing Service Updates

**Update PricingService to handle:**
- Public pricing (no discounts)
- Individual customer pricing (may have discounts)
- B2B pricing (full pricing tier system)

---

## 7. Key Questions to Discuss

### 7.1 User Management

**Q1: How do we handle public users?**
- Option A: Extend existing `users` table with `user_type` field
- Option B: Create separate `public_customers` table
- Option C: Allow guest checkout without account creation

**Recommendation:** Option A + Option C (allow guest checkout, create account optional)

**Q2: Do public users need to create accounts?**
- For browsing: No account needed
- For purchasing: Optional (guest checkout) or required?
- For order tracking: Account recommended

**Recommendation:** Guest checkout allowed, but encourage account creation

### 7.2 Order Management

**Q3: How do we handle multi-merchant orders?**
- Option A: Single order with items from multiple merchants (merchant gets notification)
- Option B: Split into separate orders per merchant automatically
- Option C: Allow buyer to choose (single or split)

**Recommendation:** Option B (split automatically) - cleaner for merchants

**Q4: Who manages order fulfillment?**
- Each merchant manages their own orders
- Platform manages order aggregation
- Merchant sees only their portion of multi-merchant orders

**Recommendation:** Each merchant manages their own orders independently

### 7.3 Pricing Strategy

**Q5: Can merchants set different public prices per product?**
- Yes, override product price for public
- Or use same price for public and B2B (just no discounts for public)

**Recommendation:** Yes, allow public price override per product

**Q6: Should public users see B2B prices?**
- Option A: Show "Member Pricing" badge with B2B price
- Option B: Hide B2B prices completely
- Option C: Configurable per merchant

**Recommendation:** Option C (configurable) - merchant decides

### 7.4 Marketplace Features

**Q7: Do we need marketplace fees/commissions?**
- Platform takes % of each sale?
- Fixed fee per transaction?
- No fees (free marketplace)?

**Recommendation:** Discuss with business - can be added later

**Q8: Do we need merchant verification?**
- Verified merchant badge?
- Verification process?
- Trust indicators?

**Recommendation:** Yes, add verification system for trust

**Q9: Do we need reviews/ratings?**
- Product reviews?
- Merchant reviews?
- Rating system?

**Recommendation:** Phase 2 feature - can add later

### 7.5 Search & Discovery

**Q10: How do users find merchants/products?**
- Search by merchant name
- Search by product name
- Filter by category
- Filter by location
- Sort by price, rating, etc.

**Recommendation:** All of the above - comprehensive search

---

## 8. Implementation Phases

### Phase 1: Merchant Settings & Basic Marketplace (Weeks 1-3)
- Create merchant_settings table
- Create merchant settings UI
- Basic marketplace directory
- Merchant storefront pages
- Product marketplace visibility controls

### Phase 2: Public Access & Pricing (Weeks 4-6)
- Public user handling
- Public pricing logic
- Product display for public users
- Public shopping cart
- Guest checkout

### Phase 3: Multi-Merchant Orders (Weeks 7-8)
- Multi-merchant cart
- Order splitting logic
- Merchant order management
- Order notifications

### Phase 4: Enhanced Features (Weeks 9-10)
- Search & filtering
- Merchant verification
- Analytics dashboard
- Reviews/ratings (if needed)

---

## 9. Impact on Existing B2B Pricing System

### 9.1 What Stays the Same
- ✅ Pricing tiers system
- ✅ Volume discounts
- ✅ Corporate pricing
- ✅ B2B pricing calculations
- ✅ Deal proposal integration

### 9.2 What Needs Updates
- 🔄 PricingService - Add public pricing logic
- 🔄 Product display - Show different prices based on user type
- 🔄 Cart system - Handle multi-merchant carts
- 🔄 Order system - Handle public orders
- 🔄 User model - Add user_type field

### 9.3 New Components Needed
- 🆕 Merchant settings management
- 🆕 Marketplace directory/listing
- 🆕 Public product catalog
- 🆕 Public shopping cart
- 🆕 Guest checkout flow
- 🆕 Merchant storefront pages

---

## 10. Database Migration Summary

### New Tables (4 tables)
1. `merchant_settings` - Merchant configuration
2. `product_marketplace_settings` - Product visibility/pricing
3. `public_customers` (optional) - Public user details
4. `marketplace_orders` (or extend orders) - Marketplace order tracking

### Modified Tables (5 tables)
1. `companies` - Add marketplace-related fields
2. `products` - Add marketplace visibility fields
3. `orders` - Add buyer_type, seller_company_id, etc.
4. `users` - Add user_type field
5. `order_items` - Track merchant per item

---

## 11. Security & Authorization

### 11.1 Public Access
- Public pages don't require authentication
- Product browsing is public
- Cart can be session-based (no login required)
- Checkout may require account (or guest checkout)

### 11.2 Merchant Access
- Only company admins can manage merchant settings
- Merchants can only edit their own settings
- Product visibility controls are company-scoped

### 11.3 Order Access
- Public users see only their orders
- Merchants see only orders for their products
- B2B customers see their company orders

---

## 12. Recommendations

### 12.1 Start Simple
1. Begin with merchant settings (opt-in marketplace)
2. Add basic marketplace directory
3. Enable public product browsing
4. Add public pricing (simple, no discounts)
5. Add guest checkout

### 12.2 Then Enhance
1. Add B2B pricing for logged-in companies
2. Add multi-merchant cart
3. Add merchant verification
4. Add search & filtering
5. Add reviews/ratings (if needed)

### 12.3 Keep B2B Features
- All existing B2B pricing features remain
- Companies can still do B2B transactions
- Marketplace is additive, not replacement

---

## 13. Next Steps

1. **Review this document** - Discuss and clarify requirements
2. **Answer key questions** - Make decisions on Q1-Q10
3. **Prioritize features** - Decide what's Phase 1 vs Phase 2
4. **Create detailed spec** - Based on decisions
5. **Start implementation** - Begin with merchant settings

---

## 14. Open Questions for Discussion

1. **Guest Checkout**: Required or optional account creation?
2. **Multi-Merchant Orders**: Single order or split automatically?
3. **Marketplace Fees**: Will platform take commission?
4. **Public Pricing**: Same as B2B base price or separate pricing?
5. **Merchant Verification**: Required or optional?
6. **Reviews/Ratings**: Include in Phase 1 or Phase 2?
7. **Search**: Basic search or advanced filtering in Phase 1?
8. **Shipping**: How to handle shipping for multi-merchant orders?

---

**Please review and let's discuss these points before implementation!**

