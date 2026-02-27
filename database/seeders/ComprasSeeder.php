<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComprasSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('===========================================');
        $this->command->info('Cargando datos del módulo de COMPRAS');
        $this->command->info('===========================================');

        // Primero ejecutar seeders base si no existen
        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            UnitSeeder::class,
            LocationSeeder::class,
        ]);

        // Datos de productos y proveedores
        $this->call([
            ProductSeeder::class,
            SupplierSeeder::class,
        ]);

        // Módulo de compras
        $this->call([
            RequestForQuotationSeeder::class,
            PurchaseQuoteSeeder::class,
            PurchaseOrderSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('RESUMEN - Módulo de Compras');
        $this->command->info('===========================================');
        
        $rfq = \App\Models\RequestForQuotation::count();
        $quotes = \App\Models\PurchaseQuote::count();
        $orders = \App\Models\PurchaseOrder::count();
        $products = \App\Models\Product::count();
        $suppliers = \App\Models\Supplier::count();

        $this->command->info("Productos: {$products}");
        $this->command->info("Proveedores: {$suppliers}");
        $this->command->info("RFQs: {$rfq}");
        $this->command->info("Cotizaciones: {$quotes}");
        $this->command->info("Órdenes de Compra: {$orders}");
        $this->command->info('');
        $this->command->info('¡Datos de compras cargados exitosamente!');
    }
}
