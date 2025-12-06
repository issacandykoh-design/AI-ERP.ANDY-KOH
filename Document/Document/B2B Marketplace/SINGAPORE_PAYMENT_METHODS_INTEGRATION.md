# Singapore Payment Methods Integration

## Overview

Add Singapore-specific payment methods to the marketplace:
1. **PayNow** - Instant bank transfers
2. **DBS PayLah!** - DBS mobile wallet
3. **GrabPay** - Grab's payment wallet
4. **Crypto.com** - Cryptocurrency payments
5. **KPay** - Korean payment method / Alternative payment
6. **AXS** - AXS payment stations (e-Station)

---

## 1. PayNow Integration

### 1.1 What is PayNow?
- Singapore's instant payment system
- Bank-to-bank transfers using mobile number or NRIC/FIN
- Real-time settlement
- Supported by all major Singapore banks
- No fees for consumers
- QR code or mobile number-based

### 1.2 Integration Methods

**Option A: PayNow QR Code**
- Generate QR code with payment details
- Customer scans QR code with banking app
- Payment confirmed via webhook/callback

**Option B: PayNow API (Bank-Specific)**
- DBS PayNow API
- UOB PayNow API
- OCBC PayNow API
- Standard Chartered PayNow API

**Option C: Payment Gateway Integration**
- Stripe (supports PayNow)
- Razorpay (supports PayNow)
- Other gateways with PayNow support

### 1.3 Implementation

```php
class PayNowService
{
    /**
     * Generate PayNow QR code
     */
    public function generateQRCode($amount, $reference, $merchantId)
    {
        // PayNow QR code format
        $qrData = [
            'amount' => $amount,
            'reference' => $reference,
            'merchant_id' => $merchantId,
            'currency' => 'SGD',
        ];
        
        // Generate QR code
        return $this->createQRCode($qrData);
    }
    
    /**
     * Verify PayNow payment
     */
    public function verifyPayment($transactionId, $reference)
    {
        // Check payment status via API
        // Or wait for webhook notification
    }
}
```

### 1.4 Database Schema

```sql
paynow_payments
├── id
├── order_id
├── invoice_id
├── amount (decimal)
├── reference_number (string, unique)
├── qr_code_url (string)
├── mobile_number (string, nullable) - If using mobile number
├── nric_fin (string, nullable) - If using NRIC/FIN
├── status (enum: pending, completed, failed, expired)
├── transaction_id (string, nullable)
├── bank_name (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
└── timestamps
```

### 1.5 Payment Flow

```
1. Customer selects PayNow
   ↓
2. System generates PayNow QR code
   ↓
3. Customer scans QR code with banking app
   ↓
4. Customer confirms payment in banking app
   ↓
5. Payment processed instantly
   ↓
6. Webhook/callback received
   ↓
7. Order status updated to "paid"
```

---

## 2. DBS PayLah! Integration

### 2.1 What is DBS PayLah!?
- DBS Bank's mobile wallet
- Mobile number-based payments
- QR code payments
- Peer-to-peer transfers
- Merchant payments

### 2.2 Integration Methods

**Option A: DBS PayLah! Merchant API**
- Direct integration with DBS API
- Requires DBS merchant account
- QR code generation
- Payment verification

**Option B: Payment Gateway**
- Some gateways support DBS PayLah!
- Check gateway documentation

### 2.3 Implementation

```php
class DBSPayLahService
{
    /**
     * Generate DBS PayLah! QR code
     */
    public function generateQRCode($amount, $reference, $merchantId)
    {
        // DBS PayLah! API integration
        $response = $this->callDBSAPI([
            'action' => 'generate_qr',
            'amount' => $amount,
            'reference' => $reference,
            'merchant_id' => $merchantId,
        ]);
        
        return $response['qr_code_url'];
    }
    
    /**
     * Verify DBS PayLah! payment
     */
    public function verifyPayment($transactionId)
    {
        // Check payment status via DBS API
    }
}
```

### 2.4 Database Schema

```sql
dbs_paylah_payments
├── id
├── order_id
├── invoice_id
├── amount (decimal)
├── reference_number (string, unique)
├── qr_code_url (string)
├── mobile_number (string) - DBS PayLah! mobile number
├── status (enum: pending, completed, failed, expired)
├── transaction_id (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
└── timestamps
```

---

## 3. GrabPay Integration

### 3.1 What is GrabPay?
- Grab's digital wallet
- Widely used in Singapore
- QR code payments
- In-app payments
- GrabPay Credits

### 3.2 Integration Methods

**Option A: GrabPay Merchant API**
- Direct integration with GrabPay API
- Requires GrabPay merchant account
- QR code generation
- Payment verification

**Option B: GrabPay Checkout**
- Redirect to GrabPay checkout
- Return to merchant site after payment

### 3.3 Implementation

```php
class GrabPayService
{
    /**
     * Generate GrabPay payment
     */
    public function createPayment($amount, $reference, $returnUrl)
    {
        // GrabPay API integration
        $response = $this->callGrabPayAPI([
            'amount' => $amount,
            'currency' => 'SGD',
            'reference' => $reference,
            'return_url' => $returnUrl,
            'cancel_url' => $returnUrl,
        ]);
        
        return [
            'payment_url' => $response['payment_url'],
            'qr_code' => $response['qr_code'],
            'transaction_id' => $response['transaction_id'],
        ];
    }
    
    /**
     * Verify GrabPay payment
     */
    public function verifyPayment($transactionId)
    {
        // Check payment status via GrabPay API
    }
}
```

