<?php

use App\Models\Product;
use App\Models\Kit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('Kit - Kits de Productos', function () {
    test('crear kit con componentes', function () {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $kit = Kit::create([
            'code' => 'KIT-001',
            'name' => 'Kit Inicial',
            'is_active' => true,
        ]);

        $kit->components()->attach($product1->id, ['quantity_required' => 2]);
        $kit->components()->attach($product2->id, ['quantity_required' => 1]);

        expect($kit->fresh()->components)->toHaveCount(2);
    });

    test('kit muestra componentes correctamente', function () {
        $product1 = Product::factory()->create(['name' => 'Producto A']);
        $product2 = Product::factory()->create(['name' => 'Producto B']);

        $kit = Kit::create([
            'code' => 'KIT-002',
            'name' => 'Kit Dos',
            'is_active' => true,
        ]);
        
        $kit->components()->attach($product1->id, ['quantity_required' => 3]);
        $kit->components()->attach($product2->id, ['quantity_required' => 2]);

        $kit->load('components');

        expect($kit->components)->toHaveCount(2);
        expect($kit->components->first()->pivot->quantity_required)->toBe(3);
    });

    test('kit inactivo no aparece en solicitudes', function () {
        $kit = Kit::create([
            'code' => 'KIT-005',
            'name' => 'Kit Inactivo',
            'is_active' => false,
        ]);

        $activeKits = Kit::where('is_active', true)->get();

        expect($activeKits->contains($kit))->toBeFalse();
    });

    test('kit activo aparece en solicitudes', function () {
        $kit = Kit::create([
            'code' => 'KIT-006',
            'name' => 'Kit Activo',
            'is_active' => true,
        ]);

        $activeKits = Kit::where('is_active', true)->get();

        expect($activeKits->contains($kit))->toBeTrue();
    });
});
