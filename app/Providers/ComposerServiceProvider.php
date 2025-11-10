<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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

            // CLAVE: Usamos 'min_stock' en lugar de 'minimum_stock'
            $lowStockProducts = Product::whereColumn('stock', '<', 'min_stock')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Pasa la colección de productos a la vista
            $view->with('lowStockProducts', $lowStockProducts);
        });
    }
}
