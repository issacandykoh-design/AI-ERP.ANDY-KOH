# Menu Customization — Feature Brief

## Purpose

- Allow Company Admins to rename menu items to match their business terminology.
- Renaming is cosmetic and does not change permissions or route access.

## Scope

- Supported: rename menu labels displayed in the sidebar and settings.
- Optional (implementation present): menu order and visibility toggles per item.
- Not included: adding/removing routes, changing icons, creating new sections.

## Who Can Rename

- Company Admins with `manage_menu_customization` permission.
- Access is available only if the company’s package includes `menu_customization` (or the module is globally enabled).
- Superadmins always have access.

## Behavior & Constraints

- Renaming affects UI labels only; underlying routes, permissions, and modules remain unchanged.
- Authorization is enforced at the controller level; menu visibility does not grant access.
- Caching may delay label updates until cleared.

## Implementation Summary

- Data model: `custom_menu_settings` stores per-company `custom_name`, `menu_order`, `is_visible`.
- Discovery: menu keys are detected from sidebar templates for consistent targeting.
- Update flow:
  - Read and merge existing settings for the company.
  - Save new `custom_name` values per menu key.
  - Clear relevant caches so changes reflect immediately.

## How To Use

- Navigate to Settings → Menu Customization.
- For each menu item, enter a custom label to suit your business.
- Save changes; refresh if necessary after cache clear.

## Technical References

- Merge `custom_name` into discovered items: `app/Http/Controllers/MenuCustomizationController.php:63-66`.
- Batch save `custom_name` per menu key: `app/Http/Controllers/MenuCustomizationController.php:117-143`.
- Visibility in Settings based on package and role: `resources/views/components/setting-sidebar.blade.php:107-115`.
- Module-driven menu discovery: `app/Helper/MenuDiscovery.php:251`.

## Operational Notes

- After changing package modules, clear caches:
  - `php artisan view:clear`
  - `php artisan cache:clear`
  - `php artisan route:clear`

## Future Enhancements (Optional)

- Admin-defined ordering presets.
- Per-role label overrides.
- Inline preview with draft/publish.
