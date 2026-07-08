<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\Supplier;
use App\Models\ProductUomConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);

    // Unidades de medida
    $this->baseUnit = Unit::create(['name' => 'Unidad', 'abbreviation' => 'U', 'user_id' => $this->user->id]);
    $this->boxUnit = Unit::create(['name' => 'Caja', 'abbreviation' => 'CJ', 'user_id' => $this->user->id]);
    $this->bulkUnit = Unit::create(['name' => 'Bulto', 'abbreviation' => 'BL', 'user_id' => $this->user->id]);

    $this->category = Category::factory()->create();
    $this->location = Location::factory()->create();

    // Producto con unidad base 'Unidad'
    $this->product = Product::create([
        'code' => 'PROD-UOM-001',
        'name' => 'Jeringa Desechable',
        'category_id' => $this->category->id,
        'unit_id' => $this->baseUnit->id, // Unidad base
        'location_id' => $this->location->id,
        'cost' => 0.50,
        'price' => 1.00,
        'stock' => 100, // Stock inicial
        'min_stock' => 10,
        'is_active' => true,
        'user_id' => $this->user->id,
    ]);

    // Conversiones
    // 1 Caja = 36 Unidades
    ProductUomConversion::create([
        'product_id' => $this->product->id,
        'uom_id' => $this->boxUnit->id,
        'conversion_factor' => 36.0000,
    ]);

    // 1 Bulto = 360 Unidades
    ProductUomConversion::create([
        'product_id' => $this->product->id,
        'uom_id' => $this->bulkUnit->id,
        'conversion_factor' => 360.0000,
    ]);
});

describe('Sistema de Conversión de Unidades de Medida (UoM)', function () {

    test('las relaciones de conversión y getConversionFactorFor devuelven valores correctos', function () {
        expect($this->product->uomConversions)->toHaveCount(2);

        // Factor para la misma unidad base es 1.0
        expect($this->product->getConversionFactorFor($this->baseUnit->id))->toBe(1.0);

        // Factores para unidades alternativas
        expect($this->product->getConversionFactorFor($this->boxUnit->id))->toBe(36.0);
        expect($this->product->getConversionFactorFor($this->bulkUnit->id))->toBe(360.0);

        // Unidad sin conversión registrada devuelve 1.0
        $otherUnit = Unit::create(['name' => 'Litro', 'abbreviation' => 'L', 'user_id' => $this->user->id]);
        expect($this->product->getConversionFactorFor($otherUnit->id))->toBe(1.0);
    });

    test('la creación de ítems SDC calcula correctamente la cantidad en unidad base', function () {
        $rfq = RequestForQuotation::create([
            'title' => 'Solicitud de Insumos',
            'date_required' => now(),
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        // Guardar 2 Bultos
        $response = $this->post(route('admin.rfq.store'), [
            'title' => 'Solicitud de Insumos',
            'date_required' => now()->toDateString(),
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $this->product->id,
                    'uom_id' => $this->bulkUnit->id,
                    'quantity_uom' => 2,
                    'quantity' => 0, // se ignora y se calcula
                    'notes' => 'Jeringas en bultos',
                ]
            ]
        ]);

        $response->assertRedirect(route('admin.rfq.show', $rfq->id + 1));

        $item = RfqItem::where('rfq_id', $rfq->id + 1)->first();
        expect($item->uom_id)->toBe($this->bulkUnit->id);
        expect($item->quantity_uom)->toBe(2);
        // Cantidad base: 2 * 360 = 720
        expect($item->quantity)->toBe(720);
    });

    test('la creación de ítems ODC calcula correctamente la cantidad y coste base', function () {
        $supplier = Supplier::factory()->create();

        // Enviar 3 Cajas a $72.00 cada caja (Costo base esperado: $72.00 / 36 = $2.00)
        $response = $this->post(route('admin.purchaseOrders.store'), [
            'supplier_id' => $supplier->id,
            'date_issued' => now()->toDateString(),
            'currency' => 'USD',
            'exchange_rate' => 1.00,
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $this->product->id,
                    'uom_id' => $this->boxUnit->id,
                    'quantity_uom' => 3,
                    'unit_cost_uom' => 72.00,
                    'quantity' => 1, // Se recalculará en prepareForValidation
                    'unit_cost' => 1.00, // Se recalculará en prepareForValidation
                ]
            ]
        ]);

        $order = PurchaseOrder::latest('id')->first();
        $response->assertRedirect(route('admin.purchaseOrders.show', $order->id));

        $item = PurchaseOrderItem::where('purchase_order_id', $order->id)->first();
        
        expect($item->uom_id)->toBe($this->boxUnit->id);
        expect($item->quantity_uom)->toBe(3);
        expect((float) $item->unit_cost_uom)->toBe(72.00);

        // Cantidad base recalculada: 3 * 36 = 108
        expect($item->quantity)->toBe(108);
        // Costo unitario base recalculado: 72.00 / 36 = 2.00
        expect((float) $item->unit_cost)->toBe(2.00);
        // Total cost: 3 * 72 = 216
        expect((float) $item->total_cost)->toBe(216.00);
    });

    test('las Entradas de Almacén (StockIn) reciben en UoM, convierten a base e incrementan el inventario', function () {
        $supplier = Supplier::factory()->create();

        // Creamos la Orden de Compra previa: 2 Bultos a $180.00 el bulto
        $order = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date_issued' => now(),
            'currency' => 'USD',
            'exchange_rate' => 1.00,
            'status' => 'issued',
            'created_by' => $this->user->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $order->id,
            'item_type' => 'product',
            'product_id' => $this->product->id,
            'uom_id' => $this->bulkUnit->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'quantity' => 720, // base unit
            'quantity_uom' => 2,
            'quantity_received' => 0,
            'unit_cost' => 0.50, // 180 / 360 = 0.5
            'unit_cost_uom' => 180.00,
            'total_cost' => 360.00,
            'equivalent_bs' => 360.00,
        ]);

        $initialStock = $this->product->stock; // 100

        // El almacenista recibe 1 bulto (Equivale a 360 unidades base)
        $response = $this->post(route('admin.stock-in.store'), [
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $order->id,
            'document_type' => 'Factura',
            'document_number' => 'FAC-0001',
            'reason' => 'Compra',
            'entry_date' => now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $this->product->id,
                    'uom_id' => $this->bulkUnit->id,
                    'quantity_received_uom' => 1,
                    'quantity' => 1, // Se calculará en prepareForValidation a 360
                    'unit_cost' => 0.50,
                    'batch_number' => 'LOTE-TEST',
                    'warehouse_location' => 'Estante A',
                    'status' => 'received',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $stockIn = StockIn::latest('id')->first();
        $response->assertRedirect(route('admin.stock-in.index'));

        // Verificar el StockInItem creado
        $stockInItem = StockInItem::where('stock_in_id', $stockIn->id)->first();
        expect($stockInItem->uom_id)->toBe($this->bulkUnit->id);
        expect($stockInItem->quantity_received_uom)->toBe(1);
        expect($stockInItem->quantity_received_base)->toBe(360);
        expect($stockInItem->quantity)->toBe(360); // base unit

        // Verificar incremento del stock en base de datos
        $this->product->refresh();
        expect($this->product->stock)->toBe($initialStock + 360); // 100 + 360 = 460

        // Verificar actualización en la Orden de Compra
        $poItem->refresh();
        expect($poItem->quantity_received)->toBe(360); // recibido en unidad base
    });
});
