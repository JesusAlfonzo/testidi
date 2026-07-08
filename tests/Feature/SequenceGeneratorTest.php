<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\RequestForQuotation;
use App\Models\InventoryRequest;
use App\Models\Dispatch;
use App\Models\Supplier;
use App\Services\SequenceService;
use App\Traits\GeneratesSequenceCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Generación Automática de Códigos y SKUs', function () {

    test('las Órdenes de Compra generan códigos ODC anuales correctos', function () {
        $supplier = Supplier::factory()->create();

        $po1 = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date_issued' => now(),
            'currency' => 'USD',
            'exchange_rate' => 1.0000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $po2 = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date_issued' => now(),
            'currency' => 'USD',
            'exchange_rate' => 1.0000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $currentYear = date('Y');
        expect($po1->code)->toBe("ODC-{$currentYear}-0001");
        expect($po2->code)->toBe("ODC-{$currentYear}-0002");
    });

    test('las Solicitudes de Cotización generan códigos SDC anuales correctos', function () {
        $rfq1 = RequestForQuotation::create([
            'title' => 'Cotización 1',
            'date_required' => now(),
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $rfq2 = RequestForQuotation::create([
            'title' => 'Cotización 2',
            'date_required' => now(),
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $currentYear = date('Y');
        expect($rfq1->code)->toBe("SDC-{$currentYear}-0001");
        expect($rfq2->code)->toBe("SDC-{$currentYear}-0002");
    });

    test('las Solicitudes de Inventario generan códigos SDI anuales correctos', function () {
        $ir1 = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => InventoryRequest::STATUS_PENDING,
            'justification' => 'Test 1',
            'requested_at' => now(),
        ]);

        $ir2 = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => InventoryRequest::STATUS_PENDING,
            'justification' => 'Test 2',
            'requested_at' => now(),
        ]);

        $currentYear = date('Y');
        expect($ir1->code)->toBe("SDI-{$currentYear}-0001");
        expect($ir2->code)->toBe("SDI-{$currentYear}-0002");
    });

    test('los Despachos generan códigos DES anuales correctos con padding de 6 dígitos', function () {
        $ir = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => InventoryRequest::STATUS_PENDING,
            'justification' => 'Test',
            'requested_at' => now(),
        ]);

        $d1 = Dispatch::create([
            'inventory_request_id' => $ir->id,
            'dispatcher_id' => $this->user->id,
            'notes' => 'Despacho 1',
        ]);

        $d2 = Dispatch::create([
            'inventory_request_id' => $ir->id,
            'dispatcher_id' => $this->user->id,
            'notes' => 'Despacho 2',
        ]);

        $currentYear = date('Y');
        expect($d1->dispatch_number)->toBe("DES-{$currentYear}-000001");
        expect($d2->dispatch_number)->toBe("DES-{$currentYear}-000002");
    });

    test('los productos estricto generan SKUs automáticos basados en el prefijo o nombre de categoría', function () {
        $unit = Unit::factory()->create();
        $location = Location::factory()->create();

        // Categoría con prefijo explícito
        $categoryExplicit = Category::create([
            'name' => 'Reactivos Especiales',
            'prefix' => 'REAC',
            'user_id' => $this->user->id,
        ]);

        // Categoría sin prefijo (debe derivar del nombre)
        $categoryImplicit = Category::create([
            'name' => 'Vidrios y Tubos',
            'user_id' => $this->user->id,
        ]);

        $prod1 = Product::create([
            'is_generic' => false,
            'name' => 'Ácido Sulfúrico',
            'category_id' => $categoryExplicit->id,
            'unit_id' => $unit->id,
            'location_id' => $location->id,
            'cost' => 10,
            'price' => 15,
            'stock' => 5,
            'min_stock' => 2,
            'is_active' => true,
            'user_id' => $this->user->id,
        ]);

        $prod2 = Product::create([
            'is_generic' => false,
            'name' => 'Tubo de Ensayo',
            'category_id' => $categoryImplicit->id,
            'unit_id' => $unit->id,
            'location_id' => $location->id,
            'cost' => 1,
            'price' => 2,
            'stock' => 100,
            'min_stock' => 10,
            'is_active' => true,
            'user_id' => $this->user->id,
        ]);

        expect($prod1->code)->toBe('REAC-0001');
        // "Vidrios y Tubos" derivado -> "Vidr" sanitizado -> "VIDR"
        expect($prod2->code)->toBe('VIDR-0001');
    });

    test('los productos genéricos y los kits generan códigos con prefijo GEN y KIT respectivamente', function () {
        $unit = Unit::factory()->create();

        $genericProduct = Product::create([
            'is_generic' => true,
            'name' => 'Lápiz Genérico',
            'unit_id' => $unit->id,
            'cost' => 0.5,
            'price' => 1.0,
            'stock' => 10,
            'min_stock' => 5,
            'is_active' => true,
            'user_id' => $this->user->id,
        ]);

        $kitProduct = Product::create([
            'is_generic' => true,
            'is_kit' => true,
            'type' => 'composite_kit',
            'name' => 'Kit de Oficina',
            'unit_id' => $unit->id,
            'cost' => 10,
            'price' => 15,
            'stock' => 0,
            'min_stock' => 0,
            'is_active' => true,
            'user_id' => $this->user->id,
        ]);

        expect($genericProduct->code)->toBe('GEN-0001');
        expect($kitProduct->code)->toBe('KIT-0001');
    });

    test('se respetan los códigos ingresados de forma manual y se sanitizan para lectores de códigos de barras', function () {
        $unit = Unit::factory()->create();

        $prod = Product::create([
            'code' => 'mi_sku Especial/123!',
            'is_generic' => true,
            'name' => 'Producto Código Manual',
            'unit_id' => $unit->id,
            'cost' => 5,
            'price' => 10,
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
            'user_id' => $this->user->id,
        ]);

        // "mi_sku Especial/123!" -> reemplaza especial y espacios con guion -> "MI-SKU-ESPECIAL-123"
        expect($prod->code)->toBe('MI-SKU-ESPECIAL-123');
    });

    test('sanitización estricta remueve acentos y caracteres no Code-128', function () {
        $clean = GeneratesSequenceCode::sanitizeBarcodeString('Ácido Nítrico (H2O) @2026!');
        expect($clean)->toBe('ACIDO-NITRICO-H2O-2026');
    });

    test('la secuencia de base de datos se comporta de manera segura y correcta', function () {
        $service = app(SequenceService::class);
        $key = 'test_secuencia';

        $val1 = $service->getNextValue($key);
        $val2 = $service->getNextValue($key);

        expect($val1)->toBe(1);
        expect($val2)->toBe(2);

        $dbVal = DB::table('sequences')->where('key', $key)->first();
        expect($dbVal->current_value)->toBe(2);
    });
});
