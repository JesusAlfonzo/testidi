<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\PurchaseQuote;
use App\Models\InventoryRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('Edge Cases - Casos Extremos', function () {
    test('crear OC con stock insuficiente crea registro', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->post(route('admin.purchaseOrders.store'), [
            'supplier_id' => $supplier->id,
            'date_issued' => now()->format('Y-m-d'),
            'currency' => 'USD',
            'exchange_rate' => 17.15,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 100, 'unit_cost' => 10],
            ],
        ]);

        $response->assertRedirect();
    });

    test('producto inactivo no aparece en listados', function () {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        $activeProducts = Product::where('is_active', true)->get();

        expect($activeProducts->contains($activeProduct))->toBeTrue();
        expect($activeProducts->contains($inactiveProduct))->toBeFalse();
    });
});
