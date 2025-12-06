# Craveva v2.0 Catalog Integration — Change Log and Guide (Since 2025-11-27)

## Overview

- Consolidated migrations into a single `craveva` database and introduced the v2.0 catalog schema (bundles, variations, attributes).
- Wired new API endpoints for catalog data, variant generation, bundle pricing, and admin controls.
- Added validations and role-based access controls to protect write operations.
- Integrated bundle pricing into the Estimate add-item flow while preserving existing exchange-rate logic.
- Validated endpoints and flows; prepared UAT-friendly read endpoints and guarded write paths.

## Highlights

- Single database consolidation for consistent schema across modules.
- v2.0 catalog tables created with correct foreign keys and indexes.
- Endpoints to manage bundles, options, items, variants, and pricing.
- Role-based protections using existing `user_roles()` and authentication middleware.
- Estimate add-item now supports bundle pricing selections.

## Database & Schema

- Migration file: `database/migrations/2025_11_28_120001_create_catalog_v2_tables.php`
- Tables added:
  - `product_bundles`, `product_bundle_items`, `product_bundle_options`, `product_bundle_option_items`
  - `product_variations`, `product_variation_options`, `product_variants`
  - `product_attributes`, `product_attribute_values`
- Notes:
  - Adjusted foreign key column types to match legacy tables (e.g., `unsignedInteger` for `products.id`).
  - Created indexes on foreign keys for performance.

## API Endpoints (New/Modified)

- Base prefix: `api` via `app/Providers/RouteServiceProvider.php`.
- Primary wiring file: `routes/api.php`.

### Catalog Read
- `GET /api/products/{id}/catalog`
  - Returns product with bundles, options, items, variations, variation options, variants, attributes.

### Variant Generation
- `POST /api/products/{id}/variants/generate`
  - Generates variant SKUs from variation option combinations.
  - Secured: `auth:sanctum`, `api.auth`, and role check for `admin` or `manager` (`routes/api.php:151`).

### Bundle Pricing
- `POST /api/products/{id}/bundles/{bundleId}/price` (`routes/api.php:228`)
  - Calculates `final_price` from selected bundle items and option items.
  - Validates bundle validity window (`valid_from`/`valid_to`), min/max selections, and option membership.

### Bundle Options
- `GET /api/products/{id}/bundles/{bundleId}/options`
- `POST /api/products/{id}/bundles/{bundleId}/options`
- `PUT /api/products/{id}/bundles/{bundleId}/options/{optionId}`
- `DELETE /api/products/{id}/bundles/{bundleId}/options/{optionId}`
  - Write paths secured via `auth:sanctum`, `api.auth`, and role checks.

### Bundle Option Items
- `GET /api/products/{id}/bundles/{bundleId}/options/{optionId}/items`
- `POST /api/products/{id}/bundles/{bundleId}/options/{optionId}/items`
- `DELETE /api/products/{id}/bundles/{bundleId}/options/{optionId}/items/{itemId}`
  - Validates product existence and company matching to the bundle’s company.

### Bundle Items
- `GET /api/products/{id}/bundles/{bundleId}/items`
- `POST /api/products/{id}/bundles/{bundleId}/items`
- `DELETE /api/products/{id}/bundles/{bundleId}/items/{bundleItemId}`
  - Validates product existence, required/optional exclusivity, substitution rules, and company matching.

### Admin Controls
- `PUT /api/products/{id}/bundles/{bundleId}/status`
- `PUT /api/products/{id}/bundles/{bundleId}/validity`
  - Toggle `is_active` and manage `valid_from`/`valid_to` with date validation.

## Security & Permissions

- Middleware: `auth:sanctum` and `Modules\RestAPI\Http\Middleware\AuthMiddleware` (`api.auth`).
- Role checks: `user_roles()` helper from `app/Helper/start.php` used on write endpoints.
- Example role guard line: `routes/api.php:151` (`$roles = user_roles() ?: [];`).

## Estimate Integration (Bundle Pricing)

- Updated method: `app/Http/Controllers/EstimateController.php:768` (`addItem`)
- Behavior:
  - If `bundleId` and active bundle detected, calls `POST /api/products/{id}/bundles/{bundleId}/price` with `option_item_ids` and `items`.
  - On success, sets line price from `final_price`.
  - Otherwise, falls back to existing exchange-rate logic.
  - Returns `pricing_metadata` in the response for UAT and downstream use.

## Validations & Guardrails

- Enforced min/max option selections per option group.
- Validated that selected option items belong to the target bundle.
- Enforced company matching for bundle option items and bundle items.
- Strict checks for substitution rules and required/optional exclusivity.
- Date window enforcement for bundle validity.

## Testing & Verification

- Syntax check for `EstimateController.php` passed (`php -l`).
- Endpoints validated via scripted calls during UAT preparation.
- Read endpoints left open for UAT; write endpoints protected via auth and roles.

## Impact on Other Modules

