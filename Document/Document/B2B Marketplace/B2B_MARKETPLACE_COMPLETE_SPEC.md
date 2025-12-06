# B2B Marketplace - Complete Specification

## Executive Summary

This document outlines the complete specification for a B2B marketplace with:
- **Account-required purchases** (no guest checkout)
- **Multi-merchant orders** with delivery portal integration
- **Singapore delivery company API integration** (dynamic delivery settings)
- **Highly customizable pricing** (public, B2B tiers, client-specific)
- **Transaction fees + subscription fees** (platform commission)
- **Pricing comparison & savings display** (value/discount shown)

---

## Middleware & Routing

- Public marketplace reads stay under base `api` group with moderate throttling.
  - `routes/api.php:60-128` `GET /api/products/{id}/catalog` for catalog aggregation.
  - `routes/api.php:228-383` `POST /api/products/{id}/bundles/{bundleId}/price` for interactive bundle pricing.
- Protected admin writes use `auth:sanctum` + `api.auth` and role checks.
  - `routes/api.php:227` variants generation; roles checked at `routes/api.php:152-155`.
- Company panel pages use `web` + `auth` under `prefix: account`.
  - Pattern references: `Modules/RestAPI/Routes/web.php:20-30`, `Modules/Letter/Routes/web.php:15-32`.
- Full map: see `B2B_MARKETPLACE_MIDDLEWARE_MAP.md` for a consolidated overview.

## Panel Module Integration

- Sidebar injection: `resources/views/sections/menu.blade.php:237-239` auto-includes `::sections.sidebar` from enabled modules via `craveva_plugins()` (`app/Helper/start.php:382-395`).
- Visibility gating: `user_modules()` (`app/Helper/start.php:328-379`) and controller-level `module_enabled('B2BMarketplace')` (`app/Helper/start.php:609-627`).
- Operating modes:
  - Marketplace-only: client portal ordering hidden; marketplace catalog public; orders managed in Company panel.
  - Hybrid: ERP client portal remains; marketplace runs alongside with bundle-aware pricing.

## 1. User Account Requirements

### 1.1 Account Types

```
┌─────────────────────────────────────────────────────────┐
│                    Account Types                        │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. Public Customer Account                            │
│     - Individual users (not in company)                │
│     - Can browse marketplace                           │
│     - See public pricing                               │
│     - Can purchase from merchants                      │
│     - Required for all purchases                       │
│                                                          │
│  2. Company User Account (B2B)                         │
│     - Users associated with a company                  │
│     - See B2B pricing (tiers, discounts)               │
│     - Can see pricing comparison (savings)             │
│     - Can purchase from merchants                      │
│                                                          │
│  3. Merchant Company Account                           │
│     - Companies selling products/services              │
│     - Manage marketplace settings                      │
│     - Set public/B2B pricing                           │
│     - Assign pricing to specific clients               │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Account Creation Flow

**For Public Customers:**
1. Browse marketplace (no account needed)
2. Add products to cart
3. **Must create account** to checkout
4. Account creation during checkout process
5. Email verification required
6. Complete purchase

**For B2B Customers:**
1. Company admin invites users
2. Users create account via invitation
3. Account linked to company
4. Automatic access to company pricing

---

## 2. Multi-Merchant Orders & Delivery Portal

### 2.1 Order Structure

**Multi-Merchant Order:**
- Single order can contain items from multiple merchants
- Order automatically splits by merchant
- Each merchant receives their portion
- Customer sees single order with multiple shipments
- Each shipment has its own delivery tracking

### 2.2 Delivery Portal Architecture

```
┌─────────────────────────────────────────────────────────┐
│              Delivery Portal System                     │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────────┐  ┌──────────────────┐          │
│  │  SuperAdmin      │  │  Delivery         │          │
│  │  Delivery        │  │  Company APIs    │          │
│  │  Settings        │  │  (Singapore)     │          │
│  └──────────────────┘  └──────────────────┘          │
│         │                      │                        │
│         └──────────┬───────────┘                        │
│                    │                                     │
│         ┌──────────▼───────────┐                        │
│         │  Delivery Service    │                        │
│         │  (Integration Layer) │                        │
│         └──────────┬───────────┘                        │
│                    │                                     │
│         ┌──────────▼───────────┐                        │
│         │  Shipping Cost       │                        │
│         │  Calculator          │                        │
│         └──────────┬───────────┘                        │
│                    │                                     │
│         ┌──────────▼───────────┐                        │
│         │  Order Management    │                        │
│         │  (Multi-Merchant)    │                        │
│         └─────────────────────┘                        │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 2.3 Singapore Delivery Companies (Examples)

