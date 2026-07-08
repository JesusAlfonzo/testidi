<?php

use App\Models\Product;
use App\Models\User;
use App\Models\InventoryRequest;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    
    // Crear usuario solicitante sin permisos de aprobación
    $this->solicitante = User::factory()->create();
    $this->solicitante->assignRole('Solicitante');
    
    // Crear usuario aprobador
    $this->aprobador = User::factory()->create();
    $this->aprobador->assignRole('Supervisor'); // Supervisor tiene permisos de aprobación
});

afterEach(function () {
    Carbon::setTestNow(); // Limpiar test double de Carbon
});

describe('Restricciones de Temporalidad (Middleware)', function () {
    
    test('solicitante es rechazado los lunes', function () {
        // Mockear día lunes (2026-07-06)
        Carbon::setTestNow(Carbon::parse('2026-07-06 12:00:00', 'America/Caracas'));
        
        $this->actingAs($this->solicitante);
        
        $response = $this->get(route('admin.requests.index'));
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'El módulo de solicitudes solo está disponible para solicitantes los días martes y miércoles.');
    });

    test('solicitante es aceptado los martes', function () {
        // Mockear día martes (2026-07-07)
        Carbon::setTestNow(Carbon::parse('2026-07-07 12:00:00', 'America/Caracas'));
        
        $this->actingAs($this->solicitante);
        
        $response = $this->get(route('admin.requests.index'));
        $response->assertStatus(200);
    });

    test('aprobador es aceptado los lunes', function () {
        // Mockear día lunes (2026-07-06)
        Carbon::setTestNow(Carbon::parse('2026-07-06 12:00:00', 'America/Caracas'));
        
        $this->actingAs($this->aprobador);
        
        $response = $this->get(route('admin.requests.index'));
        $response->assertStatus(200);
    });
});

describe('Restricciones de Existencia por Área', function () {
    
    test('solicitante no puede crear solicitud si ya existe una activa para su area', function () {
        // Mockear martes para poder acceder
        Carbon::setTestNow(Carbon::parse('2026-07-07 12:00:00', 'America/Caracas'));
        
        // Crear solicitud activa previa para área 'Citometria'
        InventoryRequest::create([
            'requester_id' => $this->aprobador->id,
            'status' => InventoryRequest::STATUS_PENDING,
            'destination_area' => 'Citometria',
            'justification' => 'Solicitud previa',
            'requested_at' => now(),
        ]);
        
        $product = Product::factory()->create(['stock' => 50]);
        
        $this->actingAs($this->solicitante);
        
        $payload = [
            'destination_area' => 'Citometria', // Misma área activa
            'reference' => 'Proyecto A',
            'justification' => 'Segunda solicitud denegada',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'quantity' => 5,
                ]
            ]
        ];
        
        $response = $this->post(route('admin.requests.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ya existe una solicitud activa para su área. Por favor, espere a que sea procesada.');
    });

    test('aprobador puede duplicar solicitudes activas para la misma area', function () {
        Carbon::setTestNow(Carbon::parse('2026-07-07 12:00:00', 'America/Caracas'));
        
        InventoryRequest::create([
            'requester_id' => $this->solicitante->id,
            'status' => InventoryRequest::STATUS_PENDING,
            'destination_area' => 'Citometria',
            'justification' => 'Solicitud previa del solicitante',
            'requested_at' => now(),
        ]);
        
        $product = Product::factory()->create(['stock' => 50]);
        
        $this->actingAs($this->aprobador);
        
        $payload = [
            'destination_area' => 'Citometria',
            'reference' => 'Proyecto B',
            'justification' => 'Solicitud duplicada permitida para aprobador',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'quantity' => 5,
                ]
            ]
        ];
        
        $response = $this->post(route('admin.requests.store'), $payload);
        $response->assertRedirect(route('admin.requests.index'));
        $response->assertSessionHas('success');
    });
});
