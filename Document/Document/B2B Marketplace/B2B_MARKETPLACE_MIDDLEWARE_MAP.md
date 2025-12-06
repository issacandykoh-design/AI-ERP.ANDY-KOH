# B2B Marketplace — Middleware Map

## Purpose
- Define middleware stacks and route grouping for marketplace reads, admin mutations, and company panel pages, using existing patterns.

## Route Groups
- Public Catalog API (no auth)
  - `GET /api/products/{id}/catalog` — serves product, bundles, options, variants, attributes. Source: `routes/api.php:60-128`.
  - `POST /api/products/{id}/bundles/{bundleId}/price` — interactive bundle pricing with validation. Source: `routes/api.php:228-383`.
  - Middleware: `api`. Optional rate limit policy `throttle:marketplace` can be applied later.

- Protected Admin API (auth required)
  - `POST /api/products/{id}/variants/generate` — admin/manager only. Source: `routes/api.php:227` and role check at `routes/api.php:152-155`.
  - Bundle/option writes follow the same pattern. Example: create/update options with role gate. Source: `routes/api.php:394-413`.
  - Middleware stack: `auth:sanctum`, `api.auth` (see `Modules/RestAPI/Routes/api.php:26-104` and `packages/open-rest-api/src/Middleware/ApiAuthMiddleware.php:12-34`).

- Company Panel Web Routes (authenticated UI)
  - Group: `Route::group(['middleware' => 'auth', 'prefix' => 'account'], ...)` pattern. Example reference: `Modules/RestAPI/Routes/web.php:20-30`, `Modules/Letter/Routes/web.php:15-32`.
  - Pages: `/account/marketplace` (directory), `/account/marketplace/orders`, `/account/marketplace/settings`.
  - Controllers enforce permissions (admin/manager for settings), and may check module visibility.

## Module Gating & Visibility
- Menu inclusion is driven by enabled modules: `resources/views/sections/menu.blade.php:237-239` includes `::sections.sidebar` for each enabled module.
- Enabled modules list: `craveva_plugins()` at `app/Helper/start.php:382-395`.
- Role-and-module gating: `user_modules()` at `app/Helper/start.php:328-379` decides if a user should see module pages.
- Route/controller guard for module state: `module_enabled('B2BMarketplace')` at `app/Helper/start.php:609-627`.

## Security & RBAC
- API authentication: `auth:sanctum` + `api.auth` for protected endpoints. Pattern used in `Modules/ServerManager/Routes/api.php:7-28` and `Modules/Payroll/Routes/api.php:5-10`.
- Optional token/user status guard: `Modules/RestAPI/Http/Middleware/AuthMiddleware.php:12-29`.
- Inline role checks for admin-only actions: example at `routes/api.php:152-155`.

## RouteServiceProvider Patterns
- Attach `api` and `web` groups via the module service provider. Examples:
  - `Modules/RestAPI/Providers/RouteServiceProvider.php:38-60`.
  - `Modules/Purchase/Providers/RouteServiceProvider.php:37-57`.

## Notes
- Public marketplace browsing stays under `api` group without auth; keep rate limits moderate.
- Checkout and order placement must use authenticated routes (`web` + `auth` or `auth:sanctum` + `api.auth`).
- Reuse Catalog v2 endpoints for all pricing and data reads; avoid duplicating logic in the module.

## Phase 1 Route Table

- `GET /api/products/{id}/catalog`
  - Middleware: `api`
  - Audience: public (read-only)
  - Reference: `routes/api.php:60-128`

- `POST /api/products/{id}/bundles/{bundleId}/price`
  - Middleware: `api`
  - Audience: public (read-only pricing calculation)
  - Reference: `routes/api.php:228-383`

- `POST /api/products/{id}/variants/generate`
  - Middleware: `auth:sanctum`, `api.auth`
  - Audience: admin/manager only (write)
  - Reference: `routes/api.php:227`, role gate `routes/api.php:152-155`

- `GET /account/marketplace`
  - Middleware: `web`, `auth`
  - Audience: company users
  - Reference: pattern `Modules/RestAPI/Routes/web.php:20-30`

- `GET /account/marketplace/orders`
  - Middleware: `web`, `auth`
  - Audience: company users
  - Reference: pattern `Modules/Letter/Routes/web.php:15-32`

- `GET|PUT /account/marketplace/settings`
  - Middleware: `web`, `auth`
  - Audience: admin/manager only
  - Gate: enforce via controller role checks and `module_enabled('B2BMarketplace')`