**Common Singapore Delivery APIs:**
- Ninja Van
- Lalamove
- Grab Express
- Qxpress
- SingPost
- DHL Express Singapore
- FedEx Singapore
- Others (configurable)

### 2.4 SuperAdmin Delivery Settings

**New Module: Delivery Management**

**Settings Structure:**
- **Delivery Company Management**
  - Add/Edit/Delete delivery companies
  - Configure API credentials per company
  - Set active/inactive status
  - Set priority/order (which to show first)
  
- **API Configuration (Dynamic Fields)**
  - API endpoint URL
  - API key/secret
  - Authentication method (Bearer token, API key, etc.)
  - Rate limit settings
  - Timeout settings
  - Webhook URLs (for tracking updates)
  
- **Delivery Options**
  - Standard delivery
  - Express delivery
  - Same-day delivery
  - Scheduled delivery
  - Pickup points
  
- **Pricing Rules**
  - Base shipping cost
  - Weight-based pricing
  - Distance-based pricing
  - Volume-based pricing
  - Minimum order value for free shipping
  - Delivery zones (Singapore regions)
  
- **Driver Settings** (if applicable)
  - Driver management (if using own drivers)
  - Driver assignment rules
  - Driver tracking integration
  - Driver performance metrics

### 2.5 Delivery Cost Calculation Flow

```
1. Customer adds products to cart
   ↓
2. System identifies merchants per item
   ↓
3. For each merchant:
   - Calculate package weight/dimensions
   - Get delivery address
   - Call delivery company API
   - Get shipping cost options
   ↓
4. Display shipping options to customer:
   - Standard (3-5 days) - $5
   - Express (1-2 days) - $10
   - Same-day - $20
   ↓
5. Customer selects shipping option
   ↓
6. Order created with shipping details
   ↓
7. After order confirmation:
   - Create delivery booking via API
   - Get tracking number
   - Send tracking info to customer
```

### 2.6 Database Schema for Delivery

#### Table: `delivery_companies`
```sql
delivery_companies
├── id
├── name (e.g., "Ninja Van", "Lalamove")
├── code (e.g., "ninja_van", "lalamove")
├── api_provider (enum: ninja_van, lalamove, grab_express, custom)
├── api_endpoint_url (string)
├── api_key (encrypted)
├── api_secret (encrypted)
├── authentication_type (enum: bearer_token, api_key, oauth)
├── is_active (boolean)
├── priority (integer) - Display order
├── supports_tracking (boolean)
├── supports_webhooks (boolean)
├── webhook_url (string)
├── rate_limit_per_minute (integer)
├── timeout_seconds (integer)
├── config (json) - Additional dynamic config
└── timestamps
```

#### Table: `delivery_zones`
```sql
delivery_zones
├── id
├── delivery_company_id
├── zone_name (e.g., "Central", "North", "East", "West")
├── postal_code_ranges (json) - e.g., ["01-10", "11-20"]
├── base_cost (decimal)
├── cost_per_kg (decimal)
├── cost_per_km (decimal)
├── estimated_days_min (integer)
├── estimated_days_max (integer)
└── timestamps
```

#### Table: `delivery_options`
```sql
delivery_options
├── id
├── delivery_company_id
├── option_name (e.g., "Standard", "Express", "Same-Day")
├── option_code (e.g., "standard", "express", "same_day")
├── estimated_hours (integer)
├── base_cost_multiplier (decimal) - e.g., 1.0, 1.5, 2.0
├── is_active (boolean)
└── timestamps
```

#### Table: `order_deliveries`
```sql
order_deliveries
├── id
├── order_id
├── merchant_company_id - Which merchant this delivery is for
├── delivery_company_id
├── delivery_option_id
├── tracking_number (string)
├── shipping_cost (decimal)
├── delivery_address (text)
├── delivery_contact_name (string)
├── delivery_contact_phone (string)
├── status (enum: pending, booked, picked_up, in_transit, delivered, failed)
├── estimated_delivery_date (datetime)
├── actual_delivery_date (datetime)
├── api_response (json) - Response from delivery API
├── webhook_data (json) - Updates from webhooks
└── timestamps
```

---

## 3. Highly Customizable Pricing System

### 3.1 Pricing Levels

