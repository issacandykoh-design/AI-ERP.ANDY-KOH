# Complete Product Page Requirements for B2B Marketplace
## Combo Products & Additional Features Needed

---

## Executive Summary

This document outlines all product page enhancements needed for a complete B2B marketplace, with special focus on **combo/bundle products** (Product A + B + C) and F&B vertical requirements.

---

## 1. COMBO/BUNDLE PRODUCTS SYSTEM

### 1.1 What Are Combo Products?

**Definition:** A combo product is a bundle of multiple individual products sold together, often at a discounted price.

**F&B Examples:**
- **Meal Combo:** Burger + Fries + Drink
- **Set Meal:** Main Course + Appetizer + Dessert
- **Party Package:** 10 Burgers + 5 Pizzas + 20 Drinks
- **Breakfast Set:** Coffee + Sandwich + Pastry
- **Catering Package:** Multiple items bundled together

### 1.2 Combo Product Structure

```
Combo Product: "Family Meal Deal"
├── Product A: Burger (x2) - $10 each
├── Product B: Fries (x2) - $3 each
├── Product C: Drink (x2) - $2 each
└── Combo Price: $25 (Save $5 vs individual)
```

### 1.3 Database Schema for Combo Products

```sql
-- New table: product_bundles (combo products)
product_bundles
├── id
├── product_id (foreign key to products - the combo product itself)
├── bundle_type (enum: fixed, customizable, optional)
├── bundle_name (string) - e.g., "Family Meal Deal"
├── bundle_description (text)
├── bundle_price (decimal) - Fixed combo price
├── bundle_discount_type (enum: percentage, fixed_amount, override_price)
├── bundle_discount_value (decimal)
├── min_items_required (integer) - Minimum items to select
├── max_items_allowed (integer) - Maximum items allowed
├── is_active (boolean)
├── valid_from (datetime, nullable)
├── valid_to (datetime, nullable)
├── company_id (foreign key)
└── timestamps

-- New table: product_bundle_items (items in combo)
product_bundle_items
├── id
├── bundle_id (foreign key to product_bundles)
├── product_id (foreign key to products - individual product in bundle)
├── quantity (integer) - How many of this product in bundle
├── is_required (boolean) - Must include this item
├── is_optional (boolean) - Can choose this item
├── can_substitute (boolean) - Can replace with alternative
├── substitute_product_id (foreign key, nullable) - Alternative product
├── display_order (integer) - Order in bundle
├── default_selected (boolean) - Pre-selected in bundle
└── timestamps

-- New table: product_bundle_options (customization options)
product_bundle_options
├── id
├── bundle_id (foreign key to product_bundles)
├── option_group_name (string) - e.g., "Choose Your Drink"
├── option_type (enum: single_choice, multiple_choice)
├── min_selections (integer)
├── max_selections (integer)
├── display_order (integer)
└── timestamps

-- New table: product_bundle_option_items (options within groups)
product_bundle_option_items
├── id
├── option_id (foreign key to product_bundle_options)
├── product_id (foreign key to products - option product)
├── price_adjustment (decimal) - Additional cost (+$1) or discount (-$0.50)
├── is_default (boolean)
├── display_order (integer)
└── timestamps
```

### 1.4 Combo Product Types

**Type 1: Fixed Bundle**
- Pre-defined products, cannot customize
- Example: "Happy Meal" - Fixed items

**Type 2: Customizable Bundle**
- Choose from options within groups
- Example: "Build Your Own Combo" - Choose burger, choose drink, choose side

**Type 3: Optional Bundle**
- Add optional items to base product
- Example: "Burger + Add Fries (+$3) + Add Drink (+$2)"

### 1.5 Combo Product Pricing Logic

```php
class ComboProductService
{
    /**
     * Calculate combo product price
     */
    public function calculateComboPrice($bundleId, $selectedItems = [])
    {
        $bundle = ProductBundle::findOrFail($bundleId);
        
        // Fixed bundle price
        if ($bundle->bundle_type === 'fixed') {
            return $bundle->bundle_price;
        }
        
        // Customizable bundle - calculate from selected items
        if ($bundle->bundle_type === 'customizable') {
            $totalPrice = 0;
            
            foreach ($selectedItems as $item) {
                $product = Product::find($item['product_id']);
                $quantity = $item['quantity'] ?? 1;
                $totalPrice += $product->price * $quantity;
            }
            
            // Apply bundle discount
            if ($bundle->bundle_discount_type === 'percentage') {
                $discount = $totalPrice * ($bundle->bundle_discount_value / 100);
                return $totalPrice - $discount;
            }
            
            if ($bundle->bundle_discount_type === 'fixed_amount') {
                return $totalPrice - $bundle->bundle_discount_value;
            }
            
            return $totalPrice;
        }
        
        // Optional bundle - base price + optional items
        if ($bundle->bundle_type === 'optional') {
            $baseProduct = Product::find($bundle->base_product_id);
            $totalPrice = $baseProduct->price;
            
            foreach ($selectedItems as $item) {
                $product = Product::find($item['product_id']);
                $totalPrice += $product->price * ($item['quantity'] ?? 1);
            }
            
            return $totalPrice;
        }
    }
}
```

