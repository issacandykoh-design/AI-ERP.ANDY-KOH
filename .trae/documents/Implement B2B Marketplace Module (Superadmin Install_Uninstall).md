## Objectives
- Build an AI-ready B2B Marketplace as a standalone Laravel Module (nwidart/laravel-modules).
- Enable Superadmin to install/uninstall/enable via package and ModuleSwitch integration.
- Keep strict boundaries: Laravel handles UX, data, and permissions; Python AI service (OpenRouter) handles recommendations and pricing.

## Architecture Overview
- **Module Name/Alias**: `Marketplace` / `b2b_marketplace`
- **Domain Areas**:
  - Catalog (Products, Variants, Attributes)
  - Suppliers & Buyers (Company profiles, verification)
  - RFQ & Quotes (request → quote → negotiation → finalize)
  - Orders (order, order items, status, fulfillment)
  - Pricing (rules, tiers, AI recommendations)
  - Payments (gateway integration reuse; settlement records)
  - Logistics (shipping options, tracking refs)
  - Analytics (AI insights: demand, price elasticity, fraud scoring)
- **Laravel Responsibilities**:
  - Controllers, routes (`web`, `api`), migrations, policies, events
  - Superadmin toggling via `module.json` + package `module_in_package`
  - Notifications using `MailMessage` per project standards
- **Python/AI Responsibilities**:
  - Recommend suppliers/products, optimal pricing, fraud risk
  - Expose HTTP endpoints; Laravel calls with signed payloads
  - Use OpenRouter LLMs via existing `.env` settings

## Data Contract (High-Level)
- **RFQ**: `{rfq_id, buyer_company_id, items:[{product_id, qty}], due_at, notes}`
- **Quote**: `{quote_id, rfq_id, supplier_company_id, items:[{product_id, qty, unit_price}], terms}`
- **RecommendationRequest**: `{context:"pricing"|"matching", company_id, items:[...], market_signals:{...}}`
- **RecommendationResponse**: `{ranked_options:[{supplier_id, score, price_suggestion}], rationale}`

## Superadmin Integration
- **Module Detection**: `module.json` with `active:true` → auto-detected by ModuleSwitch seeder.
- **Package Binding**: Add `"b2b_marketplace"` to `package->module_in_package` to grant access.
- **ModuleSetting**: Create entries for roles (`admin`, `employee`, `client`) per company; status aligned with package inclusion.
- **Artisan Commands**: `marketplace:activate` to add ModuleSetting and seed permissions; optional `marketplace:deactivate`.

## Implementation Steps
1. **Scaffold**: `php artisan module:make Marketplace` (aliases, providers, routes, config, resources).
2. **module.json**: Define `alias:b2b_marketplace`, `active:true`, `providers:[MarketplaceServiceProvider, RouteServiceProvider]`.
3. **Providers**: Mirror `Modules/Purchase` patterns: config merge, views publish, migrations load, commands register.
4. **Routes**:
   - `web.php`: under `auth` + `prefix:'account'`; pages for RFQ, Quotes, Orders, Catalog.
   - `api.php`: REST endpoints for marketplace operations.
5. **Migrations**: `suppliers`, `buyers`, `products`, `product_variants`, `rfqs`, `quotes`, `quote_items`, `orders`, `order_items`, `negotiations`, `pricing_rules`.
6. **Policies/Permissions**: Integrate with `PermissionRole`; seed CRUD permissions for module entities.
7. **ModuleSetting Integration**: Add `MarketplaceSetting::addModuleSetting($company)` and wire `marketplace:activate` similar to Purchase.
8. **Events→AI**: Emit domain events (e.g., `RFQCreated`, `QuoteSubmitted`) to a small Laravel service that posts to Python AI endpoints.
9. **Notifications**: Use `MailMessage->line()->action()`; derive public URLs via `getDomainSpecificUrl()`; sender from `smtp_settings`.
10. **Config**: Environment flags: enable/disable AI calls; default to safe fallbacks.

## Deliverables
- Marketplace module skeleton with providers, routes, migrations, config.
- Superadmin install/uninstall through package/module switches and artisan.
- AI integration stubs calling the Python service with signed requests.

## Validation Plan
- `php artisan module:list` shows `Marketplace` active.
- Run `marketplace:activate` → ModuleSetting created per company; permissions seeded.
- Access `account/marketplace/*` pages; create RFQ→Quote→Order flow; AI call logs present.

## Notes & Risks
- Follow Laravel Mix for assets; no edits under `public/` directly.
- Use queues set to `sync` locally to validate delivery.
- Avoid logging secrets; ensure `APP_URL` set; use `getDomainSpecificUrl()` for links.

Confirm to proceed and I will implement the module scaffold, settings/permissions wiring, and initial migrations following your codebase conventions.