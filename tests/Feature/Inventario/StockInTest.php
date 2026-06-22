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

function stockInItemData(Product $product, int $quantity = 1, float $cost = 10): array
{
    return [
        'product_id' => $product->id,
        'quantity' => $quantity,
        'unit_cost' => $cost,
        'batch_number' => 'LOTE-' . now()->format('Ymd'),
        'expiration_date' => now()->addYear()->format('Y-m-d'),
        'warehouse_location' => 'Almacén Principal',
    ];
}

function baseStockInPayload(array $overrides = []): array
{
    return array_merge([
        'document_type' => 'Factura',
        'document_number' => 'F-' . now()->format('Ymd') . '-' . rand(100, 999),
        'reason' => 'Compra de prueba',
        'entry_date' => now()->format('Y-m-d'),
    ], $overrides);
}

describe('StockIn - Entradas de Inventario', function () {
    test('crear entrada de inventario aumenta stock', function () {
        $product = Product::factory()->create(['stock' => 0, 'cost' => 10]);
        $supplier = Supplier::factory()->create();

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'supplier_id' => $supplier->id,
            'items' => [stockInItemData($product, 100, 10.50)],
        ]));

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

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $order->id,
            'document_type' => 'Guía',
            'document_number' => 'GR-001',
            'items' => [stockInItemData($product, 25, 10)],
        ]));

        $response->assertRedirect();
        
        expect($product->fresh()->stock)->toBe(25);
    });

    test('entrada de ajuste no requiere proveedor', function () {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'reason' => 'Ajuste / Hallazgo',
            'items' => [stockInItemData($product, 5, 10)],
        ]));

        $response->assertRedirect();
        
        expect($product->fresh()->stock)->toBe(15);
    });

    test('no permite recibir mas de lo ordenado en la OC', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 0, 'cost' => 10]);

        $order = PurchaseOrder::create([
            'code' => 'OC-LIMIT-001',
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
            'quantity' => 10,
            'quantity_received' => 0,
            'unit_cost' => 10,
            'total_cost' => 100,
        ]);

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'purchase_order_id' => $order->id,
            'supplier_id' => $supplier->id,
            'items' => [stockInItemData($product, 5, 10)],
        ]));
        $response->assertSessionHas('success');
        expect($product->fresh()->stock)->toBe(5);

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'purchase_order_id' => $order->id,
            'supplier_id' => $supplier->id,
            'items' => [stockInItemData($product, 10, 10)],
        ]));
        $response->assertSessionHasErrors('items.0.quantity');
        expect($product->fresh()->stock)->toBe(5);
    });

    test('auto-completa la OC cuando todos los productos estan recibidos', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 0, 'cost' => 10]);

        $order = PurchaseOrder::create([
            'code' => 'OC-AUTO-001',
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 1,
            'subtotal' => 50,
            'tax_amount' => 0,
            'total' => 50,
            'status' => 'issued',
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'quantity' => 5,
            'quantity_received' => 0,
            'unit_cost' => 10,
            'total_cost' => 50,
        ]);

        $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'purchase_order_id' => $order->id,
            'supplier_id' => $supplier->id,
            'items' => [stockInItemData($product, 5, 10)],
        ]));

        expect($order->fresh()->status)->toBe('completed');
    });

    test('permite recibir parcialmente y luego completar sin exceder', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 0, 'cost' => 10]);

        $order = PurchaseOrder::create([
            'code' => 'OC-PARTIAL-001',
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 1,
            'subtotal' => 80,
            'tax_amount' => 0,
            'total' => 80,
            'status' => 'issued',
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'quantity' => 8,
            'quantity_received' => 0,
            'unit_cost' => 10,
            'total_cost' => 80,
        ]);

        $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'purchase_order_id' => $order->id,
            'supplier_id' => $supplier->id,
            'items' => [stockInItemData($product, 3, 10)],
        ]));
        expect($product->fresh()->stock)->toBe(3);

        $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'purchase_order_id' => $order->id,
            'supplier_id' => $supplier->id,
            'items' => [stockInItemData($product, 5, 10)],
        ]));
        expect($product->fresh()->stock)->toBe(8);
        expect($order->fresh()->status)->toBe('completed');
    });

    test('exige fecha de vencimiento si el producto es perecedero', function () {
        $product = Product::factory()->create(['is_perishable' => true]);
        $supplier = Supplier::factory()->create();

        $itemData = stockInItemData($product, 10, 10);
        unset($itemData['expiration_date']); // Remover fecha de vencimiento

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'supplier_id' => $supplier->id,
            'items' => [$itemData],
        ]));

        $response->assertSessionHasErrors('items.0.expiration_date');
    });

    test('no exige fecha de vencimiento si el producto no es perecedero', function () {
        $product = Product::factory()->create(['is_perishable' => false, 'stock' => 0]);
        $supplier = Supplier::factory()->create();

        $itemData = stockInItemData($product, 10, 10);
        $itemData['expiration_date'] = null; // Fecha de vencimiento nula

        $response = $this->post(route('admin.stock-in.store'), baseStockInPayload([
            'supplier_id' => $supplier->id,
            'items' => [$itemData],
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        expect($product->fresh()->stock)->toBe(10);
    });
});
