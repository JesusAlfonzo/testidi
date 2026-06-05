<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $dashboardAcceso = Permission::firstOrCreate(['name' => 'dashboard_acceso']);

    $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);
    $superadmin->givePermissionTo(Permission::all());

    $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
    $supervisor->givePermissionTo($dashboardAcceso);

    $logistica = Role::firstOrCreate(['name' => 'Logistica']);
    $logistica->givePermissionTo($dashboardAcceso);

    $solicitante = Role::firstOrCreate(['name' => 'Solicitante']);
    $solicitante->givePermissionTo($dashboardAcceso);
});

describe('Dashboard Access Control and Rendering', function () {

    test('superadmin can access admin dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole('Superadmin');

        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboards.admin');
        $response->assertViewHas([
            'totalProducts',
            'lowStockCount',
            'chartApproved',
            'chartRejected',
            'chartPending',
            'expiringProducts',
            'expiringCount',
            'monthlyStats',
            'recentActivity'
        ]);
    });

    test('supervisor can access salidas dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole('Supervisor');

        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboards.salidas');
        $response->assertViewHas([
            'pendingRequestsCount',
            'expiringProducts',
            'criticalRequests'
        ]);
    });

    test('logistica can access compras dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole('Logistica');

        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboards.compras');
        $response->assertViewHas([
            'activeRfqsCount',
            'pendingPosCount',
            'lowStockProducts'
        ]);
    });

    test('solicitante can access empleado dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole('Solicitante');

        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboards.empleado');
        $response->assertViewHas([
            'myRequestsCount',
            'myApprovedCount',
            'myPendingCount',
            'myRejectedCount',
            'myRecentRequests'
        ]);
    });

});
