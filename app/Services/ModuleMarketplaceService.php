<?php

namespace App\Services;

use App\Models\MarketplaceModule;
use App\Models\ModuleCategory;
use App\Models\Company;
use App\Models\CompanyModuleSubscription;
use Illuminate\Support\Facades\Log;

class ModuleMarketplaceService
{
    /**
     * Get all active marketplace modules
     */
    public function getActiveModules(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = MarketplaceModule::where('is_active', true)
            ->with(['category', 'dependencies']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('module_key', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('sort_order')
            ->orderBy('is_featured', 'desc')
            ->orderBy('download_count', 'desc')
            ->get();
    }

    /**
     * Get module by key
     */
    public function getModuleByKey(string $moduleKey): ?MarketplaceModule
    {
        return MarketplaceModule::where('module_key', $moduleKey)
            ->where('is_active', true)
            ->with(['category', 'dependencies', 'reviews'])
            ->first();
    }

    /**
     * Get featured modules
     */
    public function getFeaturedModules(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return MarketplaceModule::where('is_active', true)
            ->where('is_featured', true)
            ->with(['category'])
            ->orderBy('sort_order')
            ->orderBy('download_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get modules by category
     */
    public function getModulesByCategory(int $categoryId): \Illuminate\Database\Eloquent\Collection
    {
        return MarketplaceModule::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with(['category'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Check if company has access to a module
     */
    public function hasAccess(Company $company, MarketplaceModule $module): bool
    {
        // Check if module is included in company's package
        if ($company->package) {
            $packageModules = json_decode($company->package->module_in_package, true) ?? [];
            if (in_array($module->module_key, $packageModules)) {
                return true;
            }
        }

        // Check if company has active subscription to this module as add-on
        $subscription = CompanyModuleSubscription::where('company_id', $company->id)
            ->where('marketplace_module_id', $module->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        return $subscription !== null;
    }

    /**
     * Get company's subscribed modules
     */
    public function getCompanySubscribedModules(Company $company): \Illuminate\Database\Eloquent\Collection
    {
        return CompanyModuleSubscription::where('company_id', $company->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['marketplaceModule', 'package'])
            ->get();
    }

    /**
     * Get all categories
     */
    public function getCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return ModuleCategory::orderBy('name')->get();
    }

    /**
     * Increment module download count
     */
    public function incrementDownloadCount(MarketplaceModule $module): void
    {
        $module->increment('download_count');
    }
}

