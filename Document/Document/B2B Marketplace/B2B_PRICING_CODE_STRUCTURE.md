# B2B Pricing System - Code Structure Examples

## Integration Notes

- Catalog v2 endpoints provide pricing inputs for UI and downstream documents.
  - Catalog read: `routes/api.php:60-128` `GET /api/products/{id}/catalog`.
  - Bundle pricing: `routes/api.php:228-383` `POST /api/products/{id}/bundles/{bundleId}/price`.
- Estimates/Orders/Invoices should carry a single `pricing_metadata` JSON per line item (bundle selections, option items, breakdowns) to avoid schema changes while enabling audit and display.
- Admin-only maintenance (variant generation and bundle option writes) must be behind `auth:sanctum` + `api.auth` with role checks (see `routes/api.php:227` and `routes/api.php:152-155`).

## Model Structure

### PricingTier Model
```php
<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends Model
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'discount_type', // 'percentage', 'fixed_amount', 'override_price'
        'discount_value',
        'minimum_order_value',
        'minimum_quantity',
        'is_active',
        'priority',
        'valid_from',
        'valid_to',
        'applies_to', // 'all', 'products', 'services', 'specific'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_order_value' => 'decimal:2',
        'minimum_quantity' => 'integer',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'pricing_tier_items')
            ->where('item_type', 'product');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PricingTierItem::class);
    }

    public function companyCustomers(): HasMany
    {
        return $this->hasMany(CompanyCustomerPricing::class);
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && $now->gt($this->valid_to)) {
            return false;
        }

        return true;
    }
}
```

### VolumeDiscountRule Model
```php
<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolumeDiscountRule extends Model
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'pricing_tier_id',
        'name',
        'discount_type', // 'percentage', 'fixed_amount', 'tiered'
        'minimum_quantity',
        'maximum_quantity',
        'minimum_order_value',
        'discount_value',
        'applies_to_product_id',
        'applies_to_category_id',
        'applies_to_type', // 'all', 'products', 'services', 'specific'
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_quantity' => 'integer',
        'maximum_quantity' => 'integer',
        'minimum_order_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'applies_to_product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'applies_to_category_id');
    }

    public function matches(int $quantity, float $orderValue, ?int $productId = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->applies_to_type === 'specific' && $this->applies_to_product_id !== $productId) {
            return false;
        }

        if ($this->minimum_quantity && $quantity < $this->minimum_quantity) {
            return false;
        }

        if ($this->maximum_quantity && $quantity > $this->maximum_quantity) {
            return false;
        }

        if ($this->minimum_order_value && $orderValue < $this->minimum_order_value) {
            return false;
        }

        return true;
    }
}
```

### CompanyCustomerPricing Model
```php
<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyCustomerPricing extends Model
{
    use HasCompany;

    protected $fillable = [
        'company_id', // Seller company
        'customer_company_id', // Buyer company
        'pricing_tier_id',
        'custom_discount_type',
        'custom_discount_value',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'custom_discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function customerCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'customer_company_id');
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function productPricing(): HasMany
    {
        return $this->hasMany(CompanyCustomerProductPricing::class);
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && $now->gt($this->valid_to)) {
            return false;
        }

        return true;
    }
}
```

## Service Class

