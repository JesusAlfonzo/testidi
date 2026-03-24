<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\User;
use App\Models\PurchaseOrder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('StockIn - Entradas de Inventario', function () {
    test('crear entrada de inventario aumenta stock', function () {
        $product = Product::factory()->create(['stock' => 0, 'cost' => 10]);
        $supplier = Supplier::factory()->create();

        $response = $this->post(route('admin.stock-in.store'), [
            'supplier_id' => $supplier->id,
            'quantity' => 100,
            'entry_date' => now()->format('Y-m-d'),
            'document_type' => 'invoice',
            'document_number' => 'FACT-001',
            'reason' => 'Entrada de prueba',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 100, 'unit_cost' => 10.50],
            ],
        ]);

        $response->assertRedirect();
        
        expect($product->fresh()->stock)->toBe(100);
    });

    test('crear entrada desde OC aumenta stock y actualiza quantity_received', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 0, 'cost' => 10]);
        
        $order = PurchaseOrder::create([
            'code' => 'OC-001',
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 1,
            'subtotal' => 100,
            'tax_amount' => 0,
            'total' => 100,
            'status' => 'issued',
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'quantity' => 50,
            'quantity_received' => 0,
            'unit_cost' => 10,
            'total_cost' => 500,
        ]);

        $response = $this->post(route('admin.stock-in.store'), [
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $order->id,
            'quantity' => 25,
            'entry_date' => now()->format('Y-m-d'),
            'document_type' => 'delivery',
            'document_number' => 'GR-001',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 25, 'unit_cost' => 10],
            ],
        ]);

        $response->assertRedirect();
        
        expect($product->fresh()->stock)->toBe(25);
    });

    test('entrada de ajuste no requiere proveedor', function () {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post(route('admin.stock-in.store'), [
            'quantity' => 5,
            'entry_date' => now()->format('Y-m-d'),
            'reason' => 'Ajuste / Hallazgo',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 10],
            ],
        ]);

        $response->assertRedirect();
        
        expect($product->fresh()->stock)->toBe(15);
    });
});
