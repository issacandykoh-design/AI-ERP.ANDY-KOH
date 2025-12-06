# B2B Marketplace Design System
## UI/UX Design Specifications & Prototype Guide

---

## Executive Summary

This document defines the complete design system for the B2B marketplace prototype. All designs will be **hardcoded** with mock data first, then functionality will be added later.

---

## 1. DESIGN PRINCIPLES

### 1.1 Core Principles

**1. B2B-First Design**
- Professional, trustworthy appearance
- Clear pricing information
- Bulk ordering emphasis
- Business-focused features

**2. F&B Vertical Focus**
- Food imagery emphasis
- Fresh product showcase
- Nutritional information prominence
- Expiry date visibility

**3. Singapore Market**
- Local payment methods visible
- Singapore delivery options
- Local currency (SGD)
- Local business context

**4. User Experience**
- Clear navigation
- Fast product discovery
- Easy ordering process
- Mobile-responsive

---

## 2. COLOR PALETTE

### 2.1 Primary Colors

```css
/* Primary Brand Colors */
--primary-color: #2563eb;        /* Blue - Trust, Professional */
--primary-dark: #1e40af;        /* Dark Blue */
--primary-light: #3b82f6;       /* Light Blue */

/* Secondary Colors */
--secondary-color: #10b981;      /* Green - Fresh, Food */
--secondary-dark: #059669;       /* Dark Green */
--secondary-light: #34d399;      /* Light Green */

/* Accent Colors */
--accent-orange: #f59e0b;        /* Orange - Food, Appetite */
--accent-red: #ef4444;           /* Red - Urgency, Sales */
--accent-yellow: #fbbf24;        /* Yellow - Energy, Fresh */
```

### 2.2 Neutral Colors

```css
/* Grays */
--gray-50: #f9fafb;
--gray-100: #f3f4f6;
--gray-200: #e5e7eb;
--gray-300: #d1d5db;
--gray-400: #9ca3af;
--gray-500: #6b7280;
--gray-600: #4b5563;
--gray-700: #374151;
--gray-800: #1f2937;
--gray-900: #111827;

/* Status Colors */
--success: #10b981;
--warning: #f59e0b;
--error: #ef4444;
--info: #3b82f6;
```

---

## 3. TYPOGRAPHY

### 3.1 Font Families

```css
/* Primary Font */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

/* Headings */
font-family: 'Poppins', 'Inter', sans-serif;

/* Monospace (for codes, prices) */
font-family: 'JetBrains Mono', 'Courier New', monospace;
```

### 3.2 Font Sizes

```css
/* Headings */
--text-4xl: 2.25rem;    /* 36px - Hero titles */
--text-3xl: 1.875rem;   /* 30px - Page titles */
--text-2xl: 1.5rem;     /* 24px - Section titles */
--text-xl: 1.25rem;     /* 20px - Subsection titles */
--text-lg: 1.125rem;     /* 18px - Large text */

/* Body */
--text-base: 1rem;      /* 16px - Body text */
--text-sm: 0.875rem;    /* 14px - Small text */
--text-xs: 0.75rem;     /* 12px - Extra small */
```

---

## 4. COMPONENT LIBRARY

### 4.1 Buttons

**Primary Button:**
```html
<button class="btn btn-primary">
  Add to Cart
</button>
```

**Secondary Button:**
```html
<button class="btn btn-secondary">
  View Details
</button>
```

**Outline Button:**
```html
<button class="btn btn-outline">
  Cancel
</button>
```

**Sizes:**
- `btn-sm` - Small
- `btn-md` - Medium (default)
- `btn-lg` - Large

---

### 4.2 Cards

**Product Card:**
```html
<div class="card product-card">
  <img src="..." class="product-image">
  <div class="card-body">
    <h3 class="product-name">Product Name</h3>
    <p class="product-price">$25.00</p>
    <button class="btn btn-primary">Add to Cart</button>
  </div>
</div>
```

**Merchant Card:**
```html
<div class="card merchant-card">
  <div class="merchant-logo">...</div>
  <h3 class="merchant-name">Merchant Name</h3>
  <p class="merchant-rating">⭐⭐⭐⭐⭐ 4.8</p>
  <a href="#" class="btn btn-outline">View Store</a>
</div>
```

---

### 4.3 Forms

**Input Fields:**
```html
<div class="form-group">
  <label>Product Name</label>
  <input type="text" class="form-control" placeholder="Enter product name">
</div>
```

**Select Dropdown:**
```html
<div class="form-group">
  <label>Category</label>
  <select class="form-control">
    <option>Select category</option>
    <option>Fresh Produce</option>
    <option>Meat & Seafood</option>
  </select>
</div>
```

---

