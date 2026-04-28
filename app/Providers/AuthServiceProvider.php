<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryRequest;
use App\Models\Kit;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\RequestForQuotation;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\Unit;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\InventoryRequestPolicy;
use App\Policies\KitPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\RequestForQuotationPolicy;
use App\Policies\StockInPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UnitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        RequestForQuotation::class => RequestForQuotationPolicy::class,
        InventoryRequest::class => InventoryRequestPolicy::class,
        StockIn::class => StockInPolicy::class,
        Category::class => CategoryPolicy::class,
        Unit::class => UnitPolicy::class,
        Location::class => LocationPolicy::class,
        Brand::class => BrandPolicy::class,
        Supplier::class => SupplierPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
