<?php

use App\Models\Product;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    
    // Crear usuario con permisos completos
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    
    $this->actingAs($this->user);

    // Datos de prueba
    $this->products = Product::factory(10)->create();
    $this->supplier = Supplier::factory()->create();
});

function createRfq($status = 'draft') {
    $rfq = RequestForQuotation::create([
        'code' => RequestForQuotation::generateCode(),
        'title' => 'RFQ Test',
        'description' => 'Description',
        'status' => $status,
        'created_by' => auth()->id() ?? 1,
    ]);
    return $rfq;
}

function createOrder($status = 'draft', $supplierId = null) {
    $order = PurchaseOrder::create([
        'code' => PurchaseOrder::generateCode(),
        'supplier_id' => $supplierId ?? Supplier::factory()->create()->id,
        'date_issued' => now(),
        'currency' => 'USD',
        'exchange_rate' => 17.15,
        'subtotal' => 100,
        'tax_amount' => 0,
        'total' => 100,
        'status' => $status,
        'created_by' => auth()->id() ?? 1,
    ]);
    return $order;
}

describe('RFQ - Solicitudes de Cotización', function () {
    test('crear rfq en estado draft', function () {
        $product = Product::factory()->create();
        
        $response = $this->post(route('admin.rfq.store'), [
            'title' => 'RFQ Test - Compra de reactivos',
            'description' => 'Prueba de creación de RFQ',
            'date_required' => now()->addDays(30)->format('Y-m-d'),
            'delivery_deadline' => now()->addDays(45)->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('request_for_quotations', [
            'title' => 'RFQ Test - Compra de reactivos',
            'status' => 'draft',
        ]);
    });

    test('enviar rfq cambia estado a sent', function () {
        $rfq = createRfq('draft');

        $response = $this->post(route('admin.rfq.mark-sent', $rfq));

        $response->assertSessionHas('success');
        expect($rfq->fresh()->status)->toBe('sent');
    });

    test('no se puede editar rfq enviada', function () {
        $rfq = createRfq('sent');
        $product = Product::factory()->create();

        $response = $this->put(route('admin.rfq.update', $rfq), [
            'title' => 'Nuevo título',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $response->assertSessionHas('error');
    });

    test('cancelar rfq cambia estado a cancelled', function () {
        $rfq = createRfq('draft');

        $response = $this->post(route('admin.rfq.cancel', $rfq));

        $response->assertSessionHas('success');
        expect($rfq->fresh()->status)->toBe('cancelled');
    });
});

describe('Órdenes de Compra', function () {
    test('crear orden de compra', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $response = $this->post(route('admin.purchaseOrders.store'), [
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(30)->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 17.15,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 100.00],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);
    });

    test('emitir oc cambia estado a issued', function () {
        $order = createOrder('draft');

        $response = $this->post(route('admin.purchaseOrders.issue', $order));

        $response->assertSessionHas('success');
        expect($order->fresh()->status)->toBe('issued');
    });

    test('no se puede editar oc emitida', function () {
        $order = createOrder('issued');
        $product = Product::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->put(route('admin.purchaseOrders.update', $order), [
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 17.15,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 100.00],
            ],
        ]);

        $response->assertSessionHas('error');
    });

    test('cancelar oc cambia estado a cancelled', function () {
        $order = createOrder('draft');

        $response = $this->post(route('admin.purchaseOrders.cancel', $order));

        $response->assertSessionHas('success');
        expect($order->fresh()->status)->toBe('cancelled');
    });

    test('no se puede cancelar oc completada', function () {
        $order = createOrder('completed');

        $response = $this->post(route('admin.purchaseOrders.cancel', $order));

        $response->assertSessionHas('error');
    });
});

describe('Flujo Completo', function () {
    test('flujo completo rfq -> oc', function () {
        // 1. Crear y enviar RFQ
        $rfq = createRfq('draft');
        
        $this->post(route('admin.rfq.mark-sent', $rfq));
        expect($rfq->fresh()->status)->toBe('sent');

        // 2. Crear OC directa
        $product = Product::factory()->create();
        $response = $this->post(route('admin.purchaseOrders.store'), [
            'supplier_id' => $this->supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(30)->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 17.15,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 100.00],
            ],
        ]);

        $order = PurchaseOrder::latest()->first();
        expect($order)->not->toBeNull();
        expect($order->status)->toBe('draft');

        // 3. Emitir OC
        $this->post(route('admin.purchaseOrders.issue', $order));
        expect($order->fresh()->status)->toBe('issued');

        // 4. Validar flujo completo exitoso
        expect($rfq->fresh()->status)->toBe('sent');
        expect($order->fresh()->status)->toBe('issued');
    });
});
