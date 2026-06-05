<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Brand;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('ProductController - CRUD y creación rápida de productos duales', function () {
    test('puede guardar un producto estricto con todos sus campos', function () {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $location = Location::factory()->create();
        $brand = Brand::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'is_generic' => false,
            'code' => 'STRICT-001',
            'name' => 'Producto Estricto',
            'description' => 'Un reactivo químico estricto',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'location_id' => $location->id,
            'brand_id' => $brand->id,
            'cost' => 150.00,
            'price' => 200.00,
            'stock' => 10,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Producto registrado con éxito.');

        $this->assertDatabaseHas('products', [
            'code' => 'STRICT-001',
            'is_generic' => false,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'brand_id' => $brand->id,
        ]);
    });

    test('puede guardar un producto genérico sin category_id, location_id, ni brand_id', function () {
        $unit = Unit::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'is_generic' => true,
            'code' => 'GENERIC-001',
            'name' => 'Producto Genérico',
            'description' => 'Un lápiz común',
            'category_id' => '',
            'unit_id' => $unit->id,
            'location_id' => '',
            'brand_id' => '',
            'cost' => 1.50,
            'price' => 2.00,
            'stock' => 100,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Producto registrado con éxito.');

        $this->assertDatabaseHas('products', [
            'code' => 'GENERIC-001',
            'is_generic' => true,
            'category_id' => null,
            'location_id' => null,
            'brand_id' => null,
        ]);
    });

    test('falla al guardar producto estricto sin category_id ni location_id', function () {
        $unit = Unit::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'is_generic' => false,
            'code' => 'STRICT-FAIL',
            'name' => 'Producto Estricto Fallido',
            'unit_id' => $unit->id,
            'category_id' => '',
            'location_id' => '',
            'cost' => 10,
            'price' => 15,
            'stock' => 10,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['category_id', 'location_id']);
    });

    test('puede actualizar un producto a genérico y limpiar sus FKs', function () {
        $product = Product::factory()->create([
            'is_generic' => false,
        ]);

        $response = $this->put(route('admin.products.update', $product), [
            'is_generic' => true,
            'code' => $product->code,
            'name' => 'Producto Genérico Modificado',
            'unit_id' => $product->unit_id,
            'category_id' => '',
            'location_id' => '',
            'brand_id' => '',
            'cost' => 5.00,
            'price' => 10.00,
            'stock' => 10,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Producto actualizado con éxito.');

        $product->refresh();
        expect($product->is_generic)->toBeTrue();
        expect($product->category_id)->toBeNull();
        expect($product->location_id)->toBeNull();
        expect($product->brand_id)->toBeNull();
    });

    test('quickStore crea un producto genérico por ajax sin category_id, location_id, ni brand_id', function () {
        $unit = Unit::factory()->create();

        $response = $this->postJson(route('admin.products.quick-store'), [
            'is_generic' => true,
            'code' => 'QUICK-GEN-001',
            'name' => 'Lápiz Rápido',
            'unit_id' => $unit->id,
            'category_id' => '',
            'location_id' => '',
            'brand_id' => '',
            'cost' => 0.50,
            'price' => 1.00,
            'min_stock' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('products', [
            'code' => 'QUICK-GEN-001',
            'is_generic' => true,
            'category_id' => null,
            'location_id' => null,
            'brand_id' => null,
            'created_on_the_fly' => true,
        ]);
    });

    test('quickStoreKit crea un kit genérico por ajax sin category_id ni location_id', function () {
        $unit = Unit::factory()->create();
        $childProduct = Product::factory()->create();

        $response = $this->postJson(route('admin.products.quick-store-kit'), [
            'is_generic' => true,
            'code' => 'QUICK-KIT-001',
            'name' => 'Kit Genérico Rápido',
            'unit_id' => $unit->id,
            'category_id' => '',
            'location_id' => '',
            'brand_id' => '',
            'cost' => 10.00,
            'components' => [
                [
                    'child_id' => $childProduct->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('products', [
            'code' => 'QUICK-KIT-001',
            'is_generic' => true,
            'category_id' => null,
            'location_id' => null,
            'brand_id' => null,
            'is_kit' => true,
            'created_on_the_fly' => true,
        ]);
    });
});
