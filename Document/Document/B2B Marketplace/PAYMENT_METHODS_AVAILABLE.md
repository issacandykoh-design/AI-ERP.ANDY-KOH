# Available Payment Methods - Current System

## Overview

The system currently supports **9 online payment gateways** plus **custom offline payment methods**. All payment methods are configurable per company and can be enabled/disabled individually.

---

## 1. Online Payment Gateways

### 1.1 Stripe
**Status:** ✅ Available  
**Features:**
- Credit/Debit card processing
- Supports multiple payment methods:
  - Card (Visa, Mastercard, Amex, etc.)
  - Alipay
  - BECS Direct Debit (Australia)
  - Bancontact
  - EPS
  - Giropay
  - iDEAL
  - SEPA Debit
- Test & Live modes
- Webhook support for payment updates
- Subscription support (via Laravel Cashier)

**Configuration:**
- `stripe_client_id` (Publishable key)
- `stripe_secret` (Secret key - encrypted)
- `stripe_webhook_secret` (Webhook secret)
- `stripe_mode` (test/live)
- `stripe_status` (active/inactive)

**Use Cases:**
- International payments
- Card payments
- Recurring subscriptions
- Most versatile gateway

---

### 1.2 PayPal
**Status:** ✅ Available  
**Features:**
- PayPal account payments
- Credit card payments (without PayPal account)
- Test & Live modes
- Sandbox support
- IPN (Instant Payment Notification) support

**Configuration:**
- `paypal_client_id`
- `paypal_secret` (encrypted)
- `sandbox_paypal_client_id`
- `sandbox_paypal_secret` (encrypted)
- `paypal_mode` (sandbox/live)
- `paypal_status` (active/inactive)

**Use Cases:**
- PayPal users
- International payments
- Consumer-friendly payments

---

### 1.3 Razorpay
**Status:** ✅ Available  
**Features:**
- Indian payment gateway
- Supports cards, UPI, netbanking, wallets
- Test & Live modes
- Webhook support

**Configuration:**
- `razorpay_key`
- `razorpay_secret` (encrypted)
- `live_razorpay_key`
- `live_razorpay_secret` (encrypted)
- `test_razorpay_key`
- `test_razorpay_secret` (encrypted)
- `razorpay_mode` (test/live)
- `razorpay_status` (active/inactive)

**Use Cases:**
- Indian market
- UPI payments
- Netbanking

---

### 1.4 Paystack
**Status:** ✅ Available  
**Features:**
- African payment gateway
- Supports cards, bank transfers
- Test & Live modes
- Webhook support

**Configuration:**
- `paystack_key`
- `paystack_secret` (encrypted)
- `paystack_merchant_email`
- `paystack_payment_url`
- `test_paystack_key`
- `test_paystack_secret` (encrypted)
- `paystack_mode` (test/live)
- `paystack_status` (active/inactive)

**Use Cases:**
- African market
- Nigerian payments
- Bank transfers

---

### 1.5 Mollie
**Status:** ✅ Available  
**Features:**
- European payment gateway
- Supports multiple European payment methods
- Test & Live modes

**Configuration:**
- `mollie_api_key` (encrypted)
- `mollie_status` (active/inactive)

**Use Cases:**
- European market
- SEPA payments
- European-specific payment methods

---

### 1.6 Payfast
**Status:** ✅ Available  
**Features:**
- South African payment gateway
- Supports cards, EFT, instant EFT
- Test & Live modes
- Webhook support

**Configuration:**
- `payfast_merchant_id`
- `payfast_merchant_key` (encrypted)
- `payfast_passphrase` (encrypted)
- `test_payfast_merchant_id`
- `test_payfast_merchant_key` (encrypted)
- `test_payfast_passphrase` (encrypted)
- `payfast_mode` (test/live)
- `payfast_status` (active/inactive)

**Use Cases:**
- South African market
- EFT payments
- Instant EFT

---

### 1.7 Authorize.net
**Status:** ✅ Available  
**Features:**
- Payment processing gateway
- Credit card processing
- Test & Live environments

**Configuration:**
- `authorize_api_login_id`
- `authorize_transaction_key` (encrypted)
- `authorize_environment` (sandbox/production)
- `authorize_status` (active/inactive)

