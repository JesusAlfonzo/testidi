<?php

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\RequestItem;
use App\Models\InventoryRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('Report Generator - Backend', function () {
    test('index page returns 200 for authorized user', function () {
        $response = $this->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.index');
        $response->assertViewHasAll(['products', 'users']);
    });

    test('validates incorrect report_type or dates', function () {
        $response = $this->get(route('admin.reports.index', [
            'report_type' => 'invalid_type',
        ]));
        $response->assertSessionHasErrors(['report_type']);

        $response2 = $this->get(route('admin.reports.index', [
            'report_type' => 'inventario',
            'fecha_inicio' => '2026-06-22',
            'fecha_fin' => '2026-06-20', // menor que inicio
        ]));
        $response2->assertSessionHasErrors(['fecha_fin']);
    });

    test('performs dynamic queries for inventario', function () {
        $product = Product::factory()->create(['name' => 'Test Product For Report', 'stock' => 15, 'cost' => 10.0]);

        $response = $this->get(route('admin.reports.index', [
            'report_type' => 'inventario',
            'product_id' => $product->id,
            'is_active' => 'active',
        ]));

        $response->assertStatus(200);
        $data = $response->viewData('data');
        expect($data)->not->toBeNull();
        expect($data->pluck('id'))->toContain($product->id);
        
        $totals = $response->viewData('totals');
        expect($totals)->not->toBeNull();
        expect($totals['count'])->toBe(1);
        expect($totals['sum_quantity'])->toBe(15);
        expect($totals['sum_amount'])->toEqual(150.0);
    });

    test('performs dynamic queries for entradas', function () {
        $product = Product::factory()->create();
        $stockIn = StockIn::create([
            'user_id' => $this->user->id,
            'entry_date' => now(),
            'document_type' => 'Factura',
            'document_number' => 'DOC-123',
            'reason' => 'Ingreso de prueba',
        ]);
        $item = StockInItem::create([
            'stock_in_id' => $stockIn->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 12.50,
            'batch_number' => 'LOT-REPORT-123',
            'warehouse_location' => 'Estante A',
        ]);

        $response = $this->get(route('admin.reports.index', [
            'report_type' => 'entradas',
            'product_id' => $product->id,
        ]));

        $response->assertStatus(200);
        $data = $response->viewData('data');
        expect($data)->not->toBeNull();
        expect($data->pluck('id'))->toContain($item->id);

        $totals = $response->viewData('totals');
        expect($totals)->not->toBeNull();
        expect($totals['count'])->toBe(1);
        expect($totals['sum_quantity'])->toBe(10);
        expect($totals['sum_amount'])->toEqual(125.0);
    });

    test('performs dynamic queries for salidas', function () {
        $product = Product::factory()->create();
        $request = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => 'Approved',
            'justification' => 'Despacho urgente',
            'destination_area' => 'Laboratorio',
            'requested_at' => now(),
            'processed_at' => now(),
        ]);
        $item = RequestItem::create([
            'request_id' => $request->id,
            'product_id' => $product->id,
            'quantity_requested' => 5,
            'unit_price_at_request' => 10.0,
            'item_type' => 'product',
        ]);

        $response = $this->get(route('admin.reports.index', [
            'report_type' => 'salidas',
            'product_id' => $product->id,
        ]));

        $response->assertStatus(200);
        $data = $response->viewData('data');
        expect($data)->not->toBeNull();
        expect($data->pluck('id'))->toContain($item->id);

        $totals = $response->viewData('totals');
        expect($totals)->not->toBeNull();
        expect($totals['count'])->toBe(1);
        expect($totals['sum_quantity'])->toBe(5);
        expect($totals['sum_amount'])->toEqual(50.0);
    });

    test('performs dynamic queries for fraccionamientos', function () {
        $product = Product::factory()->create();
        
        activity()
            ->on($product)
            ->by($this->user)
            ->withProperties(['quantity' => 20, 'type' => 'in'])
            ->log('Fraccionamiento de producto de prueba');

        $response = $this->get(route('admin.reports.index', [
            'report_type' => 'fraccionamientos',
            'product_id' => $product->id,
        ]));

        $response->assertStatus(200);
        $data = $response->viewData('data');
        expect($data)->not->toBeNull();
        expect($data->count())->toBeGreaterThan(0);

        $totals = $response->viewData('totals');
        expect($totals)->not->toBeNull();
        expect($totals['sum_quantity'])->toBe(20);
    });

    test('export endpoint generates a PDF stream', function () {
        $response = $this->post(route('admin.reports.export'), [
            'report_type' => 'inventario',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    });
});
