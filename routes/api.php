<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

ApiRoute::group(['namespace' => 'App\Http\Controllers', 'prefix' => ''], function () {
    ApiRoute::get('purchased-module', ['as' => 'api.purchasedModule', 'uses' => 'HomeController@installedModule']);
    
    // Knowledge Base Auto Process API Routes
    ApiRoute::group(['prefix' => 'knowledge-base'], function () {
        // 处理单个文件
        ApiRoute::post('process-file', ['as' => 'api.kb.processFile', 'uses' => 'KnowledgeBaseAutoProcessController@processFile']);
        
        // 批量处理所有待处理文件
        ApiRoute::post('process-all-pending', ['as' => 'api.kb.processAllPending', 'uses' => 'KnowledgeBaseAutoProcessController@processAllPending']);
        
        // 获取处理状态
        ApiRoute::get('processing-status', ['as' => 'api.kb.getProcessingStatus', 'uses' => 'KnowledgeBaseAutoProcessController@getProcessingStatus']);
        
        // 获取待处理文件列表
        ApiRoute::get('pending-files', ['as' => 'api.kb.getPendingFiles', 'uses' => 'KnowledgeBaseAutoProcessController@getPendingFiles']);
        
        // 重新处理文件
        ApiRoute::post('reprocess-file', ['as' => 'api.kb.reprocessFile', 'uses' => 'KnowledgeBaseAutoProcessController@reprocessFile']);
        
        // 批量处理进度跟踪
        ApiRoute::get('batch-progress', ['as' => 'api.kb.getBatchProgress', 'uses' => 'KnowledgeBaseAutoProcessController@getBatchProgress']);
        
        // 获取所有活跃批量处理
        ApiRoute::get('active-batches', ['as' => 'api.kb.getAllActiveBatches', 'uses' => 'KnowledgeBaseAutoProcessController@getAllActiveBatches']);
        
        // 停止批量处理
        ApiRoute::post('stop-batch', ['as' => 'api.kb.stopBatch', 'uses' => 'KnowledgeBaseAutoProcessController@stopBatch']);
        
        // 获取批量处理统计
        ApiRoute::get('batch-statistics', ['as' => 'api.kb.getBatchStatistics', 'uses' => 'KnowledgeBaseAutoProcessController@getBatchStatistics']);
        
        // 清理已完成的批量处理记录
        ApiRoute::post('cleanup-completed-batches', ['as' => 'api.kb.cleanupCompletedBatches', 'uses' => 'KnowledgeBaseAutoProcessController@cleanupCompletedBatches']);
        
        // 监控仪表板路由
        ApiRoute::get('monitor/dashboard', ['as' => 'api.kb.monitor.dashboard', 'uses' => 'KnowledgeBaseMonitorController@dashboard']);
        ApiRoute::get('monitor/statistics', ['as' => 'api.kb.monitor.statistics', 'uses' => 'KnowledgeBaseMonitorController@getProcessingStatistics']);
        ApiRoute::get('monitor/recent-files', ['as' => 'api.kb.monitor.recentFiles', 'uses' => 'KnowledgeBaseMonitorController@getRecentProcessedFiles']);
        ApiRoute::get('monitor/failed-files', ['as' => 'api.kb.monitor.failedFiles', 'uses' => 'KnowledgeBaseMonitorController@getFailedFiles']);
        ApiRoute::get('monitor/trends/{days?}', ['as' => 'api.kb.monitor.trends', 'uses' => 'KnowledgeBaseMonitorController@getProcessingTrends']);
        ApiRoute::post('monitor/reset-failed', ['as' => 'api.kb.monitor.resetFailed', 'uses' => 'KnowledgeBaseMonitorController@resetFailedFiles']);
        ApiRoute::get('monitor/file-details/{fileId}', ['as' => 'api.kb.monitor.fileDetails', 'uses' => 'KnowledgeBaseMonitorController@getFileDetails']);
    });

    ApiRoute::group(['prefix' => 'products'], function () {
        ApiRoute::get('{id}/catalog', function ($id) {
            $product = \App\Models\Product::findOrFail($id);

            $bundles = \Illuminate\Support\Facades\DB::table('product_bundles')
                ->where('product_id', $product->id)
                ->where('is_active', 1)
                ->orderBy('id')
                ->get();

            $bundleIds = $bundles->pluck('id')->all();

            $bundleItems = \Illuminate\Support\Facades\DB::table('product_bundle_items')
                ->whereIn('bundle_id', $bundleIds)
                ->orderBy('display_order')
                ->get();

            $bundleOptions = \Illuminate\Support\Facades\DB::table('product_bundle_options')
                ->whereIn('bundle_id', $bundleIds)
                ->orderBy('display_order')
                ->get();

            $optionIds = $bundleOptions->pluck('id')->all();
            $bundleOptionItems = \Illuminate\Support\Facades\DB::table('product_bundle_option_items')
                ->whereIn('option_id', $optionIds)
                ->orderBy('display_order')
                ->get();

            $variations = \Illuminate\Support\Facades\DB::table('product_variations')
                ->where('product_id', $product->id)
                ->orderBy('display_order')
                ->get();

            $variationIds = $variations->pluck('id')->all();
            $variationOptions = \Illuminate\Support\Facades\DB::table('product_variation_options')
                ->whereIn('variation_id', $variationIds)
                ->orderBy('display_order')
                ->get();

            $variants = \Illuminate\Support\Facades\DB::table('product_variants')
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->get();

            $attributeValues = \Illuminate\Support\Facades\DB::table('product_attribute_values')
                ->join('product_attributes', 'product_attribute_values.attribute_id', '=', 'product_attributes.id')
                ->where('product_attribute_values.product_id', $product->id)
                ->select('product_attributes.attribute_name', 'product_attributes.attribute_type', 'product_attribute_values.attribute_value')
                ->orderBy('product_attributes.display_order')
                ->get();

            return response()->json([
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'image' => $product->image_url,
                ],
                'bundles' => $bundles,
                'bundle_items' => $bundleItems,
                'bundle_options' => $bundleOptions,
                'bundle_option_items' => $bundleOptionItems,
                'variations' => $variations,
                'variation_options' => $variationOptions,
                'variants' => $variants,
                'attributes' => $attributeValues,
            ]);
        });

        ApiRoute::get('{id}/variants', function ($id) {
            \App\Models\Product::findOrFail($id);
            $variants = \Illuminate\Support\Facades\DB::table('product_variants')
                ->where('product_id', $id)
                ->orderBy('id')
                ->get();
            return response()->json($variants);
        });

        ApiRoute::get('{id}/attributes', function ($id) {
            \App\Models\Product::findOrFail($id);
            $attributeValues = \Illuminate\Support\Facades\DB::table('product_attribute_values')
                ->join('product_attributes', 'product_attribute_values.attribute_id', '=', 'product_attributes.id')
                ->where('product_attribute_values.product_id', $id)
                ->select('product_attributes.attribute_name', 'product_attributes.attribute_type', 'product_attribute_values.attribute_value')
                ->orderBy('product_attributes.display_order')
                ->get();
            return response()->json($attributeValues);
        });

        ApiRoute::post('{id}/variants/generate', function ($id) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $product = \App\Models\Product::findOrFail($id);

            $variations = \Illuminate\Support\Facades\DB::table('product_variations')
                ->where('product_id', $product->id)
                ->orderBy('display_order')
                ->get();

            if ($variations->isEmpty()) {
                return response()->json(['created' => 0, 'message' => 'No variations'], 200);
            }

            $optionsByVariation = [];
            foreach ($variations as $var) {
                $opts = \Illuminate\Support\Facades\DB::table('product_variation_options')
                    ->where('variation_id', $var->id)
                    ->orderBy('display_order')
                    ->get();
                if ($opts->isEmpty()) {
                    return response()->json(['created' => 0, 'message' => 'Variation has no options'], 200);
                }
                $optionsByVariation[] = $opts->all();
            }

            $combinations = [[]];
            foreach ($optionsByVariation as $opts) {
                $newComb = [];
                foreach ($combinations as $comb) {
                    foreach ($opts as $opt) {
                        $comb2 = $comb;
                        $comb2[] = $opt;
                        $newComb[] = $comb2;
                    }
                }
                $combinations = $newComb;
            }

            $created = 0;
            foreach ($combinations as $combo) {
                $suffixParts = [];
                $priceAdj = 0.0;
                $variantData = [];
                foreach ($combo as $opt) {
                    $suffixParts[] = $opt->sku_suffix ?: $opt->option_name;
                    $priceAdj += (float) $opt->price_adjustment;
                    $variantData[] = ['variation_id' => $opt->variation_id, 'option_id' => $opt->id, 'name' => $opt->option_name];
                }

                $baseSku = $product->sku ?: ('P' . $product->id);
                $variantSku = $baseSku . '-' . implode('-', $suffixParts);
                $exists = \Illuminate\Support\Facades\DB::table('product_variants')
                    ->where('variant_sku', $variantSku)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $finalPrice = (float) $product->price + $priceAdj;
                \Illuminate\Support\Facades\DB::table('product_variants')->insert([
                    'product_id' => $product->id,
                    'variant_sku' => $variantSku,
                    'variant_name' => implode(' / ', array_column($variantData, 'name')),
                    'price' => $finalPrice,
                    'stock_quantity' => null,
                    'is_active' => 1,
                    'variant_data' => json_encode($variantData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }

            return response()->json(['created' => $created], 200);
        })->middleware(['auth:sanctum', 'api.auth']);
        ApiRoute::post('{id}/bundles/{bundleId}/price', function ($id, $bundleId) {
            $product = \App\Models\Product::findOrFail($id);

            $bundle = \Illuminate\Support\Facades\DB::table('product_bundles')
                ->where('id', $bundleId)
                ->where('product_id', $product->id)
                ->where('is_active', 1)
                ->first();

            if (!$bundle) {
                return response()->json(['error' => 'Bundle not found'], 404);
            }

            $now = now();
            $validFrom = $bundle->valid_from ? \Carbon\Carbon::parse($bundle->valid_from) : null;
            $validTo = $bundle->valid_to ? \Carbon\Carbon::parse($bundle->valid_to) : null;
            if (($validFrom && $now->lt($validFrom)) || ($validTo && $now->gt($validTo))) {
                return response()->json(['error' => 'Bundle not valid'], 422);
            }

            $itemsInput = (array) request()->input('items', []);
            $optionItemIds = (array) request()->input('option_item_ids', []);

            $itemsQuery = \Illuminate\Support\Facades\DB::table('product_bundle_items')
                ->where('bundle_id', $bundle->id);

            if (!empty($itemsInput)) {
                $itemsQuery->whereIn('id', $itemsInput);
            } else {
                $itemsQuery->where('default_selected', 1);
            }

            $selectedItems = $itemsQuery->orderBy('display_order')->get();

            $selectedCount = $selectedItems->count();
            if ($bundle->min_items_required && $selectedCount < $bundle->min_items_required) {
                return response()->json(['error' => 'Minimum items not met'], 422);
            }
            if ($bundle->max_items_allowed && $selectedCount > $bundle->max_items_allowed) {
                return response()->json(['error' => 'Maximum items exceeded'], 422);
            }

            $itemSubtotal = 0.0;
            $itemBreakdown = [];
            if ($selectedCount > 0) {
                $productIds = $selectedItems->pluck('product_id')->all();
                $pricesByProduct = \Illuminate\Support\Facades\DB::table('products')
                    ->whereIn('id', $productIds)
                    ->select('id', 'price', 'name')
                    ->get()
                    ->keyBy('id');

                foreach ($selectedItems as $it) {
                    $p = $pricesByProduct->get($it->product_id);
                    $qty = (int) ($it->quantity ?: 1);
                    $price = $p ? (float) $p->price : 0.0;
                    $lineTotal = $price * $qty;
                    $itemSubtotal += $lineTotal;
                    $itemBreakdown[] = [
                        'bundle_item_id' => $it->id,
                        'product_id' => $it->product_id,
                        'name' => $p ? $p->name : null,
                        'unit_price' => $price,
                        'quantity' => $qty,
                        'line_total' => $lineTotal,
                    ];
                }
            }

            $optionsSubtotal = 0.0;
            $optionBreakdown = [];
            if (!empty($optionItemIds)) {
                $optionItems = \Illuminate\Support\Facades\DB::table('product_bundle_option_items')
                    ->whereIn('id', $optionItemIds)
                    ->get();
                if (count($optionItemIds) !== $optionItems->count()) {
                    return response()->json(['error' => 'Unknown option_item_id provided'], 422);
                }

                $optionIds = $optionItems->pluck('option_id')->all();
                $selectedGroups = \Illuminate\Support\Facades\DB::table('product_bundle_options')
                    ->whereIn('id', $optionIds)
                    ->where('bundle_id', $bundle->id)
                    ->get()
                    ->keyBy('id');

                foreach ($optionItems as $oi) {
                    if (!$selectedGroups->has($oi->option_id)) {
                        return response()->json(['error' => 'Option item does not belong to bundle', 'option_item_id' => $oi->id], 422);
                    }
                }

                $groupSelections = [];
                foreach ($optionItems as $oi) {
                    $adj = (float) $oi->price_adjustment;
                    $optionsSubtotal += $adj;
                    $opt = $selectedGroups->get($oi->option_id);
                    $groupId = $opt ? $opt->id : null;
                    if ($groupId) {
                        $groupSelections[$groupId] = isset($groupSelections[$groupId]) ? $groupSelections[$groupId] + 1 : 1;
                    }
                    $optionBreakdown[] = [
                        'option_item_id' => $oi->id,
                        'product_id' => $oi->product_id,
                        'price_adjustment' => $adj,
                        'option_id' => $oi->option_id,
                    ];
                }
            }

            $allGroups = \Illuminate\Support\Facades\DB::table('product_bundle_options')
                ->where('bundle_id', $bundle->id)
                ->get();
            foreach ($allGroups as $opt) {
                $count = isset($groupSelections[$opt->id]) ? $groupSelections[$opt->id] : 0;
                if ($opt->min_selections && $count < $opt->min_selections) {
                    return response()->json(['error' => 'Option group minimum not met', 'group' => $opt->id], 422);
                }
                if ($opt->max_selections && $count > $opt->max_selections) {
                    return response()->json(['error' => 'Option group maximum exceeded', 'group' => $opt->id], 422);
                }
            }

            $baseSubtotal = $bundle->bundle_price !== null ? (float) $bundle->bundle_price : $itemSubtotal;
            $subtotal = $baseSubtotal + $optionsSubtotal;

            $discountType = $bundle->bundle_discount_type;
            $discountValue = $bundle->bundle_discount_value !== null ? (float) $bundle->bundle_discount_value : null;
            $discountAmount = 0.0;
            $finalPrice = $subtotal;

            if ($discountType === 'percentage' && $discountValue !== null) {
                $discountAmount = $subtotal * ($discountValue / 100.0);
                $finalPrice = max($subtotal - $discountAmount, 0.0);
            } elseif ($discountType === 'fixed_amount' && $discountValue !== null) {
                $discountAmount = $discountValue;
                $finalPrice = max($subtotal - $discountAmount, 0.0);
            } elseif ($discountType === 'override_price' && $discountValue !== null) {
                $finalPrice = max($discountValue, 0.0);
            }

            return response()->json([
                'bundle_id' => $bundle->id,
                'product_id' => $product->id,
                'bundle_type' => $bundle->bundle_type,
                'base_subtotal' => $baseSubtotal,
                'items_subtotal' => $itemSubtotal,
                'options_subtotal' => $optionsSubtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice,
                'items' => $itemBreakdown,
                'options' => $optionBreakdown,
            ]);
        });

        ApiRoute::get('{id}/bundles/{bundleId}/options', function ($id, $bundleId) {
            \App\Models\Product::findOrFail($id);
            $options = \Illuminate\Support\Facades\DB::table('product_bundle_options')
                ->where('bundle_id', $bundleId)
                ->orderBy('display_order')
                ->get();
            return response()->json($options);
        });

        ApiRoute::post('{id}/bundles/{bundleId}/options', function ($id, $bundleId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            $bundle = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            if (!$bundle) { return response()->json(['error' => 'Bundle not found'], 404); }
            $validated = request()->validate([
                'option_group_name' => 'required|string|max:255',
                'option_type' => 'required|in:single_choice,multiple_choice',
                'min_selections' => 'nullable|integer|min:0',
                'max_selections' => 'nullable|integer|min:0',
                'display_order' => 'nullable|integer',
            ]);
            if (($validated['min_selections'] ?? null) !== null && ($validated['max_selections'] ?? null) !== null && $validated['min_selections'] > $validated['max_selections']) {
                return response()->json(['error' => 'min_selections must be <= max_selections'], 422);
            }
            if ($validated['option_type'] === 'single_choice') {
                $validated['min_selections'] = isset($validated['min_selections']) ? min((int)$validated['min_selections'], 1) : 0;
                $validated['max_selections'] = 1;
            }
            $validated['bundle_id'] = $bundleId;
            $validated['created_at'] = now();
            $validated['updated_at'] = now();
            $idNew = \Illuminate\Support\Facades\DB::table('product_bundle_options')->insertGetId($validated);
            $opt = \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $idNew)->first();
            return response()->json($opt, 201);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::put('{id}/bundles/{bundleId}/options/{optionId}', function ($id, $bundleId, $optionId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            $opt = \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $optionId)->where('bundle_id', $bundleId)->first();
            if (!$opt) { return response()->json(['error' => 'Option not found'], 404); }
            $validated = request()->validate([
                'option_group_name' => 'sometimes|string|max:255',
                'option_type' => 'sometimes|in:single_choice,multiple_choice',
                'min_selections' => 'sometimes|integer|min:0|nullable',
                'max_selections' => 'sometimes|integer|min:0|nullable',
                'display_order' => 'sometimes|integer|nullable',
            ]);
            if (array_key_exists('min_selections', $validated) && array_key_exists('max_selections', $validated) && $validated['min_selections'] !== null && $validated['max_selections'] !== null && $validated['min_selections'] > $validated['max_selections']) {
                return response()->json(['error' => 'min_selections must be <= max_selections'], 422);
            }
            if (isset($validated['option_type']) && $validated['option_type'] === 'single_choice') {
                $validated['min_selections'] = isset($validated['min_selections']) ? min((int)$validated['min_selections'], 1) : ($opt->min_selections ?? 0);
                $validated['max_selections'] = 1;
            }
            $validated['updated_at'] = now();
            \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $optionId)->update($validated);
            $opt2 = \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $optionId)->first();
            return response()->json($opt2);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::delete('{id}/bundles/{bundleId}/options/{optionId}', function ($id, $bundleId, $optionId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['deleted' => false, 'error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            $opt = \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $optionId)->where('bundle_id', $bundleId)->first();
            if (!$opt) { return response()->json(['deleted' => false], 200); }
            \Illuminate\Support\Facades\DB::table('product_bundle_option_items')->where('option_id', $optionId)->delete();
            \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $optionId)->delete();
            return response()->json(['deleted' => true], 200);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::get('{id}/bundles/{bundleId}/options/{optionId}/items', function ($id, $bundleId, $optionId) {
            \App\Models\Product::findOrFail($id);
            $items = \Illuminate\Support\Facades\DB::table('product_bundle_option_items')
                ->where('option_id', $optionId)
                ->orderBy('display_order')
                ->get();
            return response()->json($items);
        });

        ApiRoute::post('{id}/bundles/{bundleId}/options/{optionId}/items', function ($id, $bundleId, $optionId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            $opt = \Illuminate\Support\Facades\DB::table('product_bundle_options')->where('id', $optionId)->where('bundle_id', $bundleId)->first();
            if (!$opt) { return response()->json(['error' => 'Option not found'], 404); }
            $validated = request()->validate([
                'product_id' => 'required|integer',
                'price_adjustment' => 'required|numeric',
                'is_default' => 'nullable|boolean',
                'display_order' => 'nullable|integer',
            ]);
            $productExists = \Illuminate\Support\Facades\DB::table('products')->where('id', $validated['product_id'])->exists();
            if (!$productExists) { return response()->json(['error' => 'Product not found'], 422); }
            $bundle = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            $bundleCompanyId = $bundle ? $bundle->company_id : null;
            if ($bundle && $bundleCompanyId === null) {
                $baseProd = \Illuminate\Support\Facades\DB::table('products')->select('company_id')->where('id', $bundle->product_id)->first();
                $bundleCompanyId = $baseProd ? $baseProd->company_id : null;
            }
            if ($bundleCompanyId !== null) {
                $prodCompany = \Illuminate\Support\Facades\DB::table('products')->select('company_id')->where('id', $validated['product_id'])->first();
                if ($prodCompany && $prodCompany->company_id !== $bundleCompanyId) {
                    return response()->json(['error' => 'Product company does not match bundle company'], 422);
                }
            }
            $validated['option_id'] = $optionId;
            $validated['created_at'] = now();
            $validated['updated_at'] = now();
            $idNew = \Illuminate\Support\Facades\DB::table('product_bundle_option_items')->insertGetId($validated);
            $item = \Illuminate\Support\Facades\DB::table('product_bundle_option_items')->where('id', $idNew)->first();
            return response()->json($item, 201);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::delete('{id}/bundles/{bundleId}/options/{optionId}/items/{itemId}', function ($id, $bundleId, $optionId, $itemId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['deleted' => false, 'error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            \Illuminate\Support\Facades\DB::table('product_bundle_option_items')->where('id', $itemId)->where('option_id', $optionId)->delete();
            return response()->json(['deleted' => true], 200);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::get('{id}/bundles/{bundleId}/items', function ($id, $bundleId) {
            \App\Models\Product::findOrFail($id);
            $items = \Illuminate\Support\Facades\DB::table('product_bundle_items')
                ->where('bundle_id', $bundleId)
                ->orderBy('display_order')
                ->get();
            return response()->json($items);
        });

        ApiRoute::post('{id}/bundles/{bundleId}/items', function ($id, $bundleId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            $bundle = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            if (!$bundle) { return response()->json(['error' => 'Bundle not found'], 404); }
            $validated = request()->validate([
                'product_id' => 'required|integer',
                'quantity' => 'nullable|integer|min:1',
                'is_required' => 'nullable|boolean',
                'is_optional' => 'nullable|boolean',
                'can_substitute' => 'nullable|boolean',
                'substitute_product_id' => 'nullable|integer',
                'display_order' => 'nullable|integer',
                'default_selected' => 'nullable|boolean',
            ]);
            $productExists = \Illuminate\Support\Facades\DB::table('products')->where('id', $validated['product_id'])->exists();
            if (!$productExists) { return response()->json(['error' => 'Product not found'], 422); }
            $bundleCompanyId = $bundle->company_id;
            if ($bundleCompanyId === null) {
                $baseProd = \Illuminate\Support\Facades\DB::table('products')->select('company_id')->where('id', $bundle->product_id)->first();
                $bundleCompanyId = $baseProd ? $baseProd->company_id : null;
            }
            if ($bundleCompanyId !== null) {
                $prodCompany = \Illuminate\Support\Facades\DB::table('products')->select('company_id')->where('id', $validated['product_id'])->first();
                if ($prodCompany && $prodCompany->company_id !== $bundleCompanyId) {
                    return response()->json(['error' => 'Product company does not match bundle company'], 422);
                }
            }
            $qty = isset($validated['quantity']) ? (int)$validated['quantity'] : 1;
            $isReq = isset($validated['is_required']) ? (bool)$validated['is_required'] : false;
            $isOpt = isset($validated['is_optional']) ? (bool)$validated['is_optional'] : false;
            $canSub = isset($validated['can_substitute']) ? (bool)$validated['can_substitute'] : false;
            $subId = $validated['substitute_product_id'] ?? null;
            if ($isReq && $isOpt) { return response()->json(['error' => 'Item cannot be both required and optional'], 422); }
            if ($canSub && !$subId) { return response()->json(['error' => 'Substitute product required when can_substitute'], 422); }
            if ($subId) {
                $subExists = \Illuminate\Support\Facades\DB::table('products')->where('id', $subId)->exists();
                if (!$subExists) { return response()->json(['error' => 'Substitute product not found'], 422); }
                if ($bundleCompanyId !== null) {
                    $subCompany = \Illuminate\Support\Facades\DB::table('products')->select('company_id')->where('id', $subId)->first();
                    if ($subCompany && $subCompany->company_id !== $bundleCompanyId) {
                        return response()->json(['error' => 'Substitute product company does not match bundle company'], 422);
                    }
                }
            }
            $insert = [
                'bundle_id' => $bundleId,
                'product_id' => $validated['product_id'],
                'quantity' => $qty,
                'is_required' => $isReq ? 1 : 0,
                'is_optional' => $isOpt ? 1 : 0,
                'can_substitute' => $canSub ? 1 : 0,
                'substitute_product_id' => $subId,
                'display_order' => $validated['display_order'] ?? null,
                'default_selected' => isset($validated['default_selected']) && $validated['default_selected'] ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $idNew = \Illuminate\Support\Facades\DB::table('product_bundle_items')->insertGetId($insert);
            $item = \Illuminate\Support\Facades\DB::table('product_bundle_items')->where('id', $idNew)->first();
            return response()->json($item, 201);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::put('{id}/bundles/{bundleId}/status', function ($id, $bundleId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $product = \App\Models\Product::findOrFail($id);
            $bundle = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            if (!$bundle || $bundle->product_id != $product->id) { return response()->json(['error' => 'Bundle not found'], 404); }
            $validated = request()->validate(['is_active' => 'required|boolean']);
            \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->update(['is_active' => $validated['is_active'], 'updated_at' => now()]);
            $updated = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            return response()->json($updated);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::put('{id}/bundles/{bundleId}/validity', function ($id, $bundleId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $product = \App\Models\Product::findOrFail($id);
            $bundle = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            if (!$bundle || $bundle->product_id != $product->id) { return response()->json(['error' => 'Bundle not found'], 404); }
            $validated = request()->validate([
                'valid_from' => 'nullable|date',
                'valid_to' => 'nullable|date',
            ]);
            $from = isset($validated['valid_from']) ? \Carbon\Carbon::parse($validated['valid_from']) : null;
            $to = isset($validated['valid_to']) ? \Carbon\Carbon::parse($validated['valid_to']) : null;
            if ($from && $to && $to->lt($from)) { return response()->json(['error' => 'valid_to must be after valid_from'], 422); }
            \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->update([
                'valid_from' => $from ? $from : null,
                'valid_to' => $to ? $to : null,
                'updated_at' => now(),
            ]);
            $updated = \Illuminate\Support\Facades\DB::table('product_bundles')->where('id', $bundleId)->first();
            return response()->json($updated);
        })->middleware(['auth:sanctum', 'api.auth']);

        ApiRoute::delete('{id}/bundles/{bundleId}/items/{bundleItemId}', function ($id, $bundleId, $bundleItemId) {
            $roles = user_roles() ?: [];
            if (!in_array('admin', $roles) && !in_array('manager', $roles)) {
                return response()->json(['deleted' => false, 'error' => 'Unauthorized'], 403);
            }
            \App\Models\Product::findOrFail($id);
            \Illuminate\Support\Facades\DB::table('product_bundle_items')->where('id', $bundleItemId)->where('bundle_id', $bundleId)->delete();
            return response()->json(['deleted' => true], 200);
        })->middleware(['auth:sanctum', 'api.auth']);
    });
});
