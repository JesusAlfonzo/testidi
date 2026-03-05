<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use App\Models\Product;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('home', function ($view) {
            $lowStockProducts = Cache::remember('home:low_stock_products', 300, function () {
                return Product::whereColumn('stock', '<', 'min_stock')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            });

            $view->with('lowStockProducts', $lowStockProducts);
        });
    }
}