### 3.4 Database Schema

```sql
grabpay_payments
├── id
├── order_id
├── invoice_id
├── amount (decimal)
├── reference_number (string, unique)
├── payment_url (string)
├── qr_code_url (string, nullable)
├── status (enum: pending, completed, failed, cancelled)
├── transaction_id (string, nullable)
├── grabpay_user_id (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
└── timestamps
```

---

## 4. Crypto.com Integration

### 4.1 What is Crypto.com?
- Cryptocurrency payment gateway
- Accept crypto payments (Bitcoin, Ethereum, etc.)
- Convert to fiat automatically
- Crypto.com Pay integration

### 4.2 Integration Methods

**Option A: Crypto.com Pay API**
- Direct integration with Crypto.com Pay
- Accept multiple cryptocurrencies
- Automatic conversion to SGD
- Settlement in fiat currency

**Option B: Crypto.com Commerce API**
- Merchant payment solution
- QR code payments
- In-app payments

### 4.3 Supported Cryptocurrencies

- Bitcoin (BTC)
- Ethereum (ETH)
- Crypto.com Coin (CRO)
- USDC
- Other supported cryptocurrencies

### 4.4 Implementation

```php
class CryptoComService
{
    /**
     * Create Crypto.com payment
     */
    public function createPayment($amount, $reference, $currency = 'SGD')
    {
        // Crypto.com Pay API integration
        $response = $this->callCryptoComAPI([
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'return_url' => route('payment.crypto-com.return'),
            'cancel_url' => route('payment.crypto-com.cancel'),
        ]);
        
        return [
            'payment_url' => $response['payment_url'],
            'qr_code' => $response['qr_code'],
            'transaction_id' => $response['transaction_id'],
            'supported_cryptos' => $response['supported_currencies'],
        ];
    }
    
    /**
     * Verify Crypto.com payment
     */
    public function verifyPayment($transactionId)
    {
        // Check payment status via Crypto.com API
    }
    
    /**
     * Handle webhook from Crypto.com
     */
    public function handleWebhook($payload, $signature)
    {
        // Verify webhook signature
        // Process payment confirmation
    }
}
```

### 4.5 Database Schema

```sql
cryptocom_payments
├── id
├── order_id
├── invoice_id
├── amount_sgd (decimal) - Amount in SGD
├── amount_crypto (decimal) - Amount in crypto
├── crypto_currency (string) - BTC, ETH, etc.
├── exchange_rate (decimal) - Crypto to SGD rate
├── reference_number (string, unique)
├── payment_url (string)
├── qr_code_url (string, nullable)
├── wallet_address (string, nullable) - If direct wallet payment
├── status (enum: pending, completed, failed, expired, refunded)
├── transaction_id (string, nullable)
├── blockchain_tx_hash (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
└── timestamps
```

---

## 5. KPay Integration

### 5.1 What is KPay?
- Payment method (verify exact details - could be Korean payment or Singapore alternative)
- Mobile/Online payment solution
- QR code or app-based payments

### 5.2 Integration Methods

**Option A: KPay Merchant API**
- Direct integration with KPay API
- Requires KPay merchant account
- QR code generation
- Payment verification

**Option B: Payment Gateway Integration**
- Check if supported by existing gateways

### 5.3 Implementation

```php
class KPayService
{
    /**
     * Create KPay payment
     */
    public function createPayment($amount, $reference, $metadata = [])
    {
        // KPay API integration
        $response = $this->callKPayAPI([
            'amount' => $amount,
            'currency' => 'SGD',
            'reference' => $reference,
            'return_url' => route('payment.kpay.return'),
            'cancel_url' => route('payment.kpay.cancel'),
        ]);
        
        return [
            'payment_url' => $response['payment_url'],
            'qr_code' => $response['qr_code'],
            'transaction_id' => $response['transaction_id'],
        ];
    }
    
    /**
     * Verify KPay payment
     */
    public function verifyPayment($transactionId)
    {
        // Check payment status via KPay API
    }
}
```

### 5.4 Database Schema

```sql
kpay_payments
├── id
├── order_id
├── invoice_id
├── amount (decimal)
├── reference_number (string, unique)
├── payment_url (string)
├── qr_code_url (string, nullable)
├── status (enum: pending, completed, failed, expired, cancelled)
├── transaction_id (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
└── timestamps
```

---

## 6. AXS Integration

### 6.1 What is AXS?
- AXS (Automated Teller Machine/Electronic Station)
- Singapore's payment network
- AXS Stations (physical kiosks)
- AXS e-Station (online)
- Used for bill payments, top-ups, and merchant payments
- Very popular in Singapore

### 6.2 Integration Methods

**Option A: AXS e-Station API**
- Direct integration with AXS e-Station
- Requires AXS merchant account
- Online payment processing
- Payment verification

**Option B: AXS m-Station (Mobile)**
- Mobile app integration
- QR code payments
- In-app payments

### 6.3 Implementation

