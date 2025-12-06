# Master Technical Documentation

This master document consolidates the core technology stack, architecture, module system, business logic, directory structure, and operational standards of the application. It includes the SuperAdmin Module Switch architecture and all surrounding systems it interacts with.

## Tech Stack

- Backend: Laravel `^10.0` (`composer.json:25`), PHP `8.2.12` (`composer.json:141`) with Composer packages for payments, notifications, backups, and modularity.
- Frontend: Blade views, Bootstrap `^4.3.1` and icons, SweetAlert2, Quill editor, Frappe charts (`package.json:28–45`).
- Build tooling: Laravel Mix `^6` on Webpack 5, Sass (`package.json:20–25`).
- Real‑time: Pusher + Laravel Echo (`package.json:36–39`).
- Testing: PHPUnit `^10.1` (`composer.json:155`), Playwright E2E (`package.json:11–14`) with reports in `playwright-report/`.
- Dev tools: Laravel Boost (`composer.json:149`), IDE helper, Larastan static analysis, Pint formatter.

## Architecture Overview

- SaaS multi‑tenant design with Super Admin governance and company workspaces.
- Modular features via `nwidart/laravel-modules` (`composer.json:63`), each with its own `Config`, `Providers`, `Routes`, and optional `start.php`.
- Route segmentation orchestrated by `app/Providers/RouteServiceProvider.php`:
  - Default home: `'/account/dashboard'` (`app/Providers/RouteServiceProvider.php:21`).
  - Super Admin home: `'/account/super-admin-dashboard'` (`app/Providers/RouteServiceProvider.php:23`).
  - Maps `web`, `api`, `web-public`, `web-settings`, and Super Admin routes (`app/Providers/RouteServiceProvider.php:68–137`).
- Middleware layers (`app/Http/Kernel.php`) include XSS and CORS protections, auth, `super-admin`, `multi-company-select`, `check-company-package` (`app/Http/Kernel.php:41–84`).
- Public endpoints support external payments and signed links (`routes/web-public.php:81–147`).

## Module System

- Module manifest: `Modules/<Module>/module.json` defines `name`, `alias`, `providers`, `files`, and `active` flag (e.g., `Modules/QRCode/module.json:1–21`).
- Module registration & routing via per‑module `Providers/RouteServiceProvider.php` (e.g., `Modules/RestAPI/Providers/RouteServiceProvider.php`).
- SuperAdmin Module Switch controls global enable/disable and syncs with filesystem:
  - Routes: `routes/SuperAdmin/web.php:161–165`.
  - Controller actions: `index` (`app/Http/Controllers/SuperAdmin/ModuleSwitchController.php:28–42`), `syncModulesWithDatabase` (`:47–75`), `toggleStatus` (`:80–100`), `bulkToggle` (`:105–131`), `show` (`:136–159`).
  - Data model: `app/Models/ModuleSwitch.php:13–21`, migration `database/migrations/2025_10_02_125603_create_module_switches_table.php:10–27`.
  - Cache invalidation: `clearUserModulesCache` (`app/Http/Controllers/SuperAdmin/ModuleSwitchController.php:197–208`).

### Visibility & Permissions

- Per‑role feature visibility resolved by `user_modules()` (`app/Helper/start.php:328–379`).
  - Includes modules with `ModuleSetting` `is_allowed=1` and `status='active'` for the user’s role (`app/Helper/start.php:348–358`).
  - Excludes any module globally disabled by Module Switch (`app/Helper/start.php:367–373`).
  - Result cached under `user_modules_<id>` (`app/Helper/start.php:376`).
- Permissions are defined in `app/Models/Module.php` and mapped to roles; controllers and views gate access via `user()->permission(...)` and `in_array('<module>', user_modules())`.

## Business Logic

- Roles: Admin, Employee, Client with route and view gating (`app/Http/Kernel.php:64–84`).
- Packages: Super Admin assigns packages; package modules propagate to `ModuleSetting` records for company and roles.
- Module lifecycle for company visibility:
  1. Module exists under `Modules/` with `module.json`.
  2. Super Admin toggles global enable/disable in Module Switch (`routes/SuperAdmin/web.php:161–165`).
  3. Company package includes module; observers create `ModuleSetting` entries.
  4. `user_modules()` returns enabled modules; menus and routes render accordingly.

## Directory Structure (Summary)

- `app/Http` — 572 files, controllers/middleware, ~2.64 MB.
- `app/Models` — 309 files, core Eloquent models, ~1.03 MB.
- `resources/views` — 1373 files, Blade views, ~9.7 MB.
- `resources/lang` — 348 files, translations, ~10.55 MB.
- `Modules` — 10k+ files across feature modules; largest include `Craveva` (~22.49 MB) and `LanguagePack` (~13.51 MB).
- `public/landing` — static landing site (~1390.11 MB); `public/media` (~177.85 MB); `public/user-uploads` (~145.49 MB).
- `vendor` — Composer packages (~344.68 MB); `node_modules` — frontend packages (~196.26 MB).