### 1.6 Combo Product Display

**UI Example:**
```
┌─────────────────────────────────────────────────────────┐
│  🍔 Family Meal Deal - $25 (Save $5)                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Includes:                                              │
│  ✅ Burger (x2) - Required                             │
│  ✅ Fries (x2) - Required                               │
│                                                          │
│  Choose Your Drink:                                     │
│  ⚪ Cola (+$0)                                          │
│  ⚪ Orange Juice (+$1)                                  │
│  ⚪ Water (+$0)                                         │
│                                                          │
│  Add Extras (Optional):                                 │
│  ☐ Extra Cheese (+$2)                                  │
│  ☐ Onion Rings (+$3)                                   │
│                                                          │
│  Total: $25.00                                          │
│  Individual Price: $30.00                               │
│  You Save: $5.00                                        │
│                                                          │
│  [Add to Cart]                                          │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 2. PRODUCT VARIATIONS & OPTIONS

### 2.1 Product Variations

**What:** Same product with different attributes (size, color, flavor, etc.)

**F&B Examples:**
- **Size:** Small, Medium, Large
- **Flavor:** Vanilla, Chocolate, Strawberry
- **Spice Level:** Mild, Medium, Hot
- **Temperature:** Hot, Cold, Iced

### 2.2 Database Schema

```sql
-- Product variations
product_variations
├── id
├── product_id (parent product)
├── variation_name (string) - e.g., "Size", "Flavor"
├── variation_type (enum: single, multiple)
├── is_required (boolean)
├── display_order (integer)
└── timestamps

-- Variation options
product_variation_options
├── id
├── variation_id (foreign key)
├── option_name (string) - e.g., "Small", "Large"
├── option_value (string) - e.g., "S", "L"
├── price_adjustment (decimal) - +$2 for large
├── sku_suffix (string) - "L" for large
├── stock_quantity (integer, nullable) - Stock for this variation
├── is_default (boolean)
├── display_order (integer)
└── timestamps

-- Product variant combinations (SKU generation)
product_variants
├── id
├── product_id (parent product)
├── variant_sku (string, unique) - Generated SKU
├── variant_name (string) - "Small Red"
├── price (decimal) - Final price with adjustments
├── stock_quantity (integer)
├── is_active (boolean)
├── variant_data (json) - {size: "S", color: "Red"}
└── timestamps
```

---

## 3. PRODUCT ATTRIBUTES & SPECIFICATIONS

### 3.1 Product Attributes System

**What:** Structured product information (dimensions, weight, material, etc.)

```sql
-- Product attributes
product_attributes
├── id
├── attribute_name (string) - e.g., "Weight", "Dimensions"
├── attribute_type (enum: text, number, select, boolean)
├── is_searchable (boolean)
├── is_filterable (boolean)
├── display_order (integer)
└── timestamps

