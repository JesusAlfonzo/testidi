<?php

use App\Models\Product;
use App\Models\RequestForQuotation;
use App\Models\RfqSupplierOffer;
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

    $this->supplier = Supplier::factory()->create();
    $this->product = Product::factory()->create();
    
    $this->rfq = RequestForQuotation::create([
        'code' => RequestForQuotation::generateCode(),
        'title' => 'RFQ Test para Oferta',
        'status' => 'sent',
        'created_by' => $this->user->id,
    ]);
});

describe('RFQ Supplier Offers Persistence', function () {
    test('puede guardar una oferta de proveedor vía AJAX', function () {
        $response = $this->postJson(route('admin.rfq.save-supplier-offer', $this->rfq), [
            'supplier_id' => $this->supplier->id,
            'notes' => 'Notas de la cotización',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit_price' => 150.50,
                    'currency' => 'USD',
                    'tax_status' => 'exento',
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Oferta guardada exitosamente.',
        ]);

        $this->assertDatabaseHas('rfq_supplier_offers', [
            'rfq_id' => $this->rfq->id,
            'supplier_id' => $this->supplier->id,
            'notes' => 'Notas de la cotización',
        ]);

        $this->assertDatabaseHas('rfq_supplier_offer_items', [
            'product_id' => $this->product->id,
            'unit_price' => 150.50,
            'currency' => 'USD',
            'tax_status' => 'exento',
        ]);
    });

    test('puede convertir una oferta guardada a Orden de Compra por POST', function () {
        // Primero creamos la oferta en la base de datos
        $offer = RfqSupplierOffer::create([
            'rfq_id' => $this->rfq->id,
            'supplier_id' => $this->supplier->id,
            'notes' => 'Oferta a convertir',
        ]);

        $offer->items()->create([
            'product_id' => $this->product->id,
            'unit_price' => 200.00,
            'currency' => 'EUR',
            'tax_status' => 'gravado',
        ]);

        // Hacemos el POST de conversión
        $response = $this->post(route('admin.rfq.convert-to-po', $this->rfq), [
            'rfq_supplier_offer_id' => $offer->id,
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('admin.rfq.convert-to-po');
        $response->assertViewHas('offer');
    });

    test('puede actualizar la prioridad de una RFQ', function () {
        $rfqDraft = RequestForQuotation::create([
            'code' => RequestForQuotation::generateCode() . '-DRAFT',
            'title' => 'RFQ Borrador',
            'status' => 'draft',
            'created_by' => $this->user->id,
            'priority' => 'baja',
        ]);

        $response = $this->put(route('admin.rfq.update', $rfqDraft), [
            'title' => 'RFQ Borrador Modificada',
            'priority' => 'alta',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                ]
            ]
        ]);

        $response->assertRedirect(route('admin.rfq.show', $rfqDraft));
        $this->assertDatabaseHas('request_for_quotations', [
            'id' => $rfqDraft->id,
            'title' => 'RFQ Borrador Modificada',
            'priority' => 'alta',
        ]);
    });

    test('puede filtrar RFQs en listado Datatables por estado y prioridad', function () {
        // Crear RFQs con distintos estados y prioridades
        $rfq1 = RequestForQuotation::create([
            'code' => RequestForQuotation::generateCode() . '-F1',
            'title' => 'RFQ Filtro 1',
            'status' => 'sent',
            'priority' => 'alta',
            'created_by' => $this->user->id,
        ]);

        $rfq2 = RequestForQuotation::create([
            'code' => RequestForQuotation::generateCode() . '-F2',
            'title' => 'RFQ Filtro 2',
            'status' => 'draft',
            'priority' => 'media',
            'created_by' => $this->user->id,
        ]);

        // Filtrar por status 'sent' y prioridad 'alta'
        $response = $this->getJson(route('admin.rfq.index', [
            'status' => 'sent',
            'priority' => 'alta',
        ]), [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Verificar que solo se incluye rfq1 (que tiene status sent y prioridad alta)
        $codes = array_column($data, 'code');
        expect($codes)->toContain($rfq1->code);
        expect($codes)->not->toContain($rfq2->code);
    });
});