Detailed per‑subfolder counts are available in the inventory summary and can be refreshed on demand.

## Routing

- Main app routes: `routes/web.php`.
- Settings routes: `routes/web-settings.php`.
- Public routes: `routes/web-public.php` for payments and signed links.
- Super Admin routes: `routes/SuperAdmin/web.php` (dashboard, front settings, AI settings, Module Switch, global payments).

## Config & Providers

- Modular configuration via `config/modules.php` (generator paths, stubs, activators).
- Application providers: `config/app.php:204–234`.
- Payments: Stripe/PayPal/Paystack/Razorpay/Mollie/Flutterwave/AuthorizeNet/Square configured under `config/*`.
- Backups: Spatie backup (`composer.json:117`).

## Caching & State

- Settings caches: `global_setting()` (`app/Helper/start.php:199–209`), `push_setting()` (`:215–222`), language caches (`:229–248`).
- Themes: `superadmin_theme()` (`app/Helper/start.php:135–141`), `admin_theme()` (`:147–158`) and equivalents for employee/client.
- Plugins: `craveva_plugins()` caches enabled module list (`app/Helper/start.php:385–394`).

## Testing & QA

- Unit/Feature: PHPUnit (`phpunit.xml`), Larastan (`composer.json:151`), Pint (`composer.json:152`).
- E2E: Playwright commands in `package.json:11–14`, reports in `playwright-report/`.
- Post‑update script migrates DB and regenerates IDE metadata (`composer.json:162–169`).

## Dev Tools & IDE Integration

- Laravel Boost installed and MCP server configured for Cursor (`.cursor/mcp.json`, `Document/Document/Others/LARAVEL_BOOST_INSTALLATION_STATUS.md`).
- IDE helper, debugbar available in dev for introspection.

## Security & Compliance

- Global middleware: XSS protection (`packages/craveva/craveva/src/Middleware/XSS.php`, registered in `app/Http/Kernel.php:31`), CORS (`config/cors.php`).
- CSRF protection on `web` routes, auth guards via Sanctum/Fortify.
- No secrets committed; environment driven via `.env`.

## Deployment & Environments

- `public/index.php` enforces PHP `>= 8.2.0` and `.env` presence (`public/index.php:5–20`).
- Windows developer setup documented (`Document/Document/Others/SETUP_INSTRUCTIONS.md`).
- Vercel project configuration present (`.vercel/project.json`) for static assets.

## SuperAdmin Module Switch (Details)

- Purpose: central control to enable/disable modules globally, synchronized to each module’s `module.json` for consistency.
- Routes: `routes/SuperAdmin/web.php:161–165`.
- Controller: `app/Http/Controllers/SuperAdmin/ModuleSwitchController.php`:
  - `index` initializes view and syncs DB with filesystem (`:28–42`).
  - `syncModulesWithDatabase` reads `Modules/*/module.json` and upserts `module_switches` (`:47–75`).
  - `toggleStatus` flips `is_enabled`, writes `active` to `module.json`, clears caches (`:80–100`).
  - `bulkToggle` toggles all modules consistently (`:105–131`).
  - `show` merges filesystem metadata into a response with directory size (`:136–159`).
  - Cache clearing for all users and plugin list (`:197–208`).
- Model: `app/Models/ModuleSwitch.php:13–21` with helpers (`:35–71`).
- UI: DataTable index and details modal under `resources/views/superadmin/settings/module-switch/`.

## Module Development Guidelines

- Create new features as modules under `Modules/<Name>` with `Providers/RouteServiceProvider.php`, `Routes/web.php`, `Routes/api.php`, `module.json`, `version.txt`, and optional `start.php`.
- Respect middleware and role gating; guard controllers with `abort_403(!in_array('<module>', user_modules()))` and specific permission checks.
- Keep `module.json.active` consistent with `ModuleSwitch`.
- Provide tests (unit/feature/E2E), translation strings, and menu entries gated by `user_modules()`.
- Version modules semantically; add `laraupdater.json` where applicable for upgrade flows.

## Key Tables (Selected)

- `module_switches` — global module enablement (`database/migrations/2025_10_02_125603_create_module_switches_table.php:10–27`).
- `modules` — catalog/permissions (see `app/Models/Module.php`).
- `module_settings` — per‑company/role module access (queried by `user_modules()`).
- `permissions`, `roles`, `permission_roles`, `user_permissions` — granular access control.

## Quick References

- RouteServiceProvider: `app/Providers/RouteServiceProvider.php:21–24`, `68–137`.
- Super Admin routes: `routes/SuperAdmin/web.php:66–197`.
- Module Switch controller: `app/Http/Controllers/SuperAdmin/ModuleSwitchController.php:28–208`.
- User modules resolver: `app/Helper/start.php:328–379`.
- Build tooling: `package.json:3–15`, `20–25`.
- Composer config/platform: `composer.json:141`, packages: `composer.json:21–137`.