-- Product attribute values
product_attribute_values
├── id
├── product_id (foreign key)
├── attribute_id (foreign key)
├── attribute_value (text) - The actual value
└── timestamps
```

### 3.2 F&B-Specific Attributes

**Required Fields:**
- Allergen Information
- Nutritional Information (calories, protein, carbs, etc.)
- Ingredients List
- Serving Size
- Storage Requirements
- Preparation Time
- Shelf Life / Expiry Information
- Halal / Kosher / Vegetarian / Vegan badges
- Spice Level
- Cuisine Type
- Dietary Restrictions

---

## 4. PRODUCT PAGE ENHANCEMENTS

### 4.1 Product Media

**Current:** Single default image + ProductFiles

**Needed:**
- ✅ Multiple product images (gallery)
- ✅ Product videos
- ✅ 360° view (optional)
- ✅ Zoom functionality
- ✅ Image alt text for SEO

**Enhancement:**
- Extend ProductFiles to support video
- Add image ordering/priority
- Add image captions/alt text

---

### 4.2 Product Information Sections

**Current:** Basic description

**Needed:**
- ✅ **Overview** - Short description
- ✅ **Full Description** - Detailed description
- ✅ **Specifications** - Technical details
- ✅ **Ingredients** - For F&B
- ✅ **Nutritional Info** - For F&B
- ✅ **Allergen Info** - For F&B
- ✅ **Storage Instructions** - For F&B
- ✅ **Preparation Instructions** - For F&B
- ✅ **FAQs** - Product-specific FAQs
- ✅ **Reviews & Ratings** - Customer reviews
- ✅ **Related Products** - Similar/recommended products
- ✅ **Recently Viewed** - Recently viewed products

---

### 4.3 Product Availability & Stock

**Current:** Basic inventory tracking

**Needed:**
- ✅ **Real-time Stock Display** - "In Stock", "Low Stock", "Out of Stock"
- ✅ **Stock Quantity Display** - "Only 5 left!"
- ✅ **Pre-order Support** - "Pre-order now, ships in 2 weeks"
- ✅ **Backorder Support** - "Backordered, available in 1 month"
- ✅ **Stock Alerts** - "Notify me when back in stock"
- ✅ **Multi-location Stock** - Stock per warehouse/branch
- ✅ **Stock Reservation** - Reserve stock for pending orders

---

### 4.4 Product Pricing Display

**Current:** Single price

**Needed:**
- ✅ **Public Price** - Price for public customers
- ✅ **B2B Price** - Price for B2B customers
- ✅ **Pricing Comparison** - "Your Price vs Original Price"
- ✅ **Savings Display** - "You Save $X"
- ✅ **Volume Pricing** - "Buy 10+ for $X each"
- ✅ **Tier Pricing** - "Enterprise Tier: $X"
- ✅ **Discount Badges** - "20% OFF", "Best Value"
- ✅ **Price History** - Price change history (optional)

---

### 4.5 Product Ordering Options

**Current:** Basic quantity input

**Needed:**
- ✅ **Minimum Order Quantity (MOQ)** - "Minimum 10 units"
- ✅ **Maximum Order Quantity** - "Maximum 100 units"
- ✅ **Bulk Order Discounts** - Show volume discounts
- ✅ **Increment Quantity** - "Order in multiples of 5"
- ✅ **Lead Time Display** - "Ships in 3-5 business days"
- ✅ **Preparation Time** - "Ready in 15 minutes" (F&B)
- ✅ **Pre-order Date** - "Available from [date]"
- ✅ **Subscription Option** - "Subscribe & Save 10%"

---

### 4.6 Product Shipping & Delivery

**Current:** Not on product page

**Needed:**
- ✅ **Shipping Cost Calculator** - Calculate shipping
- ✅ **Delivery Options** - Standard, Express, Same-day
- ✅ **Delivery Time Estimate** - "Delivers in 2-3 days"
- ✅ **Free Shipping Threshold** - "Free shipping on orders $50+"
- ✅ **Shipping Restrictions** - "Not available in [location]"
- ✅ **Weight & Dimensions** - For shipping calculation
- ✅ **Delivery Zones** - Available delivery zones

---

### 4.7 Product Social Proof

**Current:** None

**Needed:**
- ✅ **Product Reviews** - Customer reviews
- ✅ **Product Ratings** - Star ratings
- ✅ **Review Count** - "Based on 125 reviews"
- ✅ **Verified Purchase Badge** - "Verified Purchase"
- ✅ **Customer Photos** - User-submitted photos
- ✅ **Q&A Section** - Questions and answers
- ✅ **Social Shares** - Share on social media
- ✅ **Wishlist Count** - "Added by 50 customers"

---

### 4.8 Product Badges & Labels

**Current:** None

**Needed:**
- ✅ **New Product** - "NEW" badge
- ✅ **Best Seller** - "BESTSELLER" badge
- ✅ **Featured** - "FEATURED" badge
- ✅ **On Sale** - "SALE" badge
- ✅ **Limited Edition** - "LIMITED" badge
- ✅ **Exclusive** - "EXCLUSIVE" badge
- ✅ **Certified** - "HALAL", "ORGANIC", etc.
- ✅ **Award Winner** - "Award Winner 2024"

---

### 4.9 Product SEO & Marketing

**Current:** Basic

**Needed:**
- ✅ **SEO Title** - Custom meta title
- ✅ **SEO Description** - Custom meta description
- ✅ **SEO Keywords** - Keywords for search
- ✅ **URL Slug** - Custom URL slug
- ✅ **Open Graph Tags** - For social sharing
- ✅ **Schema Markup** - Structured data
- ✅ **Product Tags** - Tags for categorization
- ✅ **Related Keywords** - Related search terms

---

### 4.10 Product Comparison

**Current:** None

**Needed:**
- ✅ **Compare Products** - Side-by-side comparison
- ✅ **Comparison Table** - Feature comparison
- ✅ **Alternative Products** - "You might also like"
- ✅ **Upgrade/Downgrade Options** - "Upgrade to Pro"

---

### 4.11 Product Recommendations

**Current:** None

**Needed:**
- ✅ **Frequently Bought Together** - "Customers also bought"
- ✅ **You May Also Like** - Similar products
- ✅ **Recently Viewed** - Recently viewed products
- ✅ **Trending Products** - Popular products
- ✅ **Personalized Recommendations** - Based on history

---

### 4.12 Product Analytics & Tracking

**Current:** Basic

**Needed:**
- ✅ **View Count** - Product views
- ✅ **Add to Cart Count** - How many added to cart
- ✅ **Purchase Count** - How many purchased
- ✅ **Conversion Rate** - Views to purchases
- ✅ **Average Order Value** - Average order value
- ✅ **Return Rate** - Return/refund rate
- ✅ **Customer Lifetime Value** - CLV

---

## 5. F&B-SPECIFIC PRODUCT FEATURES

### 5.1 F&B Product Fields

```sql
-- Add to products table or separate table
product_fnb_details
├── id
├── product_id (foreign key)
├── preparation_time (integer) - Minutes
├── serving_size (string) - "1 person", "2-3 people"
├── serving_temperature (enum: hot, cold, room_temp)
├── spice_level (enum: none, mild, medium, hot, extra_hot)
├── cuisine_type (string) - "Italian", "Chinese", etc.
├── dietary_tags (json) - ["vegetarian", "vegan", "gluten-free"]
├── halal_certified (boolean)
├── kosher_certified (boolean)
├── organic (boolean)
├── ingredients (text) - Full ingredients list
├── allergens (json) - ["nuts", "dairy", "gluten"]
├── nutritional_info (json) - {calories: 250, protein: 15g, ...}
├── storage_instructions (text)
├── shelf_life_days (integer)
├── best_before_days (integer)
├── requires_refrigeration (boolean)
├── can_freeze (boolean)
└── timestamps
```

### 5.2 F&B Product Display

**Example:**
```
┌─────────────────────────────────────────────────────────┐
│  🍕 Margherita Pizza                                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Size: [Small $10] [Medium $15] [Large $20] ⭐        │
│                                                          │
│  ⏱️ Preparation Time: 15 minutes                        │
│  👥 Serves: 2-3 people                                  │
│  🌶️ Spice Level: None                                  │
│  🍕 Cuisine: Italian                                     │
│                                                          │
│  🏷️ Tags: Vegetarian • Halal • Fresh                   │
│                                                          │
│  Ingredients:                                          │
│  • Pizza dough                                          │
│  • Tomato sauce                                         │
│  • Mozzarella cheese                                    │
│  • Fresh basil                                          │
│                                                          │
│  ⚠️ Allergens: Gluten, Dairy                            │
│                                                          │
│  📊 Nutritional Info (per serving):                    │
│  Calories: 250 | Protein: 12g | Carbs: 30g             │
│                                                          │
│  💾 Storage: Keep refrigerated, consume within 2 days  │
│                                                          │
│  [Add to Cart]                                          │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 6. PRODUCT PAGE LAYOUT

