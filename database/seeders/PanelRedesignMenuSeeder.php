<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Menu;
use App\Models\CustomMenuSetting;

class PanelRedesignMenuSeeder extends Seeder
{
    public function run(): void
    {
        $isDev = app()->environment(['local','development','testing']);

        // Seed navigation entry for Dashboard only (sample page)
        $this->seedDashboardMenu();

        // Seed hierarchical categories and sample products for dashboard widgets
        $this->seedProductHierarchyAndItems($isDev);
    }

    protected function seedDashboardMenu(): void
    {
        // Create or update dashboard menu entry
        $menu = Menu::query()->firstOrCreate([
            'menu_name' => 'Dashboard',
        ], [
            'translate_name' => 'Dashboard',
            'route' => 'dashboard',
            'module' => null,
            'icon' => 'bi-speedometer2',
            'setting_menu' => 0,
        ]);

        // Optional customization for order/visibility (company-agnostic demo)
        $companyId = optional(company())->id; // uses global helper if user has company
        if ($companyId) {
            CustomMenuSetting::query()->updateOrCreate([
                'company_id' => $companyId,
                'menu_key' => 'dashboard',
            ], [
                'custom_name' => 'Dashboard',
                'menu_order' => 1,
                'is_visible' => true,
                'locale' => app()->getLocale(),
            ]);
            CustomMenuSetting::clearMenuCache($companyId, 'dashboard');
        }
    }

    protected function seedProductHierarchyAndItems(bool $isDev): void
    {
        // Categories → Subcategories (acts as parent-child hierarchy)
        $categories = [
            'Starters' => ['Salads', 'Soups', 'Small Plates'],
            'Mains' => ['Pasta', 'Grill', 'Chef Specials'],
            'Desserts' => ['Cakes', 'Ice Cream', 'Pastry'],
            'Beverages' => ['Coffee', 'Tea', 'Juices'],
        ];

        $categoryIds = [];
        $subCategoryIds = [];

        foreach ($categories as $catName => $subs) {
            $category = ProductCategory::query()->firstOrCreate([
                'category_name' => $catName,
            ]);
            $categoryIds[$catName] = $category->id;

            foreach ($subs as $sub) {
                $subCat = ProductSubCategory::query()->firstOrCreate([
                    'category_id' => $category->id,
                    'category_name' => $sub,
                ], [
                    'company_id' => optional(company())->id,
                ]);
                $subCategoryIds[$catName][$sub] = $subCat->id;
            }
        }

        // Sample products for dashboard tiles/cards
        $items = [
            [
                'name' => 'Caesar Salad',
                'description' => 'Romaine, parmesan, croutons, creamy dressing',
                'price' => '8.90',
                'category' => 'Starters',
                'sub' => 'Salads',
                'status' => 'active',
                'type' => 'goods',
            ],
            [
                'name' => 'Tomato Basil Soup',
                'description' => 'Slow-cooked tomatoes, basil, cream',
                'price' => '6.50',
                'category' => 'Starters',
                'sub' => 'Soups',
                'status' => 'active',
                'type' => 'goods',
            ],
            [
                'name' => 'Grilled Chicken',
                'description' => 'Free-range chicken, herb butter, seasonal sides',
                'price' => '16.00',
                'category' => 'Mains',
                'sub' => 'Grill',
                'status' => 'active',
                'type' => 'goods',
            ],
            [
                'name' => 'Chef Special Pasta',
                'description' => 'Daily selection with handcrafted sauce',
                'price' => '14.50',
                'category' => 'Mains',
                'sub' => 'Pasta',
                'status' => 'active',
                'type' => 'goods',
            ],
            [
                'name' => 'Chocolate Lava Cake',
                'description' => 'Warm chocolate cake with molten center',
                'price' => '7.80',
                'category' => 'Desserts',
                'sub' => 'Cakes',
                'status' => 'active',
                'type' => 'goods',
            ],
            [
                'name' => 'Iced Latte',
                'description' => 'Double shot espresso over ice, milk',
                'price' => '4.90',
                'category' => 'Beverages',
                'sub' => 'Coffee',
                'status' => 'active',
                'type' => 'goods',
            ],
        ];

        foreach ($items as $item) {
            $categoryId = $categoryIds[$item['category']] ?? null;
            $subId = $subCategoryIds[$item['category']][$item['sub']] ?? null;

            // Basic integrity validation
            if (!$categoryId || !$subId) {
                continue;
            }

            $existing = Product::query()
                ->where('name', $item['name'])
                ->where('category_id', $categoryId)
                ->where('sub_category_id', $subId)
                ->first();

            if ($existing) {
                $existing->description = $item['description'];
                $existing->price = $item['price'];
                $existing->type = $item['type'];
                $existing->status = $item['status'];
                $existing->save();
                continue;
            }

            Product::query()->create([
                'company_id' => optional(company())->id,
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => $item['price'],
                'category_id' => $categoryId,
                'sub_category_id' => $subId,
                'type' => $item['type'],
                'status' => $item['status'],
                'sku' => Str::slug($item['name']),
                'allow_purchase' => false,
                'downloadable' => false,
            ]);
        }
    }
}

