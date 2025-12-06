# B2B Marketplace Master Plan

## Panels
- **Superadmin Panel**
  - Toggle module on/off; package inclusion (`module_in_package`)
  - Global defaults: currencies, locales, tax rules, incoterms, payment methods, AI feature flags
  - Compliance catalogs: HS codes, certificate types, restricted categories
- **Company Admin Panel**
  - Company marketplace settings: allowed roles, supplier onboarding rules, buyer eligibility, contract terms
  - Warehouses, shipping carriers, default lead times, return policies
  - Pricing governance: floor/ceiling price, MAP, discount policies
- **Supplier Panel**
  - Company profile: certifications, business types, MOQ, lead time
  - Catalog management: products, bundles, variants, tier prices
  - Inventory per warehouse, availability windows, blackout dates
  - Quotes, negotiations, orders, shipments
- **Buyer Panel**
  - RFQ creation, quote comparison, negotiation threads
  - Saved lists, preferred suppliers, approval flows, budgets
  - Orders, tracking, returns, invoices

## Locales & Internationalization
- **Translation Keys**: `resources/lang/{locale}/modules/marketplace.php`
- **Translatable Fields**
  - Product: `name`, `short_description`, `long_description`, `attribute_values`
  - Bundle: `title`, `description`
  - Category: `name`
- **Per-Locale Content**
  - Slugs: `product_slug_{locale}`
  - Currency & formatting: currency codes, rounding, separators
  - Measurement units (imperial/metric)
- **Addresses & Timezones**
  - Company & warehouse addresses with country/region
  - Date/time storage in UTC; display per user timezone

## Core Data Schema (New Fields)
- **Products**
  - `sku`, `slug`, `category_id`, `brand`, `barcode`
  - `name_{locale}`, `short_description_{locale}`, `long_description_{locale}`
  - `uom`, `weight`, `dimensions`, `hs_code`, `origin_country`
  - `variant_group_id`, `attribute_set_id`, `status`, `visibility`
  - `min_order_qty`, `max_order_qty`, `step_qty`
  - Media: `main_image`, `gallery[]`, `docs[]` (spec sheets, MSDS)
- **Attributes & Variations**
  - `attribute_sets`: `{name, code}`
  - `attributes`: `{code, type(text/number/list), unit, is_variant}`
  - `attribute_values`: per product and per locale
  - `product_variants`: `{variant_code, product_id, attribute_values(json), base_price, cost, upc}`
- **Bundles/Kits**
  - `bundles`: `{bundle_code, title_{locale}, description_{locale}, status}`
  - `bundle_items`: `{bundle_id, product_id|variant_id, qty}`
  - Pricing: `bundle_price` or computed rules (sum minus discount)
- **Pricing & Tiers**
  - `tier_prices`: `{product_id|variant_id, tier_name, min_qty, price, currency, valid_from, valid_to}`
  - `volume_discounts`: `{breaks:[{min_qty, discount_%|price}], customer_groups}`
  - `contract_prices`: `{buyer_company_id, product_id|variant_id, price, terms}`
  - Governance: `floor_price`, `ceiling_price`, `map_price`, `tax_category`
- **Inventory & Availability**
  - `inventory`: `{warehouse_id, product_id|variant_id, on_hand, reserved, available}`
  - `warehouse`: `{name, address, timezone}`
  - `availability_windows`: blackout/maintenance windows
- **RFQ & Quotes**
  - `rfqs`: `{buyer_company_id, deadline, incoterm, ship_to, notes}`
  - `rfq_items`: `{rfq_id, product_id|variant_id, qty, target_price?, attributes_override?}`
  - `quotes`: `{supplier_company_id, rfq_id, total, currency, payment_terms, validity}`
  - `quote_items`: `{quote_id, product_id|variant_id, qty, unit_price, taxes, discounts}`
  - `negotiations`: threaded messages and offer revisions
- **Orders & Fulfillment**
  - `orders`: `{buyer_company_id, supplier_company_id, incoterm, payment_method, status}`
  - `order_items`: `{order_id, product_id|variant_id, qty, unit_price, taxes, discounts}`
  - `shipments`: `{order_id, carrier, tracking_no, ship_from, ship_to, packages}`
- **Compliance & Docs**
  - `certifications`: `{type, issuer, valid_from, valid_to}` per supplier/product
  - `documents`: spec sheets, CoA, MSDS, test reports
- **Payments & Tax**
  - `allowed_methods`: per company/supplier
  - `tax_rules`: per locale/country/state, VAT/GST specifics
- **AI Signals & Logs**
  - `ai_recommendation_jobs`: `{context: pricing|matching|fraud, payload_hash, status}`
  - `ai_logs`: request/response metadata without secrets, latency, model

## Permissions & Roles
- Seed CRUD permissions for Catalog, RFQ, Quote, Negotiation, Order, Shipment, Pricing
- Role-based visibility: Admin, Supplier, Buyer, Employee Client
- `ModuleSetting` entries per company and role; `is_allowed` driven by `package->module_in_package`

## Notifications (Email Standards)
- Use `MailMessage` with `line()` and `action()`
- Public links via `getDomainSpecificUrl()`; `APP_URL` configured
- Sender derived from `smtp_settings.mail_from_email` else `mail_username`
- Minimal HTML; queues `sync` in local dev

## API & Events
- Events: `RFQCreated`, `QuoteSubmitted`, `OrderPlaced`, `ShipmentUpdated`
- Laravel → Python AI endpoints (signed JSON)
  - `POST /ai/recommend/suppliers`
  - `POST /ai/recommend/pricing`
- Feature flags to disable AI calls and use deterministic fallbacks

## UI Pages (By Panel)
- **Supplier**: Product editor (attributes, variants, bundles, tiers), Inventory, Quotes, Orders, Shipments
- **Buyer**: RFQ builder, Quote comparison, Negotiation, Order placement, Tracking
- **Admin**: Governance settings, catalogs, compliance, pricing rules, permissions

## Migrations (Initial Set)
- `products`, `product_variants`, `attribute_sets`, `attributes`, `attribute_values`
- `bundles`, `bundle_items`
- `tier_prices`, `volume_discounts`, `contract_prices`
- `warehouses`, `inventory`, `availability_windows`
- `rfqs`, `rfq_items`, `quotes`, `quote_items`, `negotiations`
- `orders`, `order_items`, `shipments`
- `certifications`, `documents`
- `ai_recommendation_jobs`, `ai_logs`

## Validation & Rollout
- Module scaffold visible in `php artisan module:list`
- Activation command creates `ModuleSetting` per company and seeds permissions
- UI flows for Product → RFQ → Quote → Order verified across locales and roles

If this covers your requirements, I will consolidate the existing B2B Marketplace documents into a single master doc and proceed to implement the schema, routes, permissions, and activation wiring accordingly.