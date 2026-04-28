<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    private const TTL_SHORT = 300;
    private const TTL_MEDIUM = 900;
    private const TTL_LONG = 3600;

    public function categories()
    {
        return Cache::remember('categories:list', self::TTL_MEDIUM, function () {
            return \App\Models\Category::pluck('name', 'id');
        });
    }

    public function units()
    {
        return Cache::remember('units:list', self::TTL_MEDIUM, function () {
            return \App\Models\Unit::pluck('name', 'id');
        });
    }

    public function locations()
    {
        return Cache::remember('locations:list', self::TTL_MEDIUM, function () {
            return \App\Models\Location::pluck('name', 'id');
        });
    }

    public function brands()
    {
        return Cache::remember('brands:list', self::TTL_MEDIUM, function () {
            return \App\Models\Brand::pluck('name', 'id');
        });
    }

    public function suppliers()
    {
        return Cache::remember('suppliers:list', self::TTL_MEDIUM, function () {
            return \App\Models\Supplier::pluck('name', 'id');
        });
    }

    public function productsList()
    {
        return Cache::remember('products:list', self::TTL_SHORT, function () {
            return \App\Models\Product::orderBy('name')->pluck('name', 'id');
        });
    }

    public function productStock(int $productId)
    {
        return Cache::remember("products:stock:{$productId}", self::TTL_SHORT, function () use ($productId) {
            return \App\Models\Product::find($productId)?->stock ?? 0;
        });
    }

    public function inventorySummary()
    {
        return Cache::remember('inventory:summary', self::TTL_LONG, function () {
            return [
                'total_products' => \App\Models\Product::count(),
                'total_stock' => \App\Models\Product::sum('stock'),
                'low_stock' => \App\Models\Product::whereColumn('stock', '<=', 'min_stock')->count(),
                'out_of_stock' => \App\Models\Product::where('stock', '<=', 0)->count(),
            ];
        });
    }

    public function purchasesSummary()
    {
        return Cache::remember('purchases:summary', self::TTL_MEDIUM, function () {
            return [
                'pending_rfqs' => \App\Models\RequestForQuotation::where('status', 'Pending')->count(),
                'pending_orders' => \App\Models\PurchaseOrder::where('status', 'Pending')->count(),
            ];
        });
    }

    public function requestsSummary()
    {
        return Cache::remember('requests:summary', self::TTL_SHORT, function () {
            return [
                'pending' => \App\Models\InventoryRequest::where('status', 'Pending')->count(),
                'approved' => \App\Models\InventoryRequest::where('status', 'Approved')->count(),
                'rejected' => \App\Models\InventoryRequest::where('status', 'Rejected')->count(),
            ];
        });
    }

    public function invalidateCategories()
    {
        Cache::forget('categories:list');
    }

    public function invalidateUnits()
    {
        Cache::forget('units:list');
    }

    public function invalidateLocations()
    {
        Cache::forget('locations:list');
    }

    public function invalidateBrands()
    {
        Cache::forget('brands:list');
    }

    public function invalidateSuppliers()
    {
        Cache::forget('suppliers:list');
    }

    public function invalidateProducts()
    {
        Cache::forget('products:list');
        Cache::forget('inventory:summary');
    }

    public function invalidateProductStock(int $productId)
    {
        Cache::forget("products:stock:{$productId}");
        Cache::forget('inventory:summary');
        Cache::forget('products:list');
    }

    public function invalidatePurchases()
    {
        Cache::forget('purchases:summary');
    }

    public function invalidateRequests()
    {
        Cache::forget('requests:summary');
    }

    public function invalidateAll()
    {
        Cache::forget('categories:list');
        Cache::forget('units:list');
        Cache::forget('locations:list');
        Cache::forget('brands:list');
        Cache::forget('suppliers:list');
        Cache::forget('products:list');
        Cache::forget('inventory:summary');
        Cache::forget('purchases:summary');
        Cache::forget('requests:summary');
    }
}