```php
class AXSService
{
    /**
     * Create AXS payment
     */
    public function createPayment($amount, $reference, $metadata = [])
    {
        // AXS e-Station API integration
        $response = $this->callAXSAPI([
            'amount' => $amount,
            'currency' => 'SGD',
            'reference' => $reference,
            'merchant_id' => config('payments.axs.merchant_id'),
            'return_url' => route('payment.axs.return'),
            'cancel_url' => route('payment.axs.cancel'),
        ]);
        
        return [
            'payment_url' => $response['payment_url'],
            'qr_code' => $response['qr_code'], // For AXS m-Station
            'transaction_id' => $response['transaction_id'],
            'axs_station_code' => $response['station_code'], // For physical stations
        ];
    }
    
    /**
     * Verify AXS payment
     */
    public function verifyPayment($transactionId)
    {
        // Check payment status via AXS API
    }
    
    /**
     * Get AXS Station locations (for physical payments)
     */
    public function getStationLocations($postalCode = null)
    {
        // Get nearby AXS stations
        return $this->callAXSAPI([
            'action' => 'get_stations',
            'postal_code' => $postalCode,
        ]);
    }
}
```

### 6.4 Database Schema

```sql
axs_payments
├── id
├── order_id
├── invoice_id
├── amount (decimal)
├── reference_number (string, unique)
├── payment_url (string) - e-Station URL
├── qr_code_url (string, nullable) - m-Station QR code
├── station_code (string, nullable) - If paid at physical station
├── station_location (string, nullable) - Station name/location
├── payment_method (enum: e_station, m_station, physical_station)
├── status (enum: pending, completed, failed, expired, cancelled)
├── transaction_id (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
└── timestamps
```

### 6.5 Payment Flow Options

**Option 1: AXS e-Station (Online)**
```
1. Customer selects AXS
   ↓
2. Redirected to AXS e-Station
   ↓
3. Customer logs in and pays
   ↓
4. Redirected back to merchant
   ↓
5. Payment confirmed
```

**Option 2: AXS m-Station (Mobile)**
```
1. Customer selects AXS
   ↓
2. QR code displayed
   ↓
3. Customer scans with AXS app
   ↓
4. Customer confirms in app
   ↓
5. Payment confirmed via webhook
```

**Option 3: AXS Physical Station**
```
1. Customer selects AXS
   ↓
2. Reference number generated
   ↓
3. Customer pays at AXS kiosk
   ↓
4. Payment confirmed via API
```

---

## 7. Payment Comparison Features

### 7.1 Cheapest Payment Option

**Calculate and highlight:**
- Compare all available payment methods
- Calculate fees for each method (including platform fees)
- Highlight cheapest option with badge
- Show savings amount vs other methods
- Update in real-time as order value changes

**Calculation Logic:**
```php
function calculateCheapest($amount, $availableMethods)
{
    $methodsWithFees = [];
    
    foreach ($availableMethods as $method) {
        $fee = $this->calculateFee($method, $amount);
        $methodsWithFees[] = [
            'method' => $method,
            'fee' => $fee,
            'total' => $amount + $fee,
        ];
    }
    
    $cheapest = collect($methodsWithFees)->sortBy('fee')->first();
    $cheapest->badge = 'cheapest';
    $cheapest->savings = collect($methodsWithFees)->max('fee') - $cheapest->fee;
    
    return $cheapest;
}
```

**Display:**
```
💰 Cheapest Option: PayNow
   No fees • Save $2.50 vs other methods
   Total: $150.00 (vs $152.50 with Stripe)
```

### 7.2 Fastest Payment Option

**Calculate and highlight:**
- Compare processing times for each method
- Show instant vs delayed methods
- Highlight fastest option with badge
- Show time savings vs slower methods
- Consider actual processing time, not just gateway time

**Processing Times:**
- **Instant (0 seconds):** PayNow, DBS PayLah!, GrabPay, KPay, Stripe, PayPal
- **Fast (1-2 minutes):** AXS e-Station
- **Medium (5-10 minutes):** Crypto.com (blockchain confirmation)
- **Delayed (1-3 days):** Bank transfers, some offline methods

**Display:**
```
⚡ Fastest Option: PayNow / DBS PayLah! / GrabPay / KPay
   Instant • Processed immediately
   No waiting time • Confirmed instantly
```

### 7.3 Most Safe/Secure Payment Option

**Calculate and highlight:**
- Security ratings per method (1-5 stars)
- Fraud protection levels
- Encryption standards
- Insurance/guarantees
- Regulatory compliance
- Customer protection policies
- Highlight most secure option with badge

**Security Scoring:**
```php
function calculateSecurityScore($method)
{
    $score = 0;
    
    // Bank-level security (5 points)
    if (in_array($method, ['paynow', 'dbs_paylah', 'stripe', 'paypal'])) {
        $score += 5;
    }
    
    // Fraud protection (2 points)
    if ($method->has_fraud_protection) {
        $score += 2;
    }
    
    // Insurance/guarantees (2 points)
    if ($method->has_insurance) {
        $score += 2;
    }
    
    // Regulatory compliance (1 point)
    if ($method->is_regulated) {
        $score += 1;
    }
    
    return min($score, 5); // Max 5 stars
}
```

**Security Ratings:**
- **⭐⭐⭐⭐⭐ (5 stars):** PayNow, DBS PayLah!, Stripe, PayPal
  - Bank-level security
  - Fraud protection
  - Insured transactions
  - Regulatory compliance
  
- **⭐⭐⭐⭐ (4 stars):** GrabPay, AXS, KPay
  - Strong security
  - Fraud protection
  - Some guarantees
  
- **⭐⭐⭐ (3 stars):** Crypto.com
  - Blockchain security
  - Irreversible transactions
  - Less consumer protection

**Display:**
```
🔒 Most Secure: PayNow / DBS PayLah! / Stripe
   Bank-level security • Fraud protection • Insured
   ⭐⭐⭐⭐⭐ Security Rating
```

### 7.4 Comparison Display

**Enhanced Comparison View:**

```
┌─────────────────────────────────────────────────────────┐
│  💳 Payment Method Comparison                            │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  🤖 AI Recommendation:                                  │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 💰⚡🔒 PayNow - Best Overall Choice              │   │
│  │                                                    │   │
│  │ ✅ Cheapest (No fees)                            │   │
│  │ ✅ Fastest (Instant)                              │   │
│  │ ✅ Most Secure (Bank-level)                       │   │
│  │                                                    │   │
│  │ Total: $150.00                                    │   │
│  │ Save: $2.50 vs Stripe                             │   │
│  │                                                    │   │
│  │ [Select PayNow]                                   │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ────────────────────────────────────────────────────   │
│                                                          │
│  All Payment Methods:                                   │
│                                                          │
│  Method          │ Fees │ Speed    │ Security │ Badge  │
│  ────────────────┼──────┼──────────┼──────────┼────────│
│  PayNow          │ $0   │ Instant  │ ⭐⭐⭐⭐⭐ │ 💰⚡🔒│
│  DBS PayLah!     │ $0   │ Instant  │ ⭐⭐⭐⭐⭐ │ ⚡🔒  │
│  GrabPay         │ $0   │ Instant  │ ⭐⭐⭐⭐   │ ⚡    │
│  KPay            │ $0   │ Instant  │ ⭐⭐⭐⭐   │ ⚡    │
│  Stripe          │ $0.45│ Instant  │ ⭐⭐⭐⭐⭐ │ 🔒    │
│  PayPal          │ $0.50│ Instant  │ ⭐⭐⭐⭐⭐ │ 🔒    │
│  AXS e-Station   │ $0   │ 1-2 min  │ ⭐⭐⭐⭐   │ 💰    │
│  Crypto.com      │ $0   │ 5-10 min │ ⭐⭐⭐    │       │
│                                                          │
│  💰 = Cheapest  ⚡ = Fastest  🔒 = Most Secure          │
│                                                          │
│  [Compare All Options] [Show Details]                   │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 7.5 Detailed Comparison View

**When customer clicks "Compare All Options":**

```
┌─────────────────────────────────────────────────────────┐
│  📊 Detailed Payment Comparison                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Order Amount: $150.00                                   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Method: PayNow 💰⚡🔒                            │   │
│  │ ────────────────────────────────────────────────│   │
│  │ Fee: $0.00                                       │   │
│  │ Total: $150.00                                   │   │
│  │ Speed: Instant (0 seconds)                       │   │
│  │ Security: ⭐⭐⭐⭐⭐ (Bank-level)                │   │
│  │ Features:                                        │   │
│  │ • No fees                                        │   │
│  │ • Instant processing                             │   │
│  │ • Bank-level security                            │   │
│  │ • Fraud protection                               │   │
│  │ • Insured transactions                           │   │
│  │ Savings: $2.50 vs Stripe                         │   │
│  │ [Select]                                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Method: Stripe 🔒                                │   │
│  │ ────────────────────────────────────────────────│   │
│  │ Fee: $0.45 (2.9% + $0.50)                       │   │
│  │ Total: $150.45                                   │   │
│  │ Speed: Instant (0 seconds)                       │   │
│  │ Security: ⭐⭐⭐⭐⭐ (Bank-level)                │   │
│  │ Features:                                        │   │
│  │ • Credit/Debit cards                             │   │
│  │ • Instant processing                             │   │
│  │ • Bank-level security                            │   │
│  │ • Fraud protection                               │   │
│  │ • Chargeback protection                          │   │
│  │ [Select]                                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ... (more methods)                                      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 7.6 Badge System

**Badge Types:**
- 💰 **Cheapest** - Lowest fees
- ⚡ **Fastest** - Fastest processing
- 🔒 **Most Secure** - Highest security rating
- ⭐ **Best Overall** - Best combination of all factors
- 🔥 **Popular** - Most selected by customers
- 💎 **Premium** - Premium features/benefits

**Badge Display Rules:**
- Show badges on payment method cards
- Highlight in comparison table
- Show in payment selection dropdown
- Display in order confirmation

### 7.7 Real-Time Updates

**Dynamic Comparison:**
- Update fees as order amount changes
- Recalculate cheapest/fastest/secure
- Update badges dynamically
- Show savings in real-time

**Example:**
```
Order Amount: $100
Cheapest: PayNow ($0 fee)

Order Amount: $1000
Cheapest: PayNow ($0 fee) - Save $29 vs Stripe
```

---

## 7.8 Payment Recommendation Engine

**Smart Recommendations Based On:**

1. **Order Value:**
   - Low value (< $50): Recommend cheapest (PayNow, AXS)
   - Medium value ($50-$500): Recommend balanced (PayNow, DBS PayLah!)
   - High value (> $500): Recommend secure (PayNow, Stripe)