### 6.1 Complete Product Page Structure

```
┌─────────────────────────────────────────────────────────┐
│  PRODUCT PAGE HEADER                                    │
│  - Breadcrumbs                                          │
│  - Product name                                         │
│  - Product badges (NEW, BESTSELLER, etc.)              │
│  - Share buttons                                        │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  LEFT COLUMN: PRODUCT IMAGES                            │
│  - Main product image (zoomable)                        │
│  - Image gallery (thumbnails)                           │
│  - Video (if available)                                 │
│  - 360° view (if available)                             │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  RIGHT COLUMN: PRODUCT INFO                              │
│  - Product name                                         │
│  - Product rating & reviews                            │
│  - Price (public/B2B)                                   │
│  - Savings display                                      │
│  - Stock status                                         │
│  - Product variations (size, flavor, etc.)            │
│  - Quantity selector                                    │
│  - Add to Cart button                                   │
│  - Buy Now button                                       │
│  - Wishlist button                                      │
│  - Delivery options                                     │
│  - Shipping calculator                                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  PRODUCT DETAILS TABS                                   │
│  [Description] [Specifications] [Reviews] [FAQs]       │
│                                                          │
│  Description Tab:                                       │
│  - Full product description                             │
│  - Key features                                         │
│  - Benefits                                             │
│                                                          │
│  Specifications Tab:                                    │
│  - Technical specifications                             │
│  - Dimensions & weight                                  │
│  - Materials                                            │
│                                                          │
│  Reviews Tab:                                           │
│  - Customer reviews                                     │
│  - Rating breakdown                                     │
│  - Review form                                          │
│                                                          │
│  FAQs Tab:                                              │
│  - Product FAQs                                         │
│  - Q&A section                                          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  RELATED PRODUCTS                                       │
│  - Frequently bought together                          │
│  - You may also like                                    │
│  - Recently viewed                                      │
└─────────────────────────────────────────────────────────┘
```

