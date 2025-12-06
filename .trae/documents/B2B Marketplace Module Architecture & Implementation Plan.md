## Goals
- Create a self-contained `Marketplace` Laravel module (`alias: b2b_marketplace`) following existing module patterns.
- Enable Superadmin to install/uninstall/enable via package inclusion and auto ModuleSwitch detection.
- Integrate Python AI service (OpenRouter) for supplier matching, pricing, and risk scoring with clean HTTP contracts.

## Conventions & Constraints
- Follow existing module conventions used by `Modules/Purchase` (providers, routes, migrations, commands).
- Respect Email Notification Standards: use `MailMessage->line()->action()`, public links via `getDomainSpecificUrl()`, sender from `smtp_settings`, minimal HTML, queues `sync` locally.
- No code in `public/`; compile assets via Laravel Mix.

## Module Structure
- `Modules/Marketplace/`
  - `module.json` with `active:true`, `alias:"b2b_marketplace"`, providers `[MarketplaceServiceProvider, RouteServiceProvider]` and helper `files`.
  - `Providers/MarketplaceServiceProvider.php`: merges config, registers views/translations, loads migrations, registers `marketplace:activate`.
  - `Providers/RouteServiceProvider.php`: map `web.php` (under `auth` + `prefix:'account'`) and `api.php`.
  - `Routes/web.php`, `Routes/api.php`.
  - `Database/Migrations/*`.
  - `Config/*.php` including `xss_ignore.php`.
  - `Http/Controllers/*`, `Policies/*`, `Events/*`, `Notifications/*`.

## Domain Model (Initial)
- `companies` (reuse existing `Company`).
- Catalog: `products`, `product_variants`, `product_attributes`.
- Parties: `suppliers`, `buyers` (link to `Company`).
- Market Ops:
  - `rfqs` (Request for Quote), `rfq_items`
  - `quotes`, `quote_items`, `negotiations`
  - `orders`, `order_items`, `shipments`
  - `pricing_rules`, `tier_prices`
- AI:
  - `ai_recommendation_jobs` (async records for supplier match / pricing)
  - `ai_logs` (request/response audit; no secrets stored)

## Routes & Controllers
- `web.php` (`auth`, `prefix:'account'`):
  - RFQ CRUD (`RfqController`), Quote workflow (`QuoteController`), Order management (`OrderController`).
  - Catalog management (`CatalogController`).
  - Supplier directory (`SupplierController`), Buyer settings (`BuyerController`).
- `api.php`: REST endpoints for frontend widgets or external integrations.

## Permissions & Policies
- Seed CRUD permissions for RFQs, Quotes, Orders, Catalog, Suppliers.
- Implement `Policies/*` to restrict supplier/buyer actions to company context.
- Integrate with `PermissionRole::insertModuleRolePermission(...)` similarly to `Modules\Purchase`.

## Superadmin Integration
- Module detection via `module.json` → auto ModuleSwitch seeder picks it up.
- Package binding: add `"b2b_marketplace"` into `package->module_in_package` to grant access.
- `ModuleSetting` entries for `admin`, `employee`, `client` per company with `status:'active'` and `is_allowed` based on package inclusion (`app/Models/ModuleSetting.php`).
- Artisan: `marketplace:activate` (mirrors `purchase:activate`) to add module settings and permissions for all companies.

## AI Integration (Python/OpenRouter)
- Laravel emits events (e.g., `RFQCreated`, `QuoteSubmitted`) → small HTTP client service posts signed JSON to Python endpoints.
- Contracts:
  - `POST /ai/recommend/suppliers`: `{rfq_id, company_id, items:[{product_id, qty}], constraints}` → `{ranked_suppliers:[{supplier_id, score}], rationale}`
  - `POST /ai/recommend/pricing`: `{company_id, items:[...], market_signals}` → `{price_suggestions:[{product_id, unit_price}], rationale}`
- Feature flags in config to enable/disable AI calls; fallback to deterministic rules when disabled.

## Notifications
- Use `MailMessage` with `line()` and `action()` for RFQ/Quote events; derive links via `getDomainSpecificUrl()`; ensure `APP_URL` is set.

## Implementation Steps
1. Scaffold `Marketplace` module (providers, routes, config, views, migrations).
2. Add `module.json` with `alias:"b2b_marketplace"`, `active:true`, providers.
3. Implement `MarketplaceServiceProvider` (config merge, views/translations, migrations, command registration).
4. Implement `RouteServiceProvider` and base routes.
5. Create initial migrations for RFQ/Quotes/Orders/Catalog.
6. Seed permissions and write activation command `marketplace:activate`.
7. Add `Policies` for core entities and wire authorization middleware.
8. Implement event dispatching + AI HTTP client stubs with signed payloads.
9. Add notifications following Email standards.
10. Prepare basic Blade views for Admin flows and minimal React/JS widgets if needed using Mix.

## Validation
- `php artisan module:list` shows `Marketplace` active.
- Run `marketplace:activate` → `ModuleSetting` created per company; permissions seeded.
- Navigate `account/marketplace/*` pages; complete RFQ→Quote→Order happy path.
- AI calls logged; feature flag toggles verified.

## Risks & Mitigations
- Data sprawl: keep clear boundaries and migrations versioned.
- Email deliverability: use `queue.default=sync` locally; verify links are public.
- Security: never log secrets; validate signed payloads on Python side.

Confirm and I will implement the scaffold, settings/permissions wiring, migrations, routes, and AI stubs according to your codebase patterns.