2. **Customer History:**
   - If customer always chooses PayNow → Recommend PayNow
   - If customer prefers cards → Recommend Stripe
   - If customer is price-conscious → Recommend cheapest

3. **Order Urgency:**
   - Urgent orders → Recommend fastest (PayNow, DBS PayLah!)
   - Non-urgent → Recommend cheapest (PayNow, AXS)

4. **Order Type:**
   - High-value items → Recommend secure (PayNow, Stripe)
   - Regular items → Recommend balanced (PayNow, DBS PayLah!)

**Recommendation Display:**
```
🤖 Recommended for You: PayNow
   
   Why this is best for you:
   ✅ Cheapest option (Save $2.50)
   ✅ Instant processing
   ✅ Bank-level security
   ✅ You've used this 5 times before
   
   [Select PayNow] [See All Options]
```

---

## 8. Unified Payment Gateway Structure

### 5.1 Payment Gateway Interface

```php
interface SingaporePaymentGatewayInterface
{
    public function createPayment($amount, $reference, $metadata = []);
    public function verifyPayment($transactionId);
    public function handleWebhook($payload, $signature);
    public function getPaymentStatus($transactionId);
    public function refund($transactionId, $amount = null);
}

// Implementations
class PayNowGateway implements SingaporePaymentGatewayInterface { }
class DBSPayLahGateway implements SingaporePaymentGatewayInterface { }
class GrabPayGateway implements SingaporePaymentGatewayInterface { }
class CryptoComGateway implements SingaporePaymentGatewayInterface { }
class KPayGateway implements SingaporePaymentGatewayInterface { }
class AXSGateway implements SingaporePaymentGatewayInterface { }
```

### 5.2 Payment Service Factory

```php
class SingaporePaymentService
{
    public static function getGateway($method)
    {
        return match($method) {
            'paynow' => new PayNowGateway(),
            'dbs_paylah' => new DBSPayLahGateway(),
            'grabpay' => new GrabPayGateway(),
            'cryptocom' => new CryptoComGateway(),
            'kpay' => new KPayGateway(),
            'axs' => new AXSGateway(),
            default => throw new Exception('Unsupported payment method'),
        };
    }
    
    /**
     * Compare payment methods and get recommendations
     */
    public function comparePaymentMethods($amount, $orderId = null)
    {
        $methods = $this->getAvailableMethods($amount);
        
        // Calculate cheapest
        $cheapest = collect($methods)->sortBy('fee')->first();
        $cheapest->badges[] = 'cheapest';
        $cheapest->savings = $methods->max('fee') - $cheapest->fee;
        
        // Calculate fastest
        $fastest = collect($methods)->sortBy('processing_time')->first();
        $fastest->badges[] = 'fastest';
        
        // Calculate most secure
        $mostSecure = collect($methods)->sortByDesc('security_score')->first();
        $mostSecure->badges[] = 'most_secure';
        
        return [
            'methods' => $methods,
            'cheapest' => $cheapest,
            'fastest' => $fastest,
            'most_secure' => $mostSecure,
            'recommendations' => $this->generateRecommendations($methods, $amount),
        ];
    }
    
    /**
     * Get available payment methods with fees and details
     */
    protected function getAvailableMethods($amount)
    {
        $methods = [];
        
        // PayNow
        $methods[] = [
            'code' => 'paynow',
            'name' => 'PayNow',
            'fee' => 0,
            'processing_time' => 0, // Instant
            'security_score' => 5,
            'icon' => 'paynow',
            'description' => 'Instant bank transfer',
        ];
        
        // DBS PayLah!
        $methods[] = [
            'code' => 'dbs_paylah',
            'name' => 'DBS PayLah!',
            'fee' => 0,
            'processing_time' => 0, // Instant
            'security_score' => 5,
            'icon' => 'dbs_paylah',
            'description' => 'DBS mobile wallet',
        ];
        
        // GrabPay
        $methods[] = [
            'code' => 'grabpay',
            'name' => 'GrabPay',
            'fee' => 0,
            'processing_time' => 0, // Instant
            'security_score' => 4,
            'icon' => 'grabpay',
            'description' => 'Grab digital wallet',
        ];
        
        // Stripe
        $stripeFee = $amount * 0.029 + 0.50; // 2.9% + $0.50
        $methods[] = [
            'code' => 'stripe',
            'name' => 'Stripe',
            'fee' => round($stripeFee, 2),
            'processing_time' => 0, // Instant
            'security_score' => 5,
            'icon' => 'stripe',
            'description' => 'Credit/Debit card',
        ];
        
        // PayPal
        $paypalFee = $amount * 0.034 + 0.50; // 3.4% + $0.50
        $methods[] = [
            'code' => 'paypal',
            'name' => 'PayPal',
            'fee' => round($paypalFee, 2),
            'processing_time' => 0, // Instant
            'security_score' => 5,
            'icon' => 'paypal',
            'description' => 'PayPal account',
        ];
        
        // AXS
        $methods[] = [
            'code' => 'axs',
            'name' => 'AXS',
            'fee' => 0,
            'processing_time' => 60, // 1-2 minutes
            'security_score' => 4,
            'icon' => 'axs',
            'description' => 'AXS e-Station',
        ];
        
        // KPay
        $methods[] = [
            'code' => 'kpay',
            'name' => 'KPay',
            'fee' => 0,
            'processing_time' => 0, // Instant
            'security_score' => 4,
            'icon' => 'kpay',
            'description' => 'KPay payment',
        ];
        
        // Crypto.com
        $methods[] = [
            'code' => 'cryptocom',
            'name' => 'Crypto.com',
            'fee' => 0, // No fee, but network fees apply
            'processing_time' => 300, // 5-10 minutes
            'security_score' => 3,
            'icon' => 'cryptocom',
            'description' => 'Cryptocurrency',
        ];
        
        return collect($methods)->map(function ($method) {
            return (object) $method;
        });
    }
    
    /**
     * Generate payment recommendations
     */
    protected function generateRecommendations($methods, $amount)
    {
        $recommendations = [];
        
        // Best overall (cheapest + fast + secure)
        $bestOverall = collect($methods)
            ->map(function ($method) {
                $score = 0;
                // Lower fee = higher score
                $score += (1 - ($method->fee / 10)) * 0.3;
                // Faster = higher score
                $score += (1 - ($method->processing_time / 600)) * 0.3;
                // More secure = higher score
                $score += ($method->security_score / 5) * 0.4;
                $method->overall_score = $score;
                return $method;
            })
            ->sortByDesc('overall_score')
            ->first();
        
        $recommendations['best_overall'] = $bestOverall;
        
        return $recommendations;
    }
    
    /**
     * Process payment
     */
    public function processPayment($method, $amount, $orderId, $metadata = [])
    {
        $gateway = self::getGateway($method);
        $reference = $this->generateReference($orderId);
        
        return $gateway->createPayment($amount, $reference, $metadata);
    }
}
```