```
┌─────────────────────────────────────────────────────────┐
│              Pricing Hierarchy                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Level 1: Base Product Price                           │
│     - Default price for product                        │
│                                                          │
│  Level 2: Public Pricing                               │
│     - Price shown to public customers                   │
│     - Can be same as base or different                 │
│     - Set per product                                  │
│                                                          │
│  Level 3: Pricing Tier (B2B)                          │
│     - Tier-based pricing (Enterprise, Premium, etc.)   │
│     - Can publish tier prices on website               │
│     - Or keep private (only for assigned customers)    │
│                                                          │
│  Level 4: Client-Specific Pricing                     │
│     - Assign specific pricing to client via            │
│       Client module                                    │
│     - Overrides tier pricing                           │
│     - Most specific pricing                            │
│                                                          │
│  Level 5: Volume Discounts                             │
│     - Applied on top of tier/client pricing            │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 3.2 Pricing Display Options

**Company can choose:**

1. **Publish Pricing Tiers on Website**
   - Show tier names and prices publicly
   - Example: "Enterprise Tier: 20% off"
   - Encourage B2B signups

2. **Keep Pricing Tiers Private**
   - Only show to assigned customers
   - Customers see pricing after login
   - More exclusive/personalized

3. **Client-Specific Assignment**
   - Assign pricing via Client module
   - Each client sees their custom pricing
   - Most flexible option

### 3.3 Pricing Comparison Display

**After Login, Show:**

```
┌─────────────────────────────────────────────────┐
│  Product: Widget X                              │
│                                                 │
│  Original Price:        $100.00                │
│  Your Price:            $75.00  [B2B Pricing]  │
│                                                 │
│  💰 You Save:           $25.00 (25%)           │
│                                                 │
│  Applied Discounts:                            │
│  • Corporate Pricing:   -$15.00 (15%)         │
│  • Volume Discount:     -$10.00 (10%)          │
│                                                 │
└─────────────────────────────────────────────────┘
```

**Features:**
- Show original price (strikethrough)
- Show customer's price (highlighted)
- Calculate total savings (value + percentage)
- Show breakdown of applied discounts
- Show which pricing tier/client pricing is applied
- Visual indicators (badges, colors)

### 3.4 Database Schema Updates

#### Table: `product_pricing_settings`
```sql
product_pricing_settings
├── id
├── product_id (unique)
├── public_price (decimal, nullable) - Override for public
├── use_base_price_for_public (boolean) - Use product.price
├── publish_tier_pricing (boolean) - Show tiers on website
├── allow_client_specific_pricing (boolean)
└── timestamps
```

#### Table: `client_product_pricing` (Client-Specific)
```sql
client_product_pricing
├── id
├── company_id (seller)
├── client_id (buyer - from users/client_details)
├── product_id
├── custom_price (decimal, nullable)
├── custom_discount_type (enum: percentage, fixed_amount)
├── custom_discount_value (decimal, nullable)
├── is_active (boolean)
├── valid_from (date, nullable)
├── valid_to (date, nullable)
└── timestamps
```

---

## 4. Platform Fees System

### 4.1 Fee Types

**1. Transaction Fees (Per Order)**
- Percentage of order value (e.g., 3%)
- Fixed fee per transaction (e.g., $2)
- Tiered fees (different % based on order value)
- Can be different for public vs B2B orders

**2. Subscription Fees (Monthly)**
- Merchant subscription fee (to list on marketplace)
- Different tiers (Basic, Premium, Enterprise)
- Features based on subscription level

### 4.2 Fee Configuration

**SuperAdmin Settings:**

```
┌─────────────────────────────────────────────────┐
│  Marketplace Fee Settings                      │
│                                                 │
│  Transaction Fees:                             │
│  ☑ Enable transaction fees                    │
│  Fee Type: [Percentage ▼] [Fixed Amount]      │
│  Fee Value: [3] %                             │
│                                                 │
│  Fee Rules:                                    │
│  • Public Orders: 3%                           │
│  • B2B Orders: 2%                             │
│  • Orders > $1000: 2%                          │
│  • Orders > $5000: 1.5%                        │
│                                                 │
│  Subscription Fees:                            │
│  ☑ Enable subscription fees                   │
│                                                 │
│  Subscription Tiers:                          │
│  • Basic: $99/month - 50 products            │
│  • Premium: $199/month - Unlimited products  │
│  • Enterprise: $399/month - + Priority       │
│                                                 │
└─────────────────────────────────────────────────┘
```

### 4.3 Fee Calculation & Collection

**Transaction Fee Flow:**
```
1. Customer places order ($1000)
   ↓
2. System calculates fee (3% = $30)
   ↓
3. Merchant receives: $970
4. Platform receives: $30
   ↓