### 4.4 Badges & Labels

**Price Badge:**
```html
<span class="badge badge-price">$25.00</span>
```

**Discount Badge:**
```html
<span class="badge badge-discount">-20%</span>
```

**Status Badge:**
```html
<span class="badge badge-success">In Stock</span>
<span class="badge badge-warning">Low Stock</span>
<span class="badge badge-error">Out of Stock</span>
```

**Certification Badge:**
```html
<span class="badge badge-certification">Halal</span>
<span class="badge badge-certification">Organic</span>
```

---

## 5. PAGE STRUCTURES

### 5.1 Layout Components

**Header:**
- Logo
- Search bar
- Navigation menu
- User account dropdown
- Cart icon with count

**Footer:**
- Company info
- Quick links
- Contact info
- Social media
- Legal links

**Sidebar (Dashboard):**
- User profile
- Navigation menu
- Quick actions
- Notifications

---

## 6. KEY PAGES TO DESIGN

### 6.1 Public Pages

1. **Marketplace Homepage**
   - Hero section
   - Featured categories
   - Featured merchants
   - Trending products
   - Deals & promotions

2. **Product Listing Page**
   - Filters sidebar
   - Product grid/list view
   - Sort options
   - Pagination

3. **Product Detail Page**
   - Product images
   - Product info
   - Pricing (public/B2B)
   - Add to cart
   - Reviews & ratings

4. **Merchant Storefront**
   - Merchant info
   - Product catalog
   - About merchant
   - Reviews

5. **Cart Page**
   - Cart items
   - Order summary
   - Shipping options
   - Checkout button

6. **Checkout Page**
   - Shipping address
   - Payment method
   - Order review
   - Place order

---

### 6.2 Buyer Dashboard Pages

1. **Dashboard Home**
   - Order summary
   - Recent orders
   - Quick actions
   - Recommendations

2. **Orders List**
   - Order history
   - Order status
   - Filter & search
   - Order actions

3. **Order Detail**
   - Order info
   - Items list
   - Shipping info
   - Tracking
   - Invoice

4. **Account Settings**
   - Profile info
   - Company info
   - Payment methods
   - Addresses
   - Credit terms

---

### 6.3 Merchant Dashboard Pages

1. **Dashboard Home**
   - Sales overview
   - Recent orders
   - Low stock alerts
   - Performance metrics

2. **Products Management**
   - Product list
   - Add/edit products
   - Bulk actions
   - Categories

3. **Orders Management**
   - Order list
   - Order processing
   - Fulfillment
   - Invoices

4. **Analytics**
   - Sales charts
   - Product performance
   - Customer insights
   - Revenue reports

---

## 7. RESPONSIVE BREAKPOINTS

```css
/* Mobile First */
--breakpoint-sm: 640px;   /* Small devices */
--breakpoint-md: 768px;   /* Tablets */
--breakpoint-lg: 1024px;  /* Desktops */
--breakpoint-xl: 1280px;  /* Large desktops */
--breakpoint-2xl: 1536px; /* Extra large */
```

---

## 8. ANIMATIONS & TRANSITIONS

```css
/* Transitions */
--transition-fast: 150ms;
--transition-base: 300ms;
--transition-slow: 500ms;

/* Easing */
--ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
--ease-out: cubic-bezier(0, 0, 0.2, 1);
--ease-in: cubic-bezier(0.4, 0, 1, 1);
```

---

## 9. ICON SYSTEM

**Icon Library:** Heroicons or Font Awesome

**Common Icons:**
- Shopping cart
- User account
- Search
- Filter
- Heart (wishlist)
- Star (rating)
- Check (verified)
- Truck (delivery)
- Credit card (payment)
- Settings
- Notifications

---

## 10. MOCK DATA STRUCTURE

### 10.1 Product Mock Data

```json
{
  "id": 1,
  "name": "Fresh Organic Chicken Breast",
  "merchant": {
    "id": 1,
    "name": "Fresh Farm Co.",
    "logo": "/images/merchants/fresh-farm.jpg",
    "rating": 4.8,
    "verified": true
  },
  "images": [
    "/images/products/chicken-1.jpg",
    "/images/products/chicken-2.jpg"
  ],
  "price": {
    "public": 25.00,
    "b2b": 20.00,
    "tier_1": 18.00,
    "tier_2": 16.00
  },
  "discount": {
    "percentage": 20,
    "amount": 5.00
  },
  "stock": {
    "status": "in_stock",
    "quantity": 150,
    "low_stock_threshold": 20
  },
  "category": "Meat & Seafood",
  "subcategory": "Poultry",
  "certifications": ["Halal", "Organic"],
  "nutrition": {
    "calories": 165,
    "protein": "31g",
    "fat": "3.6g"
  },
  "allergens": ["None"],
  "expiry_date": "2024-12-31",
  "description": "Fresh organic chicken breast...",
  "rating": 4.7,
  "reviews_count": 125
}
```

