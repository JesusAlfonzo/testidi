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
            'status' => 'Pending',
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
                    'priority',
                    'status',
                    'approver',
                    'processed',
                    'actions'
                ]
            ]
        ]);

        $data = $response->json('data.0');
        expect($data['justification'])->toContain('Justificación de prueba urgente');
        expect($data['priority'])->toContain('badge-danger');
        expect($data['priority'])->toContain('Alta');
    });

    test('DataTable puede filtrar solicitudes por prioridad', function () {
        $product = Product::factory()->create(['stock' => 20]);

        $reqAlta = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => 'Pending',
            'justification' => '[ALTA] Solicitud muy urgente',
            'destination_area' => 'Laboratorio',
            'reference' => 'Proyecto A',
            'requested_at' => now(),
        ]);

        $reqBaja = InventoryRequest::create([
            'requester_id' => $this->user->id,
            'status' => 'Pending',
            'justification' => '[BAJA] Solicitud sin prisa',
            'destination_area' => 'Oficina',
            'reference' => 'Proyecto B',
            'requested_at' => now(),
        ]);

        // 1. Filtrar por prioridad alta
        $responseAlta = $this->get(route('admin.requests.index', [
            'draw' => 1,
            'priority' => 'alta'
        ]));
        $responseAlta->assertStatus(200);
        $dataAlta = $responseAlta->json('data');
        expect($dataAlta)->toHaveCount(1);
        expect($dataAlta[0]['justification'])->toContain('Solicitud muy urgente');
        expect($dataAlta[0]['priority'])->toContain('Alta');

        // 2. Filtrar por prioridad baja
        $responseBaja = $this->get(route('admin.requests.index', [
            'draw' => 1,
            'priority' => 'baja'
        ]));
        $responseBaja->assertStatus(200);
        $dataBaja = $responseBaja->json('data');
        expect($dataBaja)->toHaveCount(1);
        expect($dataBaja[0]['justification'])->toContain('Solicitud sin prisa');
        expect($dataBaja[0]['priority'])->toContain('Baja');
    });

    test('guardar una solicitud prepende la prioridad al campo de justificación', function () {
        $product = Product::factory()->create(['stock' => 50]);

        $payload = [
            'destination_area' => 'Inmunología',
            'reference' => 'Uso Interno',
            'priority' => 'media',
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
        expect($savedRequest->justification)->toBe('[MEDIA] Necesitamos insumos para el análisis mensual');
        expect($savedRequest->destination_area)->toBe('Inmunología');
        expect($savedRequest->reference)->toBe('Uso Interno');
    });
});