---

## 7. DATABASE MIGRATIONS NEEDED

### 7.1 New Tables

1. `product_bundles` - Combo products
2. `product_bundle_items` - Items in bundles
3. `product_bundle_options` - Bundle customization options
4. `product_bundle_option_items` - Option items
5. `product_variations` - Product variations
6. `product_variation_options` - Variation options
7. `product_variants` - Variant combinations
8. `product_attributes` - Product attributes
9. `product_attribute_values` - Attribute values
10. `product_fnb_details` - F&B specific details
11. `product_reviews` - Product reviews
12. `product_ratings` - Product ratings
13. `product_tags` - Product tags
14. `product_related` - Related products
15. `product_faqs` - Product FAQs

### 7.2 Modified Tables

**products table additions:**
```sql
ALTER TABLE products ADD COLUMN:
├── product_type (enum: simple, bundle, variation_parent)
├── parent_product_id (nullable) - For variations
├── min_order_quantity (integer, default: 1)
├── max_order_quantity (integer, nullable)
├── lead_time_days (integer, nullable)
├── weight_kg (decimal, nullable) - For shipping
├── length_cm (decimal, nullable)
├── width_cm (decimal, nullable)
├── height_cm (decimal, nullable)
├── seo_title (string, nullable)
├── seo_description (text, nullable)
├── seo_keywords (string, nullable)
├── url_slug (string, unique, nullable)
├── view_count (integer, default: 0)
├── purchase_count (integer, default: 0)
├── rating_average (decimal, nullable)
├── rating_count (integer, default: 0)
├── is_featured (boolean, default: false)
├── is_bestseller (boolean, default: false)
├── featured_until (datetime, nullable)
├── tags (json, nullable) - Product tags
└── badge (string, nullable) - Product badge
```

---

## 8. IMPLEMENTATION PRIORITY

### Phase 1: Core Product Enhancements (Week 1-2)
1. ✅ Product variations system
2. ✅ Product attributes system
3. ✅ Enhanced product images (gallery)
4. ✅ Product tags
5. ✅ SEO fields

### Phase 2: Combo Products (Week 3-4)
1. ✅ Combo/bundle products system
2. ✅ Fixed bundles
3. ✅ Customizable bundles
4. ✅ Bundle pricing logic
5. ✅ Bundle display UI

### Phase 3: F&B Features (Week 5)
1. ✅ F&B-specific fields
2. ✅ Allergen information
3. ✅ Nutritional information
4. ✅ Preparation time
5. ✅ Storage instructions

### Phase 4: Social Proof (Week 6)
1. ✅ Product reviews
2. ✅ Product ratings
3. ✅ Customer photos
4. ✅ Q&A section

### Phase 5: Advanced Features (Week 7-8)
1. ✅ Product recommendations
2. ✅ Product comparison
3. ✅ Stock alerts
4. ✅ Wishlist integration
5. ✅ Analytics tracking

---

## 9. SUMMARY

### What We're Adding:

**Combo Products:**
- ✅ Fixed bundles
- ✅ Customizable bundles
- ✅ Optional bundles
- ✅ Bundle pricing & discounts

**Product Variations:**
- ✅ Size, flavor, color options
- ✅ Variation pricing
- ✅ Variant SKU generation

**Product Attributes:**
- ✅ Structured specifications
- ✅ Searchable/filterable attributes
- ✅ F&B-specific attributes

**Product Page Enhancements:**
- ✅ Enhanced media (gallery, videos)
- ✅ Multiple information sections
- ✅ Stock availability display
- ✅ Pricing comparison
- ✅ Shipping calculator
- ✅ Reviews & ratings
- ✅ Product badges
- ✅ SEO optimization
- ✅ Product recommendations

**F&B-Specific:**
- ✅ Preparation time
- ✅ Allergen information
- ✅ Nutritional information
- ✅ Storage requirements
- ✅ Dietary tags
- ✅ Certifications (Halal, Kosher, etc.)

---

**This comprehensive product page system will support a complete B2B marketplace with combo products, variations, and F&B-specific features!**