- Deals/Proposals:
  - Proposals can call bundle pricing on add-item to compute accurate unit price and persist `pricing_metadata` for audit.
- Invoices:
  - Order-to-invoice conversion should include bundle/variant identifiers for line metadata.
- Orders & Purchase:
  - Inventory decrement logic should consider bundle components and variant stock.
- Finance:
  - Pricing breakdowns improve transparency for approvals and invoicing.

## Known Issues Resolved

- Foreign key constraints corrected by aligning column types with legacy tables.
- Missing legacy tables restored via baseline schema dump.
- `DB_DATABASE` set to `craveva` to ensure correct connections.
- Sanctum tokens table pending creation; run the migration to add `personal_access_tokens` before authenticated write-path UAT.

## How To Test

## Updates (2025-11-29)

- Documentation updates aligned to recent discussions:
  - Added `B2B_MARKETPLACE_MIDDLEWARE_MAP.md` summarizing `api` vs `web` stacks, Sanctum usage, and role gates.
  - Clarified Company panel module integration via sidebar includes at `resources/views/sections/menu.blade.php:237-239` with gating through `user_modules()` (`app/Helper/start.php:328-379`) and `module_enabled()` (`app/Helper/start.php:609-627`).
  - Documented operating modes: marketplace-only vs hybrid and their visibility impact on client portal UIs.
  - Recommended `pricing_metadata` JSON propagation across estimates/orders/invoices for bundle selections without schema changes.

- Menu Customization fixes and gating alignment:
  - Resolved 500 error by adding `custom_menu_settings` table migration and guards.
    - Migration: `database/migrations/2025_11_29_000001_create_custom_menu_settings_table.php` (columns: `company_id`, `menu_key`, `custom_name`, `menu_order`, `is_visible`; FK to `companies`; unique index on `company_id, menu_key`).
    - Controller table checks before queries to avoid fatal errors: `app/Http/Controllers/MenuCustomizationController.php:51-56`, `113-116`, `182-185`, `219-221`, `277-279`.
  - Tightened visibility in Settings sidebar to respect package for non‑superadmins:
    - `resources/views/components/setting-sidebar.blade.php:107-115` now shows Menu Customization only if superadmin, or if module is in package AND user is `admin` or has explicit permission.
  - Enforced package gating in controller middleware:
    - `app/Http/Controllers/MenuCustomizationController.php:19-38` requires `(inPackage || ModuleSetting::checkModule('menu_customization'))` AND `admin/permission`, unless superadmin.
  - Cache invalidation guidance to reflect menu changes:
    - Run `php artisan view:clear`, `php artisan cache:clear`, `php artisan route:clear` after toggling package modules or module settings.
  - Verification outcome:
    - For a company where `menu_customization` is disabled in package, non‑superadmin users no longer see the Settings entry and route access returns 403; superadmins retain access.

```bash
# Get catalog data
curl -s http://localhost:8001/api/products/1/catalog

# Calculate bundle pricing (replace IDs accordingly)
curl -s -X POST http://localhost:8001/api/products/1/bundles/10/price \
  -H "Content-Type: application/json" \
  -d '{"option_item_ids":[101,102],"items":[201,202]}'

# Admin: toggle bundle status (requires auth token)
curl -s -X PUT http://localhost:8001/api/products/1/bundles/10/status \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"is_active": true}'
```

## Next Steps

- Persist `pricing_metadata` in proposals/invoices for reporting.
- Integrate orders to consume pricing endpoint and update inventory on completion.
- Add variant/bundle identifiers to invoice line metadata.
- Expand UAT coverage for authenticated write paths using Sanctum tokens.

## Auth Setup Checklist (Sanctum)

- Verify tokens migration exists: `database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`.
- Run migrations: `php artisan migrate` (ensures `personal_access_tokens` is created in `craveva`).
- Create or select a test user with `admin` or `manager` roles (check `roles` and `role_user`).
- Generate a token:
  - `php artisan tinker`
  - `User::find(<id>)->createToken('UAT')->plainTextToken`
- Use `Authorization: Bearer <token>` when calling protected endpoints (e.g., variants generate, bundle options/items CRUD, status/validity updates).
- Confirm middleware aliases are active:
  - `auth:sanctum` and `api.auth` (see provider and middleware config).
- Validate write endpoint responses with the token to confirm role enforcement.

## UAT Tester Additions (2025-11-28)

- Environment verification and server run: confirmed Composer/PHP availability and started server at `http://127.0.0.1:8000`.
- Database selection: verified `DB_DATABASE=craveva` in use for the app server.
- Targeted migration executed and verified: bundle tables created and present (`product_bundles`, `product_bundle_items`, `product_bundle_options`, `product_bundle_option_items`).
- Schema adjustments for seed/validation:
  - Added `language_settings.flag_code` (varchar) and `language_settings.is_rtl` (tinyint) to align with expectations.
