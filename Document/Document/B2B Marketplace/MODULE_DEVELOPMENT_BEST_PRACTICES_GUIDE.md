# Module Development Guide — Best Practices

## Executive Summary

This guide standardizes how to design, build, list, bill, secure, and operate new modules within the B2B marketplace. It encodes decisions from pricing, marketplace, menu customization, delivery, and AI credits to ensure consistency, performance, and compliance.

## Lifecycle Overview

- Ideation → Manifest & schema → Services & APIs → UI & menus → Pricing & subscriptions → Payments & webhooks → AI credits (if applicable) → Analytics & logging → Tests → Rollout with feature flags.

## Prerequisites & Governance

- Ownership: Assign a module owner and SLAs.
- Scope: Define feature set, dependencies, pricing type (free/paid/subscription), and target roles.
- Compliance: Confirm data isolation, PCI-compliant flows, audit logging.
- Feature flags: Gate new modules for staged rollout and safe rollback.

## Module Manifest (Marketplace Listing)

- Fields: `module_key`, `name`, `description`, `version`, `author`, `pricing_type`, `price_monthly`, `price_annual`, `price_lifetime`, `category`, `tags`, `icon`, `screenshots`, `documentation_url`, `support_url`, `is_featured`, `is_active`, `dependencies[]`.
- Rules: Unique `module_key`; semantic versioning; clear screenshots and support links; categories aligned to `module_categories`.

## Data Model & Migrations

- Create/extend tables:
  - `marketplace_modules` (listing metadata)
  - `module_categories` (taxonomy)
  - `module_dependencies` (required/optional links)
  - `company_module_subscriptions` (tenant subscriptions)
  - `module_subscription_items` (gateway items)
  - `module_purchases` (lifetime buys)
- If AI features: include links to `company_ai_credits`, `ai_credit_transactions`, `ai_usage_logs`, summaries.
- Indexes: company_id, marketplace_module_id, status, created_at.

## Services

- `ModuleMarketplaceService`: listing, search, categories, dependencies.
- `ModuleAddonService`: add/remove module to package with proration.
- `SubscriptionService`: subscription lifecycle (activate/renew/cancel/expire).
- `PaymentService`: gateway abstraction; create subscription items; handle proration and refunds.
- If AI: `AICreditsService`, `AIUsageTrackingService`, `AIRateLimit` checks.

## Controllers & Routes

- Controllers: `ModuleMarketplaceController`, `ModuleAddonController`, `PaymentController`, `InvoiceController` updates.
- API: `/api/marketplace/modules`, `/api/modules/subscriptions`, `/api/billing/*` updates, AI endpoints if applicable.
- Versioning: Introduce new endpoints versioned; maintain backward-compatible package APIs during transition.

### Craveva Integration Pointers

- Menu injection follows existing sidebar include:
  - Company: `resources/views/sections/menu.blade.php:237-239` includes `::sections.sidebar` for enabled modules returned by `craveva_plugins()` (`app/Helper/start.php:382-395`).
  - Superadmin: `resources/views/super-admin/sections/super-admin-menu.blade.php:53-57` includes module entries.
- Visibility and permissions:
  - `user_modules()` (`app/Helper/start.php:328-379`) gates module visibility per role/type.
  - Controllers can block access when disabled via `module_enabled('<ModuleName>')` (`app/Helper/start.php:609-627`).
- Route providers:
  - Use module `RouteServiceProvider` to attach `web` and `api` groups (see `Modules/RestAPI/Providers/RouteServiceProvider.php:38-60`, `Modules/Purchase/Providers/RouteServiceProvider.php:37-57`).

## UI Integration & Menus

- Menu entries: Ensure “Marketplace”, “My Modules”, and module-specific entries integrate with MenuRenderer.
- Badges: Show subscription status, expiring flags, and AI credit balance badges where relevant.
- Responsive layouts: Sidebar/topbar/mobile parity; preview and draft flows for menu changes.

### Company Panel Menu Example

- File placement: module view `resources/views/modules/b2b-marketplace/sections/sidebar.blade.php`.
- Inclusion: auto-included by `resources/views/sections/menu.blade.php:237-239` when the module is enabled.
- Entry examples:
  - `Marketplace` → `/account/marketplace` (visible when `module_enabled('B2BMarketplace')` and user in `user_modules()`).
  - `Marketplace Orders` → `/account/marketplace/orders` (company users; admin-only actions gated in controllers).
  - `Marketplace Settings` → `/account/marketplace/settings` (admin/manager only).