---

## 6. Database Schema Updates

### 6.1 Payment Gateway Credentials Table

**Add Singapore payment methods:**

```sql
ALTER TABLE payment_gateway_credentials ADD COLUMN:
├── paynow_status (enum: active, inactive)
├── paynow_merchant_id (string, nullable)
├── paynow_api_key (encrypted, nullable)
├── paynow_api_secret (encrypted, nullable)
├── paynow_bank_account (string, nullable) - For receiving payments
│
├── dbs_paylah_status (enum: active, inactive)
├── dbs_paylah_merchant_id (string, nullable)
├── dbs_paylah_api_key (encrypted, nullable)
├── dbs_paylah_api_secret (encrypted, nullable)
│
├── grabpay_status (enum: active, inactive)
├── grabpay_merchant_id (string, nullable)
├── grabpay_api_key (encrypted, nullable)
├── grabpay_api_secret (encrypted, nullable)
├── grabpay_environment (enum: sandbox, production)
│
├── kpay_status (enum: active, inactive)
├── kpay_merchant_id (string, nullable)
├── kpay_api_key (encrypted, nullable)
├── kpay_api_secret (encrypted, nullable)
├── kpay_environment (enum: sandbox, production)
│
├── axs_status (enum: active, inactive)
├── axs_merchant_id (string, nullable)
├── axs_api_key (encrypted, nullable)
├── axs_api_secret (encrypted, nullable)
├── axs_environment (enum: sandbox, production)
│
├── cryptocom_status (enum: active, inactive)
├── cryptocom_api_key (encrypted, nullable)
├── cryptocom_api_secret (encrypted, nullable)
├── cryptocom_merchant_id (string, nullable)
├── cryptocom_environment (enum: sandbox, production)
└── cryptocom_webhook_secret (encrypted, nullable)
```

### 6.2 Singapore Payments Table

```sql
singapore_payments
├── id
├── payment_method (enum: paynow, dbs_paylah, grabpay, cryptocom, kpay, axs)
├── order_id (nullable)
├── invoice_id (nullable)
├── amount (decimal)
├── currency (string, default: SGD)
├── reference_number (string, unique)
├── status (enum: pending, completed, failed, expired, refunded)
├── transaction_id (string, nullable)
├── payment_data (json) - Method-specific data
├── qr_code_url (string, nullable)
├── payment_url (string, nullable)
├── paid_at (datetime, nullable)
├── expires_at (datetime)
├── created_at
└── updated_at
```

---

## 7. UI/UX Implementation

### 7.1 Payment Method Selection

