# Panel Redesign Menu Seeder (Dashboard Sample)

Purpose
- Populate realistic, hierarchical menu data for the dashboard sample page only, using existing schema.
- Keep production-safe: idempotent, works in `local` and `testing` environments; no global overrides.

Data model mapping
- Navigation: `menus` + optional `custom_menu_settings` (order/visibility/name) → creates a single Dashboard entry.
- Hierarchy: `product_category` (parent) → `product_sub_category` (child).
- Items: `products` with `name`, `description`, `price`, `category_id`, `sub_category_id`, `type`, `status`, `sku`.

Seeder class
- `database/seeders/PanelRedesignMenuSeeder.php`
- Idempotent: uses `firstOrCreate`/`updateOrCreate` to avoid duplicates.
- Environments: runs the same way in `local`/`testing`; skips company-specific customization if no `company()`.

Run commands
1. Seed only this module:
   - `php artisan db:seed --class=Database\Seeders\PanelRedesignMenuSeeder`
2. Optional: clear view/cache if UI relies on cached data:
   - `php artisan optimize:clear`

Updating the seed data
- Edit arrays in `seedProductHierarchyAndItems()` to add categories/subcategories/items.
- Fields to keep consistent:
  - name (string), description (string), price (string), category (string), sub (string), status (`active`/`inactive`), type (`goods`/`service`).
- Use realistic values for dashboard metrics and cards.

Validation & integrity
- Category and subcategory must exist before product creation.
- Products are matched by `name + category_id + sub_category_id` to prevent duplicates.
- Basic value checks are applied; invalid entries are skipped.

Testing notes
- Safe to run repeatedly in `local` and `testing`.
- Verify the dashboard widgets/cards render seeded products and categories.

Scope
- Affects only dashboard-related sample data; does not modify other modules.