5. Fee recorded in transaction_fees table
6. Merchant sees fee in order details
7. Platform admin sees fee in dashboard
```

**Subscription Fee Flow:**
```
1. Merchant subscribes to marketplace
   ↓
2. Monthly subscription fee charged
   ↓
3. Access to marketplace features
4. Product listing limits based on tier
   ↓
5. Auto-renewal or manual payment
```

### 4.4 Database Schema

#### Table: `marketplace_fee_settings`
```sql
marketplace_fee_settings
├── id
├── fee_type (enum: transaction, subscription)
├── fee_name (string)
├── fee_category (enum: public_order, b2b_order, subscription)
├── fee_calculation_type (enum: percentage, fixed, tiered)
├── fee_value (decimal)
├── minimum_order_value (decimal, nullable) - For tiered
├── maximum_order_value (decimal, nullable) - For tiered
├── is_active (boolean)
└── timestamps
```

#### Table: `transaction_fees`
```sql
transaction_fees
├── id
├── order_id
├── merchant_company_id
├── fee_type (enum: transaction, subscription)
├── fee_amount (decimal)
├── order_total (decimal)
├── merchant_receives (decimal)
├── platform_receives (decimal)
├── fee_percentage (decimal, nullable)
├── status (enum: pending, collected, refunded)
├── collected_at (datetime, nullable)
└── timestamps
```

#### Table: `merchant_subscriptions`
```sql
merchant_subscriptions
├── id
├── company_id (merchant)
├── subscription_tier (enum: basic, premium, enterprise)
├── monthly_fee (decimal)
├── product_limit (integer, nullable) - NULL = unlimited
├── features (json) - Available features
├── status (enum: active, cancelled, expired)
├── started_at (datetime)
├── expires_at (datetime)
├── auto_renew (boolean)
└── timestamps
```

---

## 5. Complete Database Schema

### 5.1 New Tables Summary

**Merchant & Marketplace (4 tables):**
1. `merchant_settings` - Merchant configuration
2. `product_marketplace_settings` - Product visibility
3. `product_pricing_settings` - Pricing configuration
4. `client_product_pricing` - Client-specific pricing

**Delivery System (4 tables):**
5. `delivery_companies` - Delivery company config
6. `delivery_zones` - Delivery zones/pricing
7. `delivery_options` - Delivery speed options
8. `order_deliveries` - Order delivery tracking

**Fees System (3 tables):**
9. `marketplace_fee_settings` - Fee configuration
10. `transaction_fees` - Transaction fee records
11. `merchant_subscriptions` - Merchant subscriptions

**User & Orders (2 tables):**
12. `public_customers` - Public customer details (optional)
13. Extend `orders` - Add marketplace fields

**Total: 13 new tables + modifications to existing tables**

### 5.2 Modified Tables

**orders table additions:**
```sql
ALTER TABLE orders ADD COLUMN:
├── buyer_type (enum: public_customer, individual_customer, company_customer)
├── buyer_user_id (nullable) - For individual customers
├── buyer_company_id (nullable) - For B2B customers
├── seller_company_id (index) - Merchant company
├── marketplace_order_number (string, unique)
├── is_marketplace_order (boolean)
├── platform_fee (decimal)
├── merchant_receives (decimal)
└── order_split_by_merchant (json) - Which items from which merchant
```

**users table additions:**
```sql
ALTER TABLE users ADD COLUMN:
├── user_type (enum: employee, client, public_customer)
├── is_public_customer (boolean)
└── company_id (nullable) - NULL for public customers
```

**products table additions:**
```sql
ALTER TABLE products ADD COLUMN:
├── is_marketplace_listed (boolean)
├── marketplace_visibility (enum: public, b2b_only, hidden)
└── public_price (decimal, nullable)
```

---

## 6. Implementation Phases

### Phase 1: Foundation (Weeks 1-3)
- Merchant settings module
- Account-required checkout
- Basic marketplace directory
- Public vs B2B pricing basics

### Phase 2: Pricing System (Weeks 4-6)
- Customizable pricing (public, tiers, client-specific)
- Pricing comparison display
- Savings calculation
- Client module integration

### Phase 3: Delivery Portal (Weeks 7-9)
- SuperAdmin delivery settings
- Delivery company API integration
- Shipping cost calculator
- Order delivery tracking

### Phase 4: Multi-Merchant Orders (Weeks 10-11)
- Multi-merchant cart
- Order splitting logic
- Delivery assignment per merchant
- Order management updates

### Phase 5: Fees System (Weeks 12-13)
- Transaction fee calculation
- Subscription fee system
- Fee reporting
- Payment processing

### Phase 6: Polish & Testing (Weeks 14-15)
- UI/UX improvements
- Comprehensive testing
- Performance optimization
- Documentation

---

## 7. Key Features Summary

### 7.1 Merchant Features
- ✅ Enable/disable marketplace participation
- ✅ Set public pricing per product
- ✅ Choose to publish tier pricing or keep private
- ✅ Assign client-specific pricing via Client module
- ✅ Control product visibility
- ✅ Manage order settings
- ✅ View marketplace analytics
- ✅ Manage subscription tier

### 7.2 Customer Features
- ✅ Browse marketplace (no account needed)
- ✅ Create account (required for purchase)
- ✅ See public pricing
- ✅ See B2B pricing (after login)
- ✅ See pricing comparison & savings
- ✅ Add products from multiple merchants to cart
- ✅ See shipping costs before checkout
- ✅ Track deliveries per merchant
- ✅ View order history

### 7.3 SuperAdmin Features
- ✅ Manage delivery companies (dynamic API config)
- ✅ Configure delivery zones & pricing
- ✅ Set transaction fees (percentage/fixed/tiered)
- ✅ Set subscription tiers & pricing
- ✅ View platform revenue (fees)
- ✅ Manage merchant subscriptions
- ✅ Monitor delivery integrations
- ✅ Driver settings (if applicable)

---

## 8. Technical Considerations

### 8.1 Delivery API Integration

**Integration Pattern:**
```php
interface DeliveryServiceInterface {
    public function calculateShipping($weight, $dimensions, $from, $to);
    public function createBooking($order, $deliveryOption);
    public function trackDelivery($trackingNumber);
    public function handleWebhook($data);
}

