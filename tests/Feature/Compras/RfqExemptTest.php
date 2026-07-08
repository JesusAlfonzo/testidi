<?php

use App\Models\Product;
use App\Models\User;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('RFQ Exempt VAT Items', function () {
    
    test('puede crear rfq guardando is_exempt en rfq_items', function () {
        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 20]);

        $payload = [
            'title' => 'RFQ de Prueba Exenta',
            'description' => 'Prueba de exención de IVA por ítem',
            'priority' => 'baja',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product1->id,
                    'quantity' => 10,
                    'is_exempt' => true,
                    'notes' => 'Este item es exento',
                ],
                [
                    'item_type' => 'product',
                    'product_id' => $product2->id,
                    'quantity' => 5,
                    'is_exempt' => false,
                    'notes' => 'Este item paga IVA',
                ]
            ]
        ];

        $response = $this->post(route('admin.rfq.store'), $payload);
        $response->assertRedirect();

        $rfq = RequestForQuotation::latest('id')->first();
        expect($rfq->items)->toHaveCount(2);

        $itemExempt = $rfq->items->where('product_id', $product1->id)->first();
        $itemTaxable = $rfq->items->where('product_id', $product2->id)->first();

        expect($itemExempt->is_exempt)->toBeTrue();
        expect($itemTaxable->is_exempt)->toBeFalse();
    });

    test('puede actualizar rfq guardando is_exempt', function () {
        $product = Product::factory()->create(['stock' => 10]);
        
        $rfq = RequestForQuotation::create([
            'code' => RequestForQuotation::generateCode(),
            'title' => 'RFQ original',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $rfq->items()->create([
            'item_type' => 'product',
            'product_id' => $product->id,
            'quantity' => 10,
            'is_exempt' => false,
        ]);

        $payload = [
            'title' => 'RFQ modificado',
            'priority' => 'media',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'is_exempt' => true, // Cambia a exento
                    'notes' => 'Modificado a exento',
                ]
            ]
        ];

        $response = $this->put(route('admin.rfq.update', $rfq), $payload);
        $response->assertRedirect();

        $rfqItem = RfqItem::where('rfq_id', $rfq->id)->first();
        expect($rfqItem->is_exempt)->toBeTrue();
    });

    test('al convertir RFQ a PO el calculo del IVA excluye los items exentos', function () {
        $product1 = Product::factory()->create(['stock' => 100]);
        $product2 = Product::factory()->create(['stock' => 100]);
        $supplier = \App\Models\Supplier::factory()->create();

        $rfq = RequestForQuotation::create([
            'code' => RequestForQuotation::generateCode(),
            'title' => 'RFQ de Prueba Conversion',
            'status' => 'sent',
            'created_by' => $this->user->id,
        ]);

        // Item 1: Exento, cantidad = 2, costo = 100 (subtotal = 200, IVA = 0)
        $rfq->items()->create([
            'item_type' => 'product',
            'product_id' => $product1->id,
            'quantity' => 2,
            'is_exempt' => true,
        ]);

        // Item 2: Gravado, cantidad = 3, costo = 50 (subtotal = 150, IVA = 24)
        $rfq->items()->create([
            'item_type' => 'product',
            'product_id' => $product2->id,
            'quantity' => 3,
            'is_exempt' => false,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(5)->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 35.00,
            'iva_exempt' => false, // Pedido no exento en general
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product1->id,
                    'quantity' => 2,
                    'unit_cost' => 100.00,
                    'is_exempt' => true,
                ],
                [
                    'item_type' => 'product',
                    'product_id' => $product2->id,
                    'quantity' => 3,
                    'unit_cost' => 50.00,
                    'is_exempt' => false,
                ]
            ]
        ];

        $response = $this->post(route('admin.rfq.store-po', $rfq), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $po = \App\Models\PurchaseOrder::latest('id')->first();
        
        // subtotal = 350
        // tax_amount = 150 * 0.16 = 24
        // total = 374
        expect($po->subtotal)->toEqual(350.00);
        expect($po->tax_amount)->toEqual(24.00);
        expect($po->total)->toEqual(374.00);

        // Equivalentes Bs
        // subtotal_bs = 350 * 35 = 12250
        // tax_amount_bs = 24 * 35 = 840
        // total_bs = 374 * 35 = 13090
        expect($po->subtotal_bs)->toEqual(12250.00);
        expect($po->tax_amount_bs)->toEqual(840.00);
        expect($po->total_bs)->toEqual(13090.00);
    });
});