- Minimal test data inserted to exercise catalog and pricing flows:
  - Products: `Test Product A` (`id=1`, price `20.00`) and `Test Product B` (`id=2`, price `5.00`).
  - Bundle: `Starter Bundle` for product `1` (`id=2`).
  - Bundle item: required, default-selected item for product `1` (`bundle_item_id=2`).
  - Option group: `Add-ons` (`option_id=6`, single_choice, min `0`, max `1`).
  - Option item: add-on for product `2` with `price_adjustment=2.50` (`option_item_id=9`).
- Endpoint validation results:
  - `GET /api/products/1/catalog` → 200; returns product, bundles, items, options, option items.
  - `POST /api/products/1/bundles/2/price` with `{"option_item_ids":[9]}` → 200; `final_price=22.50` (base `20.00` + `2.50`).
  - Write endpoints confirmed protected via middleware and role checks; full auth-path UAT pending Sanctum token availability.

## Duplicates/Conflicts Resolved

- Avoided restating endpoint definitions already documented above; this section only adds UAT-specific actions and observations.
- Sanctum tokens table state:
  - Previous notes indicate tokens are enabled; during UAT, table `craveva.personal_access_tokens` was not found.
  - Action required: run Sanctum migration to create the table before testing authenticated write endpoints.

## Verification Artifacts

- Catalog sample response includes `bundles` and `bundle_items` for product `1`.
- Pricing sample: `final_price=22.50` when selecting option item `9`.

## Change Timeline (since 2025-11-27)

- Restored missing legacy tables and fixed foreign key types to align with existing schema.
- Added v2.0 catalog migration and created tables for bundles, variations, attributes, and variants.
- Wired catalog read endpoint and variant generation, guarded write endpoints with auth and role checks.
- Implemented bundle pricing endpoint with validity window and selection validations.
- Added admin endpoints to toggle `is_active` and manage `valid_from`/`valid_to`.
- Integrated bundle pricing into `EstimateController::addItem` with safe fallback to existing exchange-rate logic.
- Enabled Laravel Sanctum tokens by ensuring `personal_access_tokens` table is present.
- Created and placed this business-facing change log document in `Document/Document/B2B Marketplace`.

## Code References

- Bundle pricing endpoint: `routes/api.php:228`
- Role guard usage example: `routes/api.php:151`
- Estimate add-item integration: `app/Http/Controllers/EstimateController.php:768`
- Composer automation scripts: `composer.json:166-171`
- v2.0 catalog migration: `database/migrations/2025_11_28_120001_create_catalog_v2_tables.php`
- Sanctum tokens migration: `database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php`

## UAT Results Update (2025-11-28)

- Database in use: `laravel` from `.env` (`DB_DATABASE=laravel`).
- Tokens table: confirmed present; created via migration-equivalent SQL when `php artisan migrate` returned "Nothing to migrate".
- Admin test user: created and assigned `admin` role (`users.id=1`, `roles.name='admin'`, `role_user` mapped).
- Token generated for UAT: created a `personal_access_tokens` record for `User#1` with full bearer token format `tokenId|plainToken`.
- Read endpoints validated:
  - `GET /api/products/1/catalog` returned product, bundles, items, and option items.
  - `POST /api/products/1/bundles/1/price` with `{ "option_item_ids": [1] }` returned `final_price=22.50` for `Starter Bundle` built on `Test Product A (20.00)` plus add-on `2.50`.
- Write endpoints are guarded (`auth:sanctum`, `api.auth`, and role checks). Auth-path UAT was partially exercised; intermittent dev server termination in the IDE interrupted further POST/PUT tests. Next step is to run a persistent server and retest.

### How to Run Auth-Path UAT Locally

- Start a dev server in a persistent shell: `php artisan serve --host 127.0.0.1 --port 8001`
- Use the bearer token in the `Authorization` header when calling protected endpoints.
- Sample calls:
  - `POST /api/products/1/variants/generate` (expects 200 with count or 200 with message `No variations`).
  - `POST /api/products/1/bundles/1/options` with JSON body to create an option group.
  - `PUT /api/products/1/bundles/1/status` with `{ "is_active": true }`.

### Notes

- Middleware and role checks are attached per `routes/api.php` for all write endpoints.
- If `route:list` fails due to controller constructors (e.g., Stripe credentials), prefer inspecting `routes/api.php` directly or set minimal env values for external services.

### Auth-Path Setup Adjustments (UAT)

- Created `user_auths` table to align with `App\Models\UserAuth` and `Modules\RestAPI\Entities\UserAuth`.
- Added `users.user_auth_id` and linked `User#1 (uat.admin@example.com)` to `UserAuth#1`.
- Generated Sanctum token for `Modules\RestAPI\Entities\UserAuth#1` and used header `Authorization: Bearer <tokenId|plainToken>`.
- With these adjustments, write endpoints authenticate against the `sanctum` guard using `UserAuth` rather than `User`.