```
┌─────────────────────────────────────────────────────────┐
│  💳 Choose Payment Method                                │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  🇸🇬 Singapore Payment Methods                           │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 📱 PayNow                                       │   │
│  │ Instant bank transfer via mobile/QR              │   │
│  │ ✅ No fees • ⚡ Instant • 🔒 Secure              │   │
│  │ [Select]                                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 🏦 DBS PayLah!                                   │   │
│  │ DBS mobile wallet payment                        │   │
│  │ ✅ Fast • 💰 PayLah! Credits • 🔒 Secure        │   │
│  │ [Select]                                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 🚗 GrabPay                                       │   │
│  │ Grab digital wallet payment                      │   │
│  │ ✅ Popular • 💰 GrabPay Credits • 🔒 Secure     │   │
│  │ [Select]                                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ ₿ Crypto.com                                     │   │
│  │ Pay with cryptocurrency                          │   │
│  │ ✅ Bitcoin • Ethereum • USDC • 🔒 Secure       │   │
│  │ [Select]                                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 💳 KPay                                           │   │
│  │ KPay payment method                               │   │
│  │ ✅ Fast • 💰 No fees • 🔒 Secure                │   │
│  │ [Select]                                          │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 🏪 AXS                                             │   │
│  │ AXS e-Station payment                              │   │
│  │ ✅ Popular • 💰 No fees • 🔒 Secure             │   │
│  │ [Select]                                           │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ────────────────────────────────────────────────────   │
│                                                          │
│  🌍 International Payment Methods                       │
│  [Stripe] [PayPal] [Razorpay] ...                       │
│                                                          │
│  ────────────────────────────────────────────────────   │
│                                                          │
│  💡 Payment Recommendations:                            │
│  💰 Cheapest: PayNow (Save $2.50)                      │
│  ⚡ Fastest: PayNow / DBS PayLah! (Instant)            │
│  🔒 Most Secure: PayNow / Stripe (Bank-level)          │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 7.2 PayNow Payment Flow

```
┌─────────────────────────────────────────────────────────┐
│  📱 PayNow Payment                                       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Amount: SGD $150.00                                    │
│  Reference: ORD-12345                                    │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │                                                   │   │
│  │          [QR CODE IMAGE]                         │   │
│  │                                                   │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  📱 Scan with your banking app                          │
│                                                          │
│  OR                                                      │
│                                                          │
│  Enter Mobile Number:                                    │
│  [___________]                                          │
│                                                          │
│  ⏱️ Payment expires in: 15:00                           │
│                                                          │
│  💡 Payment will be processed instantly                 │
│                                                          │
│  [Cancel Payment]                                       │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 7.3 Crypto.com Payment Flow

