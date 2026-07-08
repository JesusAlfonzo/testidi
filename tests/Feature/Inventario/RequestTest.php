<?php

use App\Models\Product;
use App\Models\User;
use App\Models\InventoryRequest;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('Requests - Solicitudes de Salida / Despachos', function () {
    test('puede acceder al listado de solicitudes', function () {
        $response = $this->get(route('admin.requests.index'));
        $response->assertStatus(200);
        $response->assertSee('Solicitudes de Salida');
    });

    test('DataTable retorna JSON con la información correcta y badges de prioridad', function () {
        $product = Product::factory()->create(['stock' => 20]);
        
        $requestModel = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => InventoryRequest::STATUS_PENDING,
            'justification' => '[ALTA] Justificación de prueba urgente',
            'destination_area' => 'Laboratorio',
            'reference' => 'Proyecto Vacunas',
            'requested_at' => now(),
        ]);

        $requestModel->items()->create([
            'product_id' => $product->id,
            'quantity_requested' => 5,
            'unit_price_at_request' => $product->cost ?? 10.0,
            'item_type' => 'product',
        ]);

        $response = $this->get(route('admin.requests.index', ['draw' => 1]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'date',
                    'requester',
                    'destination_area',
                    'justification',
                    'status',
                    'approver',
                    'processed',
                    'actions'
                ]
            ]
        ]);

        $data = $response->json('data.0');
        expect($data['justification'])->toContain('Justificación de prueba urgente');
    });

    test('guardar una solicitud guarda el campo de justificación sin cambios', function () {
        $product = Product::factory()->create(['stock' => 50]);

        $payload = [
            'destination_area' => 'Informatica',
            'reference' => 'Uso Interno',
            'justification' => 'Necesitamos insumos para el análisis mensual',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'item_type' => 'product',
                ]
            ]
        ];

        $response = $this->post(route('admin.requests.store'), $payload);
        $response->assertRedirect(route('admin.requests.index'));

        $savedRequest = InventoryRequest::latest('id')->first();
        expect($savedRequest->justification)->toBe('Necesitamos insumos para el análisis mensual');
        expect($savedRequest->destination_area)->toBe('Informatica');
        expect($savedRequest->reference)->toBe('Uso Interno');
    });
});
