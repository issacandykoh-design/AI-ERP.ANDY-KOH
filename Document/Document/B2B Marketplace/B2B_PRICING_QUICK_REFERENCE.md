# B2B Pricing System - Quick Reference Guide

## Key Concepts

### 1. Pricing Tiers
**What**: Named pricing levels (e.g., "Enterprise", "Premium", "Standard") with discount rules.

**Example**:
- Tier: "Enterprise"
- Discount: 20% off all products
- Minimum Order: $1,000
- Applies to: All products except clearance items

### 2. Volume Discounts
**What**: Automatic discounts based on quantity or order value.

**Example**:
- Buy 10-49 units: 5% discount
- Buy 50-99 units: 10% discount
- Buy 100+ units: 15% discount

### 3. Corporate Pricing
**What**: Custom pricing that Company A sets for Company B (their customer).

**Example**:
- Company A sets 25% discount for Company B
- Company A sets custom price $50 for Product X for Company B (instead of $100)

### 4. Product Selection
**What**: Choose which products/services a pricing tier applies to.

**Options**:
- All products/services
- Specific products only
- Product categories
- Exclude specific items

---

## User Scenarios

### Scenario 1: Platform Admin Creates Pricing Tier
1. Admin creates "Enterprise Tier" with 20% discount
2. Sets minimum order value of $5,000
3. Applies to all products
4. Companies can be assigned to this tier

### Scenario 2: Company Sets Corporate Pricing
1. Company A logs in
2. Goes to "Customer Pricing" section
3. Selects Company B (their customer)
4. Sets 25% discount for Company B
5. Sets custom price $50 for Product X for Company B
6. When Company B orders, they see these prices

### Scenario 3: Volume Discount Applied
1. Company B adds 25 units of Product X to cart
2. System checks: 25 units qualifies for 10% volume discount
3. Base price: $100
4. Volume discount: 10% → $90
5. Corporate discount (if applicable): 25% → $67.50
6. Final price per unit: $67.50

### Scenario 4: Deal Proposal with Pricing
1. Sales rep creates deal for Company B
2. Adds products to proposal
3. System auto-applies:
   - Corporate pricing (25% discount)
   - Volume discount (10% for 25 units)
4. Proposal shows:
   - Original price: $100
   - Corporate discount: -$25
   - Volume discount: -$7.50
   - Final price: $67.50
   - Total savings: $32.50 per unit

---

## Pricing Calculation Flow

```
Product Price: $100
Quantity: 25 units

Step 1: Check Corporate Product Pricing
  → None found

Step 2: Check Corporate Pricing (Company-level)
  → Found: 25% discount for Company B
  → Price: $100 × 0.75 = $75

Step 3: Check Volume Discount
  → Found: 10% discount for 25 units
  → Price: $75 × 0.90 = $67.50

Step 4: Check Pricing Tier
  → Already applied via corporate pricing

Final Price per Unit: $67.50
Total for 25 units: $1,687.50
```

---

## Database Tables Summary

| Table | Purpose |
|-------|---------|
| `pricing_tiers` | Store pricing tier definitions |
| `pricing_tier_items` | Link products/services to tiers |
| `volume_discount_rules` | Store volume discount rules |
| `company_customer_pricing` | Store corporate pricing relationships |
| `company_customer_product_pricing` | Store custom product prices per customer |
| `deal_proposal_pricing` | Track pricing applied to proposals |

---

## Key Features Checklist

- [x] Create pricing tiers with discounts
- [x] Select products/services for tiers
- [x] Volume discounts (quantity-based)
- [x] Volume discounts (value-based)
- [x] Corporate pricing (company-to-company)
- [x] Custom product prices per customer
- [x] Auto-apply pricing in proposals
- [x] Show different pricing for logged-in companies
- [x] Pricing validity periods
- [x] Priority system for pricing rules

---

## Implementation Priority

### Must Have (Phase 1-2)
1. Basic pricing tiers
2. Product/service selection
3. Simple volume discounts

### Should Have (Phase 3-4)
4. Corporate pricing
5. Custom product prices
6. Deal proposal integration

### Nice to Have (Phase 5+)
7. Advanced analytics
8. A/B testing
9. Dynamic pricing

---

## Example Use Cases

### Use Case 1: B2B Marketplace
- Platform has multiple seller companies
- Each seller can set pricing for their customers
- Volume discounts encourage bulk purchases
- Pricing tiers for different customer segments

### Use Case 2: Corporate Sales
- Company A sells to Company B
- Company A sets special pricing for Company B
- Company B sees custom prices when logged in
- Proposals automatically use corporate pricing

### Use Case 3: Deal Management
- Sales team creates deals for customers
- System auto-applies appropriate pricing
- Proposals show pricing breakdown
- Customers see savings clearly

---

## Questions to Consider

1. **Who can create pricing tiers?**
   - Platform admins only?
   - Or can companies create their own?

2. **Can pricing tiers be combined?**
   - Yes, with priority system
   - Most specific rule wins

3. **What happens if pricing rules conflict?**
   - Priority system resolves conflicts
   - Most specific rule (product-level) wins

4. **Can pricing be time-limited?**
   - Yes, validity periods (valid_from, valid_to)

5. **Can companies see other companies' pricing?**
   - No, only their own pricing and pricing set for them

---

## Next Steps

1. Review this proposal
2. Confirm requirements
3. Prioritize features
4. Start Phase 1 implementation
5. Test with sample data
6. Iterate based on feedback

---

## Catalog v2 Endpoints to Use

- `GET /api/products/{id}/catalog` for product + bundles/options/variants/attributes (`routes/api.php:60-128`).
- `POST /api/products/{id}/bundles/{bundleId}/price` for validated interactive bundle pricing (`routes/api.php:228-383`).
- Admin maintenance examples (guarded): `POST /api/products/{id}/variants/generate` (`routes/api.php:227`, roles `routes/api.php:152-155`).