---

## 11. PROTOTYPE FILE STRUCTURE

```
resources/views/prototype/
├── layouts/
│   ├── app.blade.php          # Main layout
│   ├── dashboard.blade.php     # Dashboard layout
│   └── merchant.blade.php      # Merchant layout
├── marketplace/
│   ├── index.blade.php         # Homepage
│   ├── products/
│   │   ├── index.blade.php     # Product listing
│   │   └── show.blade.php      # Product detail
│   ├── merchants/
│   │   ├── index.blade.php     # Merchant directory
│   │   └── show.blade.php      # Merchant storefront
│   └── cart.blade.php          # Shopping cart
├── buyer/
│   ├── dashboard.blade.php     # Buyer dashboard
│   ├── orders/
│   │   ├── index.blade.php     # Orders list
│   │   └── show.blade.php      # Order detail
│   └── account.blade.php       # Account settings
└── merchant/
    ├── dashboard.blade.php     # Merchant dashboard
    ├── products/
    │   ├── index.blade.php     # Products list
    │   └── create.blade.php   # Add product
    └── orders/
        ├── index.blade.php     # Orders list
        └── show.blade.php      # Order detail
```

---

## 12. DESIGN MOCKUPS - KEY SCREENS

### 12.1 Marketplace Homepage

**Layout:**
```
┌─────────────────────────────────────────────────────────┐
│  HEADER: Logo | Search | Nav | Account | Cart          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  HERO SECTION:                                           │
│  ┌──────────────────────────────────────────────┐     │
│  │  "Singapore's #1 B2B F&B Marketplace"        │     │
│  │  [Search Products...]                         │     │
│  │  [Browse Categories]                           │     │
│  └──────────────────────────────────────────────┘     │
│                                                          │
│  FEATURED CATEGORIES:                                    │
│  [Fresh Produce] [Meat & Seafood] [Beverages] ...      │
│                                                          │
│  FEATURED MERCHANTS:                                     │
│  [Merchant 1] [Merchant 2] [Merchant 3] ...            │
│                                                          │
│  TRENDING PRODUCTS:                                      │
│  [Product 1] [Product 2] [Product 3] ...                │
│                                                          │
│  DEALS & PROMOTIONS:                                     │
│  [Deal 1] [Deal 2] [Deal 3] ...                         │
│                                                          │
├─────────────────────────────────────────────────────────┤
│  FOOTER: Links | Contact | Social                       │
└─────────────────────────────────────────────────────────┘
```

---

### 12.2 Product Detail Page

**Layout:**
```
┌─────────────────────────────────────────────────────────┐
│  HEADER                                                  │
├─────────────────────────────────────────────────────────┤
│  [Breadcrumbs: Home > Category > Product]               │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  LEFT: Product Images                                    │
│  ┌──────────┐                                           │
│  │  [Main]  │                                           │
│  │  Image   │                                           │
│  └──────────┘                                           │
│  [Thumbnail] [Thumbnail] [Thumbnail]                   │
│                                                          │
│  RIGHT: Product Info                                     │
│  Product Name                                            │
│  ⭐⭐⭐⭐⭐ 4.7 (125 reviews)                            │
│                                                          │
│  Pricing:                                                │
│  Your Price: $20.00                                      │
│  Original: $25.00                                        │
│  You Save: $5.00 (20%)                                   │
│                                                          │
│  Stock: In Stock (150 available)                         │
│                                                          │
│  Quantity: [ - ] [ 1 ] [ + ]                            │
│                                                          │
│  [Add to Cart] [Buy Now] [Save for Later]               │
│                                                          │
│  Delivery Options:                                       │
│  ⚡ Same Day | 🚚 Next Day | 📦 Standard                 │
│                                                          │
│  Certifications: [Halal] [Organic]                      │
│                                                          │
├─────────────────────────────────────────────────────────┤
│  TABS: Description | Specifications | Reviews | FAQs     │
│                                                          │
│  Description Tab:                                        │
│  Full product description...                             │
│                                                          │
│  Nutrition Facts:                                        │
│  Calories: 165 | Protein: 31g | Fat: 3.6g              │
│                                                          │
│  Allergens: None                                         │
│                                                          │
│  Storage: Keep refrigerated...                           │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 13. NEXT STEPS

1. Create layout templates
2. Create homepage prototype
3. Create product pages
4. Create cart & checkout
5. Create dashboards
6. Add mock data
7. Style with CSS
8. Make responsive

---

**This design system will guide all prototype creation!**