### PricingService
```php
<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyCustomerPricing;
use App\Models\CompanyCustomerProductPricing;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\VolumeDiscountRule;

class PricingService
{
    /**
     * Calculate the final price for a product
     *
     * @param int $productId
     * @param int|null $sellerCompanyId
     * @param int|null $buyerCompanyId
     * @param int $quantity
     * @param float|null $basePrice
     * @return array
     */
    public function calculatePrice(
        int $productId,
        ?int $sellerCompanyId = null,
        ?int $buyerCompanyId = null,
        int $quantity = 1,
        ?float $basePrice = null
    ): array {
        $product = Product::findOrFail($productId);
        $basePrice = $basePrice ?? (float) $product->price;
        
        $calculation = [
            'base_price' => $basePrice,
            'applied_rules' => [],
            'discounts' => [],
            'final_price' => $basePrice,
            'total' => $basePrice * $quantity,
        ];

        $currentPrice = $basePrice;

        // Step 1: Check for company customer product pricing (most specific)
        if ($sellerCompanyId && $buyerCompanyId) {
            $productPricing = CompanyCustomerProductPricing::whereHas('companyCustomerPricing', function ($query) use ($sellerCompanyId, $buyerCompanyId) {
                $query->where('company_id', $sellerCompanyId)
                    ->where('customer_company_id', $buyerCompanyId)
                    ->where('is_active', true);
            })
            ->where('product_id', $productId)
            ->first();

            if ($productPricing && $productPricing->companyCustomerPricing->isActive()) {
                if ($productPricing->custom_price !== null) {
                    $currentPrice = (float) $productPricing->custom_price;
                    $calculation['applied_rules'][] = 'corporate_product_pricing';
                    $calculation['discounts'][] = [
                        'type' => 'custom_price',
                        'value' => $currentPrice,
                        'description' => 'Custom product price',
                    ];
                } elseif ($productPricing->custom_discount_type) {
                    $discount = $this->applyDiscount($currentPrice, $productPricing->custom_discount_type, $productPricing->custom_discount_value);
                    $currentPrice = $discount['price'];
                    $calculation['applied_rules'][] = 'corporate_product_discount';
                    $calculation['discounts'][] = $discount;
                }
            }
        }

        // Step 2: Check for company customer pricing (corporate pricing)
        if ($currentPrice === $basePrice && $sellerCompanyId && $buyerCompanyId) {
            $corporatePricing = CompanyCustomerPricing::where('company_id', $sellerCompanyId)
                ->where('customer_company_id', $buyerCompanyId)
                ->where('is_active', true)
                ->first();

            if ($corporatePricing && $corporatePricing->isActive()) {
                if ($corporatePricing->custom_discount_type) {
                    $discount = $this->applyDiscount($currentPrice, $corporatePricing->custom_discount_type, $corporatePricing->custom_discount_value);
                    $currentPrice = $discount['price'];
                    $calculation['applied_rules'][] = 'corporate_pricing';
                    $calculation['discounts'][] = $discount;
                } elseif ($corporatePricing->pricing_tier_id) {
                    $tierDiscount = $this->applyPricingTier($currentPrice, $corporatePricing->pricingTier, $productId);
                    if ($tierDiscount) {
                        $currentPrice = $tierDiscount['price'];
                        $calculation['applied_rules'][] = 'corporate_pricing_tier';
                        $calculation['discounts'][] = $tierDiscount;
                    }
                }
            }
        }

        // Step 3: Check for volume discounts
        $orderValue = $currentPrice * $quantity;
        $volumeDiscount = $this->applyVolumeDiscount($currentPrice, $quantity, $orderValue, $productId, $sellerCompanyId);
        if ($volumeDiscount) {
            $currentPrice = $volumeDiscount['price'];
            $calculation['applied_rules'][] = 'volume_discount';
            $calculation['discounts'][] = $volumeDiscount;
        }

        // Step 4: Check for pricing tier (if not already applied)
        if (!in_array('corporate_pricing_tier', $calculation['applied_rules'])) {
            $tierDiscount = $this->applyPricingTier($currentPrice, null, $productId, $buyerCompanyId, $sellerCompanyId);
            if ($tierDiscount) {
                $currentPrice = $tierDiscount['price'];
                $calculation['applied_rules'][] = 'pricing_tier';
                $calculation['discounts'][] = $tierDiscount;
            }
        }

        $calculation['final_price'] = $currentPrice;
        $calculation['total'] = $currentPrice * $quantity;
        $calculation['total_savings'] = ($basePrice * $quantity) - $calculation['total'];

        return $calculation;
    }

    /**
     * Apply a discount to a price
     */
    protected function applyDiscount(float $price, string $type, float $value): array
    {
        $originalPrice = $price;
        
        switch ($type) {
            case 'percentage':
                $discountAmount = $price * ($value / 100);
                $price = $price - $discountAmount;
                break;
            case 'fixed_amount':
                $price = max(0, $price - $value);
                $discountAmount = $originalPrice - $price;
                break;
            case 'override_price':
                $price = $value;
                $discountAmount = $originalPrice - $price;
                break;
        }

        return [
            'type' => $type,
            'value' => $value,
            'amount' => $discountAmount,
            'price' => $price,
            'description' => $this->getDiscountDescription($type, $value),
        ];
    }

    /**
     * Apply pricing tier discount
     */
    protected function applyPricingTier(
        float $price,
        ?PricingTier $tier = null,
        ?int $productId = null,
        ?int $buyerCompanyId = null,
        ?int $sellerCompanyId = null
    ): ?array {
        if (!$tier) {
            // Find applicable tier
            $tier = $this->findApplicablePricingTier($productId, $buyerCompanyId, $sellerCompanyId);
        }

        if (!$tier || !$tier->isActive()) {
            return null;
        }

        // Check if tier applies to this product
        if (!$this->tierAppliesToProduct($tier, $productId)) {
            return null;
        }

        return $this->applyDiscount($price, $tier->discount_type, $tier->discount_value);
    }

    /**
     * Apply volume discount
     */
    protected function applyVolumeDiscount(
        float $price,
        int $quantity,
        float $orderValue,
        ?int $productId = null,
        ?int $companyId = null
    ): ?array {
        $rules = VolumeDiscountRule::where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            })
            ->orderBy('discount_value', 'desc')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($quantity, $orderValue, $productId)) {
                return $this->applyDiscount($price, $rule->discount_type, $rule->discount_value);
            }
        }

        return null;
    }

    /**
     * Find applicable pricing tier
     */
    protected function findApplicablePricingTier(
        ?int $productId = null,
        ?int $buyerCompanyId = null,
        ?int $sellerCompanyId = null
    ): ?PricingTier {
        $query = PricingTier::where('is_active', true)
            ->orderBy('priority', 'desc');

        if ($buyerCompanyId) {
            $query->where(function ($q) use ($buyerCompanyId, $sellerCompanyId) {
                $q->where('company_id', $buyerCompanyId)
                    ->orWhere(function ($q2) use ($sellerCompanyId) {
                        $q2->where('company_id', $sellerCompanyId)
                            ->whereNull('company_id');
                    });
            });
        }

        return $query->first();
    }

    /**
     * Check if tier applies to product
     */
    protected function tierAppliesToProduct(PricingTier $tier, ?int $productId): bool
    {
        if ($tier->applies_to === 'all') {
            return true;
        }

        if ($tier->applies_to === 'specific' && $productId) {
            return $tier->items()
                ->where('product_id', $productId)
                ->where('item_type', 'product')
                ->exists();
        }

        // Add more logic for categories, services, etc.

        return false;
    }

    /**
     * Get discount description
     */
    protected function getDiscountDescription(string $type, float $value): string
    {
        return match ($type) {
            'percentage' => "{$value}% discount",
            'fixed_amount' => "$${value} off",
            'override_price' => "Custom price: $${value}",
            default => 'Discount applied',
        };
    }
}
```

