# UI/UX Technical Documentation

This study documents the end‑to‑end UI/UX system of the authenticated app (`/account/*`), covering structure, components, style tokens, behaviors, and patterns used across admin, employee, client, and superadmin panels. It is intended for designers planning new features and redesigns.

## Information Architecture

- Entry & routing
  - Post‑login landing: `app/Providers/RouteServiceProvider.php:21` → `'/account/dashboard'`
  - Advanced dashboard: `routes/web.php:176` → `'/account/dashboard-advanced'`
  - Superadmin dashboard: `routes/SuperAdmin/web.php:66–69` → `'/account/super-admin-dashboard'`
- Panels
  - Admin advanced (tabbed dashboards): `resources/views/dashboard/admin.blade.php`
  - Employee: `resources/views/dashboard/employee/index.blade.php`
  - Client: `resources/views/dashboard/client/index.blade.php`
  - Superadmin: `resources/views/super-admin/dashboard/index.blade.php`
- Navigation
  - Topbar: admin/client `resources/views/sections/topbar.blade.php`; superadmin `resources/views/super-admin/sections/topbar.blade.php` (included via layout condition at `resources/views/layouts/app.blade.php:162–166`)
  - Sidebar: `resources/views/sections/sidebar.blade.php` (included at `resources/views/layouts/app.blade.php:169`)
  - Body container: `resources/views/layouts/app.blade.php:171–191`

## Layout & Grid

- Global layout: `resources/views/layouts/app.blade.php`
  - Wrapper `.body-wrapper` → `.main-container` section (`resources/views/layouts/app.blade.php:171–191`)
  - Dashboard content area is scoped by `.admin-dashboard` (inner region swapped by AJAX), `resources/views/dashboard/admin.blade.php:154–156`
  - Tabs are displayed in the header region `#filter-section`, `resources/views/dashboard/admin.blade.php:39–76`
- Responsive behavior
  - Relies on Bootstrap grid classes throughout (e.g., `row`, `col-*` widely used in dashboard partials like `resources/views/dashboard/ajax/overview.blade.php:3–85`)

## Design Tokens & Themes

- Theme variable: `--header_color` set according to app/company/global settings (`resources/views/sections/theme_css.blade.php:6–7`)
- Applied to key UI elements: buttons, active tabs, pagination, badges (`resources/views/sections/theme_css.blade.php:9–75`)
- Dark theme: `body.dark-theme` toggled via `user()->dark_theme` (`resources/views/layouts/app.blade.php:155`)
- Custom overrides: optional `public/css/app-custom.css`, `public/css/custom-css/theme-custom.css` auto‑included if present (`resources/views/layouts/app.blade.php:127–133`)

## Core CSS & Assets

- App stylesheet: `public/css/main.css` linked by layout (`resources/views/layouts/app.blade.php:37`)
  - Includes Quill editor theme and base content styles (`public/css/main.css:1–120`)
- Vendor CSS: Font Awesome, Simple Line Icons, Datepicker, Timepicker, Select2, Bootstrap Icons (`resources/views/layouts/app.blade.php:16–33`)
- Charts: Frappe charts included per view (e.g., overview `resources/views/dashboard/ajax/overview.blade.php:1`)

## Iconography

- Font Awesome (`resources/views/layouts/app.blade.php:16`) and Bootstrap Icons (`resources/views/layouts/app.blade.php:32`) are primary icon sets
- Simple Line Icons used in some menus (`resources/views/layouts/app.blade.php:20`)

## Components (Blade)

- Tabs: `x-tab` for dashboard tab navigation (`resources/views/dashboard/admin.blade.php:48–76`)
- Cards & Widgets: `x-cards.widget` metric tiles (`resources/views/dashboard/ajax/overview.blade.php:6–11`, `16–21`, etc.)
- Data containers: `x-cards.data` with embedded charts (`resources/views/dashboard/ajax/overview.blade.php:90–101`)
- Tables: `x-table` (`resources/views/dashboard/ajax/overview.blade.php:111–163`)
- Charts: `x-bar-chart`, `x-line-chart` (`resources/views/dashboard/ajax/overview.blade.php:92–101`)
- Forms & Buttons: `x-form`, `x-forms.button-primary`, `x-forms.button-secondary` (`resources/views/dashboard/admin.blade.php:95–137`)

## Patterns & Interactions

- Tabbed dashboards (admin)
  - Tabs link to `?tab=overview|project|client|hr|ticket|finance` with permission gating (`resources/views/dashboard/admin.blade.php:48–76`)
  - AJAX navigation replaces `.admin-dashboard` via `$.easyAjax` (`resources/views/dashboard/admin.blade.php:190–227`)
- Date range filters
  - Daterangepicker attached to inputs (`resources/views/dashboard/admin.blade.php:160–178`)
  - Locale and presets provided in layout (`resources/views/layouts/app.blade.php:235–255`)
- Modals
  - Standard modal IDs: `#myModal`, `#myModalXl`, etc. (`resources/views/layouts/app.blade.php:205–213`)
  - Asynchronous content loading via `$.ajaxModal` (`resources/views/layouts/app.blade.php:344–347`, `351–355`)
- AJAX helpers
  - `$.easyAjax` used widely for CRUD and content swaps (e.g., dashboard tabs `resources/views/dashboard/admin.blade.php:211–226`)
