<?php

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\User;
use App\Models\InventoryRequest;
use App\Models\Kit;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
    
    $this->category = Category::factory()->create();
    $this->unit = Unit::factory()->create();
    $this->location = Location::factory()->create();
});

describe('FIFO - First In First Out', function () {
    
    test('consumeFromOldestBatch consume lote mas antiguo primero', function () {
        $product = Product::factory()->create([
            'track_expiry' => true,
            'stock' => 30,
        ]);

        ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-001',
            'quantity' => 10,
            'expiry_date' => now()->addDays(10),
            'unit_cost' => 10,
        ]);

        ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-002',
            'quantity' => 20,
            'expiry_date' => now()->addDays(30),
            'unit_cost' => 10,
        ]);

        $consumed = ProductBatch::consumeFromOldestBatch($product->id, 15);

        expect($consumed)->toHaveCount(2)
            ->and($consumed[0]['batch_number'])->toBe('BATCH-001')
            ->and($consumed[0]['quantity'])->toBe(10)
            ->and($consumed[1]['batch_number'])->toBe('BATCH-002')
            ->and($consumed[1]['quantity'])->toBe(5);

        $batch1 = ProductBatch::where('batch_number', 'BATCH-001')->first();
        $batch2 = ProductBatch::where('batch_number', 'BATCH-002')->first();

        expect($batch1->quantity)->toBe(0)
            ->and($batch2->quantity)->toBe(15);
    });

    test('shouldUseFifo retorna true cuando track_expiry y hay lotes', function () {
        $product = Product::factory()->create([
            'track_expiry' => true,
            'stock' => 10,
        ]);

        ProductBatch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-001',
            'quantity' => 10,
            'expiry_date' => now()->addDays(30),
            'unit_cost' => 10,
        ]);

        expect($product->shouldUseFifo())->toBeTrue();
    });

    test('shouldUseFifo retorna false cuando no hay track_expiry', function () {
        $product = Product::factory()->create([
            'track_expiry' => false,
            'stock' => 10,
        ]);

        expect($product->shouldUseFifo())->toBeFalse();
    });

    test('shouldUseFifo retorna false cuando no hay lotes activos', function () {
        $product = Product::factory()->create([
            'track_expiry' => true,
            'stock' => 0,
        ]);

        expect($product->shouldUseFifo())->toBeFalse();
    });
});

describe('Pre-validacion de Stock en Aprobacion', function () {

    test('aprobacion falla si stock insuficiente en cualquier item', function () {
        $product = Product::factory()->create([
            'stock' => 5,
            'is_active' => true,
            'track_expiry' => false,
        ]);

        $request = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => 'Pending',
            'justification' => 'Solicitud de prueba',
            'requested_at' => now(),
        ]);

        $request->items()->create([
            'product_id' => $product->id,
            'item_type' => 'product',
            'quantity_requested' => 10,
            'unit_price_at_request' => 10,
        ]);

        $service = new \App\Services\InventoryRequestService();

        expect(fn() => $service->approve($request))
            ->toThrow(\Exception::class, 'Stock insuficiente');
    });

    test('aprobacion exitosa cuando todos los items tienen stock', function () {
        $product = Product::factory()->create([
            'stock' => 100,
            'is_active' => true,
            'track_expiry' => false,
        ]);

        $request = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => 'Pending',
            'justification' => 'Solicitud de prueba',
            'requested_at' => now(),
        ]);

        $request->items()->create([
            'product_id' => $product->id,
            'item_type' => 'product',
            'quantity_requested' => 10,
            'unit_price_at_request' => 10,
        ]);

        $service = new \App\Services\InventoryRequestService();
        $service->approve($request);

        expect($product->fresh()->stock)->toBe(90)
            ->and($request->fresh()->status)->toBe('Approved');
    });
});

describe('Validacion de Eliminacion de Maestros', function () {

    test('no se puede eliminar categoria con productos', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->delete(route('admin.categories.destroy', $category));

        $response->assertSessionHas('error');
        expect($product->fresh()->category_id)->toBe($category->id);
    });

    test('no se puede eliminar unidad con productos', function () {
        $unit = Unit::factory()->create();
        $product = Product::factory()->create(['unit_id' => $unit->id]);

        $response = $this->delete(route('admin.units.destroy', $unit));

        $response->assertSessionHas('error');
    });

    test('no se puede eliminar ubicacion con productos', function () {
        $location = Location::factory()->create();
        $product = Product::factory()->create(['location_id' => $location->id]);

        $response = $this->delete(route('admin.locations.destroy', $location));

        $response->assertSessionHas('error');
    });

    test('no se puede eliminar marca con productos', function () {
        $brand = \App\Models\Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $response = $this->delete(route('admin.brands.destroy', $brand));

        $response->assertSessionHas('error');
    });
});

describe('Validacion de Proveedor Duplicado', function () {

    test('convertToSupplier rechaza proveedor duplicado por nombre', function () {
        $supplier = Supplier::factory()->create(['name' => 'Proveedor Test']);
        
        $quote = \App\Models\PurchaseQuote::factory()->create([
            'supplier_name_temp' => 'Proveedor Test',
            'supplier_id' => null,
        ]);

        $response = $this->post(route('admin.quotations.convert-supplier', $quote), []);

        $response->assertSessionHas('error');
    });

    test('convertToSupplier rechaza proveedor duplicado por tax_id', function () {
        $supplier = Supplier::factory()->create(['tax_id' => 'J123456789']);
        
        $quote = \App\Models\PurchaseQuote::factory()->create([
            'supplier_name_temp' => 'Nuevo Proveedor',
            'supplier_email_temp' => 'test@test.com',
            'supplier_id' => null,
        ]);

        $response = $this->post(route('admin.quotations.convert-supplier', $quote), [
            'tax_id' => 'J123456789',
        ]);

        $response->assertSessionHas('error');
    });
});

describe('Cache Invalidation', function () {

    test('invalidateProductStock limpia products:list', function () {
        $cacheService = new \App\Services\CacheService();
        
        \Illuminate\Support\Facades\Cache::put('products:list', ['test' => 'data']);
        \Illuminate\Support\Facades\Cache::put('products:stock:1', 100);
        
        $cacheService->invalidateProductStock(1);
        
        expect(\Illuminate\Support\Facades\Cache::get('products:list'))->toBeNull()
            ->and(\Illuminate\Support\Facades\Cache::get('products:stock:1'))->toBeNull();
    });
});