```
┌─────────────────────────────────────────────────────────┐
│  ₿ Crypto.com Payment                                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Amount: SGD $150.00                                    │
│                                                          │
│  Select Cryptocurrency:                                 │
│  ┌──────────────────────────────────────────────────┐   │
│  │ ₿ Bitcoin (BTC)                                 │   │
│  │   0.0023 BTC ≈ SGD $150.00                      │   │
│  │   [Select]                                       │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Ξ Ethereum (ETH)                                 │   │
│  │   0.056 ETH ≈ SGD $150.00                        │   │
│  │   [Select]                                       │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ 💵 USDC                                          │   │
│  │   150 USDC ≈ SGD $150.00                         │   │
│  │   [Select]                                       │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  💡 You'll receive SGD, we handle crypto conversion     │
│                                                          │
│  [Continue to Crypto.com Pay]                           │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 8. API Integration Details

### 8.1 PayNow API Integration

**Requirements:**
- Bank merchant account (DBS, UOB, OCBC, etc.)
- API credentials from bank
- Webhook endpoint for payment notifications

**API Endpoints:**
- Generate QR code
- Verify payment status
- Receive webhook notifications

### 8.2 DBS PayLah! API Integration

**Requirements:**
- DBS merchant account
- DBS PayLah! API credentials
- Merchant ID

**API Endpoints:**
- Create payment
- Generate QR code
- Verify payment
- Webhook handling

### 8.3 GrabPay API Integration

**Requirements:**
- GrabPay merchant account
- GrabPay API credentials
- OAuth integration

**API Endpoints:**
- Create payment
- Generate QR code
- Payment status check
- Webhook handling

### 8.4 Crypto.com Pay API Integration

**Requirements:**
- Crypto.com merchant account
- API key and secret
- Webhook secret for verification

**API Endpoints:**
- Create payment
- Get supported cryptocurrencies
- Payment status check
- Webhook handling
- Refund support

---

## 9. Webhook Handling

### 9.1 Webhook Routes

```php
// routes/web.php
Route::post('/webhooks/paynow', [PayNowWebhookController::class, 'handle']);
Route::post('/webhooks/dbs-paylah', [DBSPayLahWebhookController::class, 'handle']);
Route::post('/webhooks/grabpay', [GrabPayWebhookController::class, 'handle']);
Route::post('/webhooks/cryptocom', [CryptoComWebhookController::class, 'handle']);
Route::post('/webhooks/kpay', [KPayWebhookController::class, 'handle']);
Route::post('/webhooks/axs', [AXSWebhookController::class, 'handle']);
```

### 9.2 Webhook Security

```php
class PayNowWebhookController
{
    public function handle(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-PayNow-Signature');
        $payload = $request->getContent();
        
        if (!$this->verifySignature($signature, $payload)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        // Process payment confirmation
        $this->processPayment($request->all());
        
        return response()->json(['status' => 'success']);
    }
}
```

---

## 10. Payment Status Tracking

### 10.1 Status Flow

```
Pending → Processing → Completed
   ↓         ↓            ↓
Expired   Failed      Refunded
```

### 10.2 Real-Time Updates

- Poll payment status (for methods without webhooks)
- Webhook notifications (preferred)
- Customer notification when payment confirmed
- Order status update automatically

---

## 11. Implementation Phases

### Phase 1: PayNow Integration (Week 1-2)
- Set up PayNow merchant account
- Integrate PayNow API
- Generate QR codes
- Handle webhooks
- Test payment flow

### Phase 2: DBS PayLah! Integration (Week 3)
- Set up DBS PayLah! merchant account
- Integrate DBS PayLah! API
- Test payment flow

### Phase 3: GrabPay Integration (Week 4)
- Set up GrabPay merchant account
- Integrate GrabPay API
- Test payment flow

### Phase 4: Crypto.com Integration (Week 5)
- Set up Crypto.com merchant account
- Integrate Crypto.com Pay API
- Support multiple cryptocurrencies
- Test payment flow

### Phase 5: KPay & AXS Integration (Week 6)
- Set up KPay merchant account
- Integrate KPay API
- Set up AXS merchant account
- Integrate AXS e-Station API
- Support AXS m-Station (mobile)
- Support AXS physical stations
- Test payment flows

### Phase 6: Payment Comparison Features (Week 7)
- Implement cheapest calculation
- Implement fastest calculation
- Implement most secure calculation
- Add badge system
- Add recommendation engine
- UI/UX for comparison display

### Phase 7: UI/UX & Testing (Week 8)
- Payment method selection UI
- Payment flow UI
- QR code display
- Status tracking
- Comprehensive testing

---

## 12. Merchant Account Requirements

### 12.1 PayNow
- Singapore bank account (DBS, UOB, OCBC, etc.)
- Business registration
- Bank merchant services enrollment
- API access from bank

### 12.2 DBS PayLah!
- DBS business account
- DBS PayLah! merchant enrollment
- API credentials

### 12.3 GrabPay
- GrabPay merchant account
- Business verification
- API credentials

### 12.4 Crypto.com
- Crypto.com merchant account
- Business verification
- API credentials
- KYC compliance

### 12.5 KPay
- KPay merchant account
- Business verification
- API credentials

### 12.6 AXS
- AXS merchant account
- Business registration
- AXS e-Station enrollment
- API credentials

---

## 13. Fees & Costs

### 13.1 PayNow
- **Consumer:** No fees
- **Merchant:** Bank charges may apply (check with bank)
- **Platform:** Can charge transaction fee

### 13.2 DBS PayLah!
- **Consumer:** No fees (usually)
- **Merchant:** Transaction fees apply
- **Platform:** Can charge transaction fee

### 13.3 GrabPay
- **Consumer:** No fees (usually)
- **Merchant:** Transaction fees apply
- **Platform:** Can charge transaction fee

### 13.4 Crypto.com
- **Consumer:** Network fees (crypto transaction fees)
- **Merchant:** Transaction fees apply
- **Platform:** Can charge transaction fee
- **Exchange Rate:** Crypto to SGD conversion rate

### 13.5 KPay
- **Consumer:** Usually no fees
- **Merchant:** Transaction fees may apply
- **Platform:** Can charge transaction fee

### 13.6 AXS
- **Consumer:** Usually no fees (or minimal)
- **Merchant:** Transaction fees may apply
- **Platform:** Can charge transaction fee

---

## 14. Security Considerations

### 14.1 Payment Security
- Encrypt API credentials
- Verify webhook signatures
- Use HTTPS for all payment endpoints
- Validate payment amounts
- Prevent duplicate payments

### 14.2 QR Code Security
- Time-limited QR codes
- Unique reference numbers
- Expiry timestamps
- Secure QR code generation

### 14.3 Crypto Security
- Secure wallet integration
- Exchange rate validation
- Transaction verification
- Blockchain confirmation

---

## 15. Testing

### 15.1 Sandbox/Test Mode
- All payment methods should have test/sandbox mode
- Test payment flows
- Test webhook handling
- Test error scenarios
- Test refunds (if applicable)

### 15.2 Test Scenarios
- Successful payment
- Failed payment
- Expired payment
- Duplicate payment prevention
- Webhook retry handling
- Refund processing

---

## 16. Documentation Requirements

### 16.1 API Documentation
- Payment method setup guides
- API integration guides
- Webhook documentation
- Error handling guides

### 16.2 User Documentation
- How to use PayNow
- How to use DBS PayLah!
- How to use GrabPay
- How to use Crypto.com
- FAQ section

---

## 17. Summary

### Payment Methods to Add:
1. ✅ **PayNow** - Instant bank transfers (QR/Mobile)
2. ✅ **DBS PayLah!** - DBS mobile wallet
3. ✅ **GrabPay** - Grab digital wallet
4. ✅ **Crypto.com** - Cryptocurrency payments
5. ✅ **KPay** - KPay payment method
6. ✅ **AXS** - AXS e-Station / m-Station / Physical stations

### Key Features:
- QR code generation
- Real-time payment verification
- Webhook handling
- Payment status tracking
- Refund support (where applicable)
- Multi-currency support (Crypto.com)
- **Payment comparison** (cheapest, fastest, most secure)
- **Smart recommendations** based on order value and customer preferences
- **Badge system** to highlight best options

### Implementation:
- Unified payment gateway interface
- Singapore-specific payment tables
- UI/UX for payment selection
- Integration with existing payment system
- Marketplace payment splitting support

---

## 18. Next Steps

1. **Apply for merchant accounts:**
   - PayNow (via bank)
   - DBS PayLah!
   - GrabPay
   - Crypto.com

2. **Get API credentials:**
   - API keys
   - API secrets
   - Merchant IDs
   - Webhook secrets

3. **Set up development environment:**
   - Sandbox/test accounts
   - Test API credentials
   - Webhook endpoints

4. **Start implementation:**
   - Begin with PayNow (most popular)
   - Then add other methods
   - Test thoroughly
   - Deploy to production

---

**These Singapore-specific payment methods will make the marketplace more accessible to Singapore customers and increase conversion rates!**