// Implementations:
- NinjaVanService implements DeliveryServiceInterface
- LalamoveService implements DeliveryServiceInterface
- GrabExpressService implements DeliveryServiceInterface
```

**Error Handling:**
- API failures → Fallback to manual delivery
- Rate limiting → Queue requests
- Timeout handling → Retry logic
- Webhook verification → Security checks

### 8.2 Pricing Calculation Updates

**Updated PricingService:**
```php
PricingService::calculatePrice(
    $productId,
    $sellerCompanyId,
    $buyerUserId, // Can be null for public
    $buyerCompanyId, // Can be null for public/individual
    $quantity
): array {
    // Returns:
    // - base_price
    // - public_price
    // - b2b_price (if applicable)
    // - client_specific_price (if applicable)
    // - final_price
    // - savings_amount
    // - savings_percentage
    // - applied_discounts[]
}
```

### 8.3 Order Splitting Logic

```php
function splitOrderByMerchant($order) {
    $merchants = [];
    
    foreach ($order->items as $item) {
        $merchantId = $item->product->company_id;
        
        if (!isset($merchants[$merchantId])) {
            $merchants[$merchantId] = [
                'company_id' => $merchantId,
                'items' => [],
                'subtotal' => 0,
            ];
        }
        
        $merchants[$merchantId]['items'][] = $item;
        $merchants[$merchantId]['subtotal'] += $item->amount;
    }
    
    // Create delivery booking for each merchant
    foreach ($merchants as $merchant) {
        createDeliveryBooking($merchant, $order);
    }
}
```

---

## 9. Security & Authorization

### 9.1 Account Security
- Email verification required
- Password strength requirements
- Two-factor authentication (optional)
- Account lockout after failed attempts

### 9.2 Payment Security
- PCI-compliant payment processing
- Encrypted payment data
- Secure API credentials storage
- Webhook signature verification

### 9.3 Data Privacy
- Customer data isolation
- Merchant data isolation
- GDPR compliance considerations
- Data retention policies

---

## 10. Next Steps

1. **Review this specification**
2. **Clarify delivery API requirements** (which companies, API docs)
3. **Design SuperAdmin delivery settings UI**
4. **Design pricing comparison UI**
5. **Plan fee calculation logic**
6. **Start Phase 1 implementation**

---

## 11. Open Questions

1. **Driver Settings**: Do we need driver management, or just delivery company API integration?
2. **Delivery Companies**: Which specific Singapore delivery companies should we integrate first?
3. **Fee Structure**: Exact fee percentages/amounts for transaction fees?
4. **Subscription Tiers**: Exact features and pricing for subscription tiers?
5. **Pricing Display**: Exact UI/UX for pricing comparison display?

---

**This is a comprehensive specification. Let's discuss any clarifications needed before implementation!**

