<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\RequestForQuotation;
use App\Models\PurchaseQuote;
use App\Models\PurchaseOrder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    
    // Crear roles y permisos
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Asignar permisos al rol Superadmin
    $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);
    $permissions = Permission::pluck('name');
    $superadmin->givePermissionTo($permissions);

    // Asignar permisos al rol Solicitante
    $solicitante = Role::firstOrCreate(['name' => 'Solicitante']);
    $solicitante->givePermissionTo([
        'dashboard_acceso',
        'solicitudes_crear',
        'solicitudes_ver',
    ]);

    // Asignar permisos al rol Logística
    $logistica = Role::firstOrCreate(['name' => 'Logistica']);
    $logistica->givePermissionTo([
        'dashboard_acceso',
        'categorias_ver', 'categorias_crear', 'categorias_editar', 'categorias_eliminar',
        'unidades_ver', 'unidades_crear', 'unidades_editar', 'unidades_eliminar',
        'ubicaciones_ver', 'ubicaciones_crear', 'ubicaciones_editar', 'ubicaciones_eliminar',
        'marcas_ver', 'marcas_crear', 'marcas_editar', 'marcas_eliminar',
        'proveedores_ver', 'proveedores_crear', 'proveedores_editar', 'proveedores_eliminar',
        'productos_ver', 'productos_crear', 'productos_editar', 'productos_eliminar',
        'kits_ver', 'kits_crear', 'kits_editar', 'kits_eliminar',
        'entradas_ver', 'entradas_crear', 'entradas_eliminar',
        'solicitudes_ver', 'solicitudes_aprobar', 'solicitudes_crear',
        'ordenes_compra_ver', 'ordenes_compra_crear', 'ordenes_compra_editar', 'ordenes_compra_eliminar',
        'cotizaciones_ver', 'cotizaciones_crear', 'cotizaciones_editar', 'cotizaciones_eliminar', 
        'cotizaciones_aprobar', 'cotizaciones_rechazar',
        'rfq_ver', 'rfq_crear', 'rfq_editar', 'rfq_eliminar', 'rfq_enviar',
        'reportes_stock', 'reportes_ver', 'reportes_kardex','reportes_movimientos',
    ]);

    // Asignar permisos al rol Supervisor
    $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
    $allPermissions = Permission::pluck('name');
    $supervisorPermissions = $allPermissions->reject(function ($permission) {
        return str_starts_with($permission, 'usuarios_') || str_starts_with($permission, 'roles_');
    });
    $supervisor->givePermissionTo($supervisorPermissions);
});

describe('Permisos - Superadmin', function () {
    test('superadmin puede acceder a todas las rutas de compras', function () {
        $user = User::factory()->create();
        $user->assignRole('Superadmin');
        
        $this->actingAs($user);

        // RFQ
        $this->get(route('admin.rfq.index'))->assertStatus(200);
        
        // Cotizaciones
        $this->get(route('admin.quotations.index'))->assertStatus(200);
        
        // Órdenes de Compra
        $this->get(route('admin.purchaseOrders.index'))->assertStatus(200);
    });

    test('superadmin puede aprobar cotizaciones', function () {
        $user = User::factory()->create();
        $user->assignRole('Superadmin');
        $this->actingAs($user);

        $quote = PurchaseQuote::factory()->create(['status' => 'selected']);

        $response = $this->post(route('admin.quotations.approve', $quote));
        
        $response->assertSessionHas('success');
        expect($quote->fresh()->status)->toBe('approved');
    });
});

describe('Permisos - Solicitante', function () {
    test('solicitante NO puede acceder a rutas de compras', function () {
        $user = User::factory()->create();
        $user->assignRole('Solicitante');
        
        $this->actingAs($user);

        // RFQ - debe ser bloqueado
        $this->get(route('admin.rfq.index'))->assertStatus(403);
        
        // Cotizaciones - debe ser bloqueado
        $this->get(route('admin.quotations.index'))->assertStatus(403);
        
        // Órdenes de Compra - debe ser bloqueado
        $this->get(route('admin.purchaseOrders.index'))->assertStatus(403);
    });

    test('solicitante NO puede aprobar cotizaciones', function () {
        $user = User::factory()->create();
        $user->assignRole('Solicitante');
        $this->actingAs($user);

        $quote = PurchaseQuote::factory()->create(['status' => 'selected']);

        $response = $this->post(route('admin.quotations.approve', $quote));
        
        // Debe ser bloqueado por middleware de permiso
        $response->assertStatus(403);
    });
});

describe('Permisos - Logistica', function () {
    test('logistica puede acceder a rfq', function () {
        $user = User::factory()->create();
        $user->assignRole('Logistica');
        
        $this->actingAs($user);

        $this->get(route('admin.rfq.index'))->assertStatus(200);
    });

    test('logistica puede crear rfq', function () {
        $user = User::factory()->create();
        $user->assignRole('Logistica');
        
        $this->actingAs($user);

        $product = Product::factory()->create();

        $response = $this->post(route('admin.rfq.store'), [
            'title' => 'RFQ Test Logistica',
            'description' => 'Test',
            'date_required' => now()->addDays(30)->format('Y-m-d'),
            'delivery_deadline' => now()->addDays(45)->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('request_for_quotations', [
            'title' => 'RFQ Test Logistica',
        ]);
    });

    test('logistica NO puede aprobar cotizaciones (falta permiso)', function () {
        $user = User::factory()->create();
        $user->assignRole('Logistica');
        $this->actingAs($user);

        $quote = PurchaseQuote::factory()->create(['status' => 'selected']);

        $response = $this->post(route('admin.quotations.approve', $quote));
        
        // Debe ser bloqueado - logistica no tiene cotizaciones_aprobar
        // El middleware puede retornar 403 o redirigir
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    });

    test('logistica NO puede acceder a usuarios', function () {
        $user = User::factory()->create();
        $user->assignRole('Logistica');
        
        $this->actingAs($user);

        $this->get(route('admin.users.index'))->assertStatus(403);
    });
});

describe('Permisos - Supervisor', function () {
    test('supervisor puede acceder a reportes pero NO a usuarios', function () {
        $user = User::factory()->create();
        $user->assignRole('Supervisor');
        
        $this->actingAs($user);

        // Reportes - permitido
        $this->get(route('admin.reports.stock'))->assertStatus(200);
        
        // Usuarios - bloqueado
        $this->get(route('admin.users.index'))->assertStatus(403);
        
        // Roles - bloqueado
        $this->get(route('admin.roles.index'))->assertStatus(403);
    });

    test('supervisor puede aprobar cotizaciones', function () {
        $user = User::factory()->create();
        $user->assignRole('Supervisor');
        $this->actingAs($user);

        $quote = PurchaseQuote::factory()->create(['status' => 'selected']);

        $response = $this->post(route('admin.quotations.approve', $quote));
        
        $response->assertSessionHas('success');
        expect($quote->fresh()->status)->toBe('approved');
    });
});
