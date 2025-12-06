<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Product;

class PanelRedesignPublicController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()->orderBy('category_name')->get();

        $data = [];
        foreach ($categories as $cat) {
            $subs = ProductSubCategory::query()
                ->where('category_id', $cat->id)
                ->orderBy('category_name')
                ->get();

            $subData = [];
            foreach ($subs as $sub) {
                $items = Product::query()
                    ->where('category_id', $cat->id)
                    ->where('sub_category_id', $sub->id)
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get();

                $subData[] = [
                    'id' => $sub->id,
                    'name' => $sub->category_name,
                    'items' => $items,
                ];
            }

            $data[] = [
                'id' => $cat->id,
                'name' => $cat->category_name,
                'subs' => $subData,
            ];
        }

        return view('public.prototype.panelredesign', [
            'categories' => $data,
            'pageTitle' => 'Panel Redesign Public Preview',
        ]);
    }
}