## Controller Example

### PricingTierController
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\PricingTier\StoreRequest;
use App\Http\Requests\PricingTier\UpdateRequest;
use App\Models\PricingTier;
use App\Services\PricingService;

class PricingTierController extends Controller
{
    public function index()
    {
        $tiers = PricingTier::with(['products', 'items'])
            ->where(function ($query) {
                $query->whereNull('company_id')
                    ->orWhere('company_id', company()->id);
            })
            ->get();

        return view('pricing-tiers.index', compact('tiers'));
    }

    public function store(StoreRequest $request)
    {
        $tier = PricingTier::create($request->validated());

        if ($request->has('product_ids')) {
            $tier->products()->attach($request->product_ids);
        }

        return redirect()->route('pricing-tiers.index')
            ->with('success', 'Pricing tier created successfully');
    }

    public function calculatePrice(Request $request, PricingService $pricingService)
    {
        $calculation = $pricingService->calculatePrice(
            $request->product_id,
            $request->seller_company_id,
            $request->buyer_company_id,
            $request->quantity ?? 1
        );

        return response()->json($calculation);
    }
}
```

## Usage Example

```php
// In a Proposal Controller
use App\Services\PricingService;

public function createProposal(Request $request, PricingService $pricingService)
{
    $deal = Deal::findOrFail($request->deal_id);
    $products = $request->products; // Array of [product_id => quantity]

    $proposalItems = [];
    $total = 0;

    foreach ($products as $productId => $quantity) {
        $calculation = $pricingService->calculatePrice(
            $productId,
            company()->id, // Seller company
            $deal->company_id, // Buyer company
            $quantity
        );

        $proposalItems[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $calculation['final_price'],
            'total' => $calculation['total'],
            'pricing_breakdown' => $calculation,
        ];

        $total += $calculation['total'];
    }

    // Create proposal with calculated pricing
    // ...
}
```

---

This structure provides a solid foundation for implementing the B2B pricing system with clear separation of concerns and reusable service methods.