- Notifications
  - Mark‑as‑read via AJAX (`resources/views/layouts/app.blade.php:328–340`)

## Forms & Inputs

- Select2 (`resources/views/layouts/app.blade.php:29`) and Bootstrap Select (`resources/views/layouts/app.blade.php:199–204` overrides)
- Datepicker (`resources/views/layouts/app.blade.php:23`, config at `resources/views/layouts/app.blade.php:218–234`)
- Dropzone: global configuration set in layout (`resources/views/layouts/app.blade.php:267–277`)
- Quill editor: initialized via helper functions (layout scripts `resources/views/layouts/app.blade.php:381–521`)

## Data Tables

- Yajra DataTables for listing modules/settings (e.g., SuperAdmin ModuleSwitch) with custom switches and actions
  - Controller: `app/Http/Controllers/SuperAdmin/ModuleSwitchController.php:28–42`
  - View: `resources/views/superadmin/settings/module-switch/index.blade.php:79–86`
  - JS integration: `resources/views/superadmin/settings/module-switch/index.blade.php:92–111`

## Role & Permission Gating

- Tabs appear based on `user()->permission('view_*_dashboard') == 'all'` and installed modules (`resources/views/dashboard/admin.blade.php:28–34`, `48–76`)
- Module availability per user computed by `user_modules()` which filters by `ModuleSetting` and `ModuleSwitch` (`app/Helper/start.php:328–379`)
- Superadmin theme and restrictions: `superadmin_theme()->restrict_admin_theme_change` affect admin/employee/client themes (`app/Helper/start.php:144–176`, `178–192`)

## Dashboards (Data Sources)

- Overview (admin)
  - Data builder: `app/Traits/OverviewDashboard.php:29–134`
  - Charts: earnings (`app/Traits/OverviewDashboard.php:141–211`), timelogs (`app/Traits/OverviewDashboard.php:218–234`)
  - View: `resources/views/dashboard/ajax/overview.blade.php`
- Other tabs follow similar trait → `$this->view` → partial patterns (project/client/hr/ticket/finance)

## Accessibility & i18n

- Text uses localization helpers `__()` / `@lang` throughout (e.g., `resources/views/dashboard/ajax/overview.blade.php:7–9`)
- Keyboard/accessibility specifics are primarily inherited from vendors (Select2, Datepicker, Bootstrap)
- Designer guidance: ensure contrast meets WCAG for light/dark themes; maintain focus states for interactive elements

## Visual Style Summary

- Typography: System defaults and preloaded Helvetica Neue for app (`resources/views/layouts/app.blade.php:11–14`)
- Color system: primary derived from `--header_color` with light/dark variants (see Theme CSS)
- Spacing: Bootstrap spacing utilities in views (e.g., `px-4`, `py-0`, `mb-3`) and custom utility classes
- Cards: flat blocks with icon + metric for KPIs; data cards contain charts and tables

## Charts

- Overview loads Frappe charts (`resources/views/dashboard/ajax/overview.blade.php:1`)
- Chart components `x-bar-chart`, `x-line-chart` consume controller‑provided data (labels/series/colors)
- Full‑screen pie chart modal pattern in layout (`resources/views/layouts/app.blade.php:349–355`)

## SuperAdmin UI (Modules)

- Module Switch settings: index/table and detail modal (`resources/views/superadmin/settings/module-switch/*`)
- Controller toggles also update `module.json.active` and clear caches (`app/Http/Controllers/SuperAdmin/ModuleSwitchController.php:80–100`, `197–208`)

## Designer Implementation Guidance

- Respect tab/permission gating when proposing new dashboards
- Design widgets to fit `x-cards.widget` footprint (icon, label, value, optional info)
- Provide chart designs compatible with Frappe/Chart.js (simple series, clear legends)
- Maintain layout consistency: header filters → tabbed nav → content block `.admin-dashboard`
- Plan for date range filter presence and responsiveness (mobile vs desktop tabs widths `resources/views/dashboard/admin.blade.php:14–22`)
- Icon usage: prefer Font Awesome/Bootstrap Icons for consistency
- Color: derive primaries from theme variable; avoid hardcoding brand colors unless part of theme overrides

## Dev Hand‑Off Checklist

1) Identify target panel and tab; confirm permissions and module gating
2) Map data sources (trait/controller) and variables exposed to blade
3) Design components using existing blade components where possible
4) Define chart data format; prefer reusable chart components
5) Provide responsive specs for grid columns (xl/lg/md/sm) and spacing utilities
6) Specify accessible states (focus/hover/active) for interactive elements
7) Prepare i18n microcopy; avoid embedding literals in components
8) If introducing new module features, coordinate with SuperAdmin Module Switch and `user_modules()` visibility rules

## References

- Layout CSS include: `resources/views/layouts/app.blade.php:37`
- Theme CSS include: `resources/views/layouts/app.blade.php:122–125`; source `resources/views/sections/theme_css.blade.php:1–75`
- Global JS include: `resources/views/layouts/app.blade.php:196`; daterange config `resources/views/layouts/app.blade.php:235–255`
- Admin dashboard shell & interactions: `resources/views/dashboard/admin.blade.php:48–76`, `154–156`, `160–178`, `190–227`
- Overview partial: `resources/views/dashboard/ajax/overview.blade.php`
- Visibility resolver: `app/Helper/start.php:328–379`