## Pricing & Subscriptions

- Precedence: base price → public price → tier pricing → client-specific pricing → volume discounts.
- Visibility: Decide public vs private tier display; client-specific pricing via Client module assignments.
- Bundles: Optional bundle discounts for multi-module add-ons; encode eligibility in rules.
- Subscription types: monthly, annual, lifetime; auto-renew; trial optional.
- Proration: Calculate mid-cycle add/remove adjustments.

## Payments & Webhooks

- Gateways: Stripe/PayPal/etc. with subscription items per module; lifetime one-time purchases.
- Webhooks: Subscribe to invoice/payment events; update `company_module_subscriptions` status; handle retries and idempotency.
- Invoices: Unified invoice line items (Package + Module Add-ons + AI Credits), proration details, promotions shown.

## AI Credits Integration (If Applicable)

- Balance: Check `company_ai_credits` before AI calls.
- Costs: Model-based and content-type multipliers (text/image/video/music).
- Deduction: Record `ai_credit_transactions`; real-time UI updates.
- Promotions: Apply bonus credits on purchase validating eligibility and caps.
- Exhaustion: Block calls; show purchase prompt; log exhaustion; optional grace/queue.

## Usage Tracking & Analytics

- Logs: `module_usage_tracking`, `ai_usage_logs` (module, content type, model, credits used).
- Summaries: `ai_usage_summary`, `ai_usage_by_module`, `ai_usage_by_content_type`.
- Dashboards: Profile usage charts, exports (CSV/Excel/PDF), period filters.
- Experiments: Track CTR/dwell from menus; A/B item placements.

## Permissions & Security

- Route guards: Enforce role and permission checks regardless of menu visibility.
- Subscription checks: Block access when not subscribed; support grace period policies.
- Audit logs: Record subscription changes, credit transactions, menu changes.
- Tenancy: Strict isolation of company data; validate company_id on all reads/writes.

## Localization & Branding

- Labels: Provide translation keys; per-tenant label packs with fallbacks.
- Currency: Per-module pricing display respecting locale.
- RTL: Validate UI in RTL locales if applicable.

## Performance & Caching

- Cache: listings, module details, credit packages, subscription status.
- Index hot queries; eager-load relationships; paginate listings.
- Background jobs: installation tasks, renewal reminders, credit expiration checks, usage summaries.

## Testing Requirements

- Unit: pricing rules, proration, permission checks, dependency resolution.
- Feature: subscribe/unsubscribe/renew; payment flows; menu visibility; AI deduction and exhaustion.
- Integration: gateway webhooks; unified invoice generation; multi-tenant isolation.
- Performance: listing load, subscription creation, deduction throughput.

## Rollout & Migration

- Migrations: Create tables, add indexes, seed categories and baseline modules.
- Feature flags: Progressive enablement; staged tenant rollout; rollback plan.
- Documentation: Publish module user guide and support runbooks.

## Developer Checklist

- Define manifest and categories; register in `marketplace_modules`.
- Implement services, controllers, routes; add permission/role checks.
- Add pricing rules, subscription lifecycle, proration.
- Integrate payments, webhooks, and unified invoices.
- Integrate menus with badges and conditional visibility.
- If AI: implement balance checks, deduction, logging, rate limits, exhaustion handling.
- Add analytics and dashboards; wire experiments.
- Write tests; add feature flags; prepare migration scripts; document.

## Reference Snippets

- Subscription check

```
if (!SubscriptionService::hasActive($companyId, $moduleKey)) {
    return redirect('/marketplace')->with('error', 'Module subscription required');
}
```

- AI usage logging

```
AIUsageTrackingService::log([
  'company_id' => $companyId,
  'user_id' => $userId,
  'module_name' => $moduleKey,
  'content_type' => 'text',
  'model_used' => $model,
  'credits_used' => $credits,
]);
```

- Menu badge example

```
MenuRenderer::badge('AI Credits', CreditsService::balance($companyId));
```

---

This guide aligns new modules to marketplace, billing, menu customization, delivery, and AI credits policies for consistent, secure, and scalable implementations.