**Use Cases:**
- US market
- Credit card processing
- Merchant accounts

---

### 1.8 Square
**Status:** ✅ Available  
**Features:**
- Payment processing
- Point of sale integration
- Test & Live environments

**Configuration:**
- `square_application_id`
- `square_access_token` (encrypted)
- `square_location_id`
- `square_environment` (sandbox/production)
- `square_status` (active/inactive)

**Use Cases:**
- Point of sale
- In-person payments
- US market

---

### 1.9 Flutterwave
**Status:** ✅ Available  
**Features:**
- Multi-currency payments
- African payment gateway
- Supports cards, bank transfers, mobile money
- Test & Live modes
- Webhook support

**Configuration:**
- `flutterwave_key`
- `flutterwave_secret` (encrypted)
- `flutterwave_hash` (encrypted)
- `test_flutterwave_key`
- `test_flutterwave_secret` (encrypted)
- `test_flutterwave_hash` (encrypted)
- `live_flutterwave_key`
- `live_flutterwave_secret` (encrypted)
- `live_flutterwave_hash` (encrypted)
- `flutterwave_webhook_secret_hash`
- `flutterwave_mode` (test/live)
- `flutterwave_status` (active/inactive)

**Use Cases:**
- African market
- Multi-currency
- Mobile money
- Bank transfers

---

## 2. Offline Payment Methods

### 2.1 Custom Offline Methods
**Status:** ✅ Available  
**Features:**
- Companies can create custom offline payment methods
- Each method has:
  - Name (e.g., "Bank Transfer", "Cash on Delivery", "Cheque")
  - Description (instructions for customers)
  - Image/Icon
  - Status (active/inactive)

**Configuration:**
- Managed via `OfflinePaymentMethod` model
- Company-specific (each company can have their own)
- Can have multiple offline methods

**Common Examples:**
- Bank Transfer
- Cash on Delivery (COD)
- Cheque
- Wire Transfer
- Direct Deposit
- Manual Payment

**Use Cases:**
- B2B transactions
- Large orders
- Trusted customers
- Local payment methods
- Cash on delivery

---

## 3. Payment Configuration

### 3.1 Company-Level Configuration
Each company can configure:
- Which payment gateways to enable
- API credentials for each gateway
- Test vs Live mode
- Offline payment methods

**Location:** Settings → Payment Gateway Credentials

### 3.2 Global Configuration (SuperAdmin)
SuperAdmin can configure:
- Global payment gateway settings
- Default payment methods
- Platform-level payment processing

---

## 4. Payment Flow

### 4.1 For Invoices
1. Customer receives invoice
2. Clicks "Pay Now"
3. Selects payment method
4. Redirected to payment gateway (if online)
5. Completes payment
6. Payment recorded in system
7. Invoice status updated

### 4.2 For Orders
1. Customer adds products to cart
2. Proceeds to checkout
3. Selects payment method
4. Completes payment
5. Order status updated
6. Invoice generated (if applicable)

---

## 5. Payment Status

**Payment Statuses:**
- `pending` - Payment initiated but not completed
- `complete` - Payment successfully completed
- `failed` - Payment failed
- `refunded` - Payment refunded

---

## 6. Marketplace Integration Considerations

### 6.1 Current Payment Flow
- Payments are company-scoped
- Each company configures their own payment methods
- Customers pay directly to merchant company

### 6.2 Marketplace Payment Requirements

**For B2B Marketplace, we need:**

1. **Platform Fee Collection**
   - Need to collect platform fees (transaction fees)
   - Options:
     - **Option A:** Split payment (customer pays merchant + platform separately)
     - **Option B:** Collect full amount, then distribute (merchant receives net)
     - **Option C:** Two-step payment (order payment + platform fee)

2. **Multi-Merchant Payments**
   - Single order with multiple merchants
   - Each merchant needs to receive their portion
   - Platform fee needs to be collected

3. **Payment Gateway Selection**
   - Merchant can choose which payment methods to accept
   - Platform can require certain payment methods
   - Customer sees available payment methods per merchant

4. **Payment Splitting**
   - For multi-merchant orders:
     - Option 1: Customer pays full amount, platform splits
     - Option 2: Customer pays each merchant separately
     - Option 3: Platform collects, then distributes

---

## 7. Recommended Payment Flow for Marketplace

### 7.1 Single Payment (Recommended)
```
Customer Order: $1000
├── Merchant A items: $600
├── Merchant B items: $300
├── Platform fee (3%): $30
└── Shipping: $70

Customer pays: $1000 (single payment)
├── Platform receives: $30 (fee)
├── Merchant A receives: $600 - $18 (fee) = $582
└── Merchant B receives: $300 - $9 (fee) = $291
```

**Implementation:**
- Customer pays full amount to platform
- Platform holds funds
- Platform distributes to merchants (minus fees)
- Platform keeps transaction fees

### 7.2 Payment Gateway Requirements
- Need to support payment splitting
- Or use escrow/holding account
- Or use marketplace payment solution (Stripe Connect, PayPal Marketplace)

---

## 8. Payment Gateway Recommendations for Marketplace

### 8.1 Stripe Connect (Recommended)
**Why:**
- Built for marketplaces
- Supports payment splitting
- Handles platform fees automatically
- Multi-party payments
- Escrow support

**Features:**
- Connect accounts for merchants
- Split payments automatically
- Platform fee collection
- Payouts to merchants

### 8.2 PayPal Marketplace
**Why:**
- Marketplace solution
- Multi-party payments
- Fee collection

**Features:**
- Merchant accounts
- Payment splitting
- Platform fees

### 8.3 Custom Solution
**Why:**
- More control
- Use existing gateways
- Custom fee logic

**Implementation:**
- Collect full payment
- Hold in escrow account
- Distribute to merchants
- Keep platform fees

---

## 9. Database Schema for Marketplace Payments

### 9.1 Payment Splitting Table
```sql
payment_splits
├── id
├── payment_id (main payment)
├── merchant_company_id
├── amount (merchant receives)
├── platform_fee (fee amount)
├── status (pending, paid, failed)
├── paid_at (datetime)
└── timestamps
```

### 9.2 Payment Updates Needed
```sql
-- Add to payments table:
├── is_marketplace_payment (boolean)
├── platform_fee_amount (decimal)
├── split_payment (boolean)
```

---

## 10. Implementation Recommendations

### 10.1 Phase 1: Use Existing Payment Methods
- Keep current payment gateways
- Add payment splitting logic
- Manual fee collection (or automated via API)

### 10.2 Phase 2: Integrate Marketplace Payment Solution
- Integrate Stripe Connect or PayPal Marketplace
- Automatic payment splitting
- Automatic fee collection
- Automatic payouts to merchants

### 10.3 Phase 3: Add Escrow/Holding Account
- Hold payments until order fulfillment
- Release to merchants after delivery confirmation
- Better buyer protection

---

## 11. Summary

### Current Payment Methods Available:
1. ✅ Stripe (9 payment types)
2. ✅ PayPal
3. ✅ Razorpay (India)
4. ✅ Paystack (Africa)
5. ✅ Mollie (Europe)
6. ✅ Payfast (South Africa)
7. ✅ Authorize.net
8. ✅ Square
9. ✅ Flutterwave (Africa, multi-currency)
10. ✅ Offline Payment Methods (customizable)

### For Marketplace:
- **Recommendation:** Integrate Stripe Connect or PayPal Marketplace
- **Alternative:** Use existing gateways with custom payment splitting
- **Consider:** Escrow/holding account for buyer protection

---

## 12. Next Steps

1. **Decide on payment splitting method:**
   - Stripe Connect (recommended)
   - PayPal Marketplace
   - Custom solution with existing gateways

2. **Design payment flow:**
   - How platform fees are collected
   - How merchants receive payments
   - Payment timing (immediate vs after delivery)

3. **Implement payment splitting:**
   - Database schema
   - Payment processing logic
   - Merchant payout system

4. **Add payment method selection:**
   - Merchant chooses which methods to accept
   - Customer sees available methods
   - Platform can require certain methods

---

**All current payment methods can be used in the marketplace, but we need to add payment splitting and fee collection logic!**

