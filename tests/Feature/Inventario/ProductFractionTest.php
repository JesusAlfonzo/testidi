<?php

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductFraction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('Fractioning - Fraccionamiento / Desempaque de Productos', function () {
    test('desempaque exitoso con producto simple (sin lotes/FIFO)', function () {
        // 1. Crear productos parent y child
        $parent = Product::factory()->create([
            'name' => 'Caja de Lápices (x30)',
            'type' => 'individual',
            'track_expiry' => false,
            'requires_serial' => false,
            'stock' => 5,
            'is_active' => true,
        ]);

        $child = Product::factory()->create([
            'name' => 'Lápiz Individual',
            'type' => 'individual',
            'track_expiry' => false,
            'requires_serial' => false,
            'stock' => 10,
            'is_active' => true,
        ]);

        // 2. Crear relación de fraccionamiento
        ProductFraction::create([
            'parent_product_id' => $parent->id,
            'child_product_id' => $child->id,
            'conversion_factor' => 30,
        ]);

        // 3. Invocar endpoint para desempacar 2 cajas
        $response = $this->postJson(route('admin.products.unpack', $parent), [
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 4. Verificar stocks resultantes
        expect($parent->fresh()->stock)->toBe(3); // 5 - 2
        expect($child->fresh()->stock)->toBe(70); // 10 + (2 * 30)
    });

    test('desempaque exitoso con FIFO (traspaso y prorrateo de lotes)', function () {
        // 1. Crear productos con track_expiry = true
        $parent = Product::factory()->create([
            'name' => 'Caja de Jeringas (x100)',
            'type' => 'individual',
            'track_expiry' => true,
            'requires_serial' => false,
            'stock' => 5,
            'is_active' => true,
        ]);

        $child = Product::factory()->create([
            'name' => 'Jeringa Individual',
            'type' => 'individual',
            'track_expiry' => true,
            'requires_serial' => false,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Relación
        ProductFraction::create([
            'parent_product_id' => $parent->id,
            'child_product_id' => $child->id,
            'conversion_factor' => 100,
        ]);

        // Lote del padre
        $parentBatch = ProductBatch::create([
            'product_id' => $parent->id,
            'batch_number' => 'LOT-PARENT-001',
            'expiration_date' => now()->addDays(60),
            'quantity' => 5,
            'unit_cost' => 150.00, // 150$ la caja de 100
            'price' => 300.00,
            'currency' => 'USD',
            'tax_status' => 'exento',
            'invoice_number' => 'FAC-12345',
        ]);

        // 2. Invocar endpoint para abrir 1 caja
        $response = $this->postJson(route('admin.products.unpack', $parent), [
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 3. Verificar stocks generales
        expect($parent->fresh()->stock)->toBe(4);
        expect($parentBatch->fresh()->quantity)->toBe(4);

        expect($child->fresh()->stock)->toBe(110); // 10 + 100

        // 4. Verificar lote generado para el hijo
        // Costo prorrateado = 150.00 / 100 = 1.50
        // Precio prorrateado = 300.00 / 100 = 3.00
        $childBatch = ProductBatch::where('product_id', $child->id)->first();
        expect($childBatch)->not->toBeNull();
        expect($childBatch->quantity)->toBe(100);
        expect($childBatch->batch_number)->toBe('LOT-PARENT-001');
        expect((float) $childBatch->unit_cost)->toBe(1.50);
        expect((float) $childBatch->price)->toBe(3.00);
        expect($childBatch->invoice_number)->toBe('FAC-12345');
    });

    test('desempaque fallido por stock insuficiente del padre', function () {
        $parent = Product::factory()->create([
            'name' => 'Caja de Guantes (x50)',
            'type' => 'individual',
            'track_expiry' => false,
            'stock' => 0,
            'is_active' => true,
        ]);

        $child = Product::factory()->create([
            'name' => 'Guante Individual',
            'type' => 'individual',
            'track_expiry' => false,
            'stock' => 0,
            'is_active' => true,
        ]);

        ProductFraction::create([
            'parent_product_id' => $parent->id,
            'child_product_id' => $child->id,
            'conversion_factor' => 50,
        ]);

        $response = $this->postJson(route('admin.products.unpack', $parent), [
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        expect($response->json('message'))->toContain('Stock insuficiente');
    });

    test('desempaque fallido por falta de configuración de fraccionamiento', function () {
        $parent = Product::factory()->create([
            'name' => 'Caja de Algodón',
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.products.unpack', $parent), [
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        expect($response->json('message'))->toContain('no tiene configurado un factor de fraccionamiento');
    });

    test('crear producto con is_fraction_parent requiere child_product_id y conversion_factor', function () {
        $data = [
            'name' => 'Caja Nueva',
            'code' => 'BOX-NEW-999',
            'type' => 'individual',
            'cost' => 10,
            'price' => 20,
            'stock' => 0,
            'min_stock' => 1,
            'is_active' => true,
            'unit_id' => \App\Models\Unit::first()?->id ?? \App\Models\Unit::factory()->create()->id,
            'category_id' => \App\Models\Category::first()?->id ?? \App\Models\Category::factory()->create()->id,
            'location_id' => \App\Models\Location::first()?->id ?? \App\Models\Location::factory()->create()->id,
            'is_fraction_parent' => true,
        ];

        $response = $this->post(route('admin.products.store'), $data);
        $response->assertSessionHasErrors(['child_product_id', 'conversion_factor']);
    });

    test('crear producto con is_fraction_parent y valores correctos guarda relacion', function () {
        $child = Product::factory()->create([
            'unit_id' => \App\Models\Unit::first()?->id ?? \App\Models\Unit::factory()->create()->id,
            'category_id' => \App\Models\Category::first()?->id ?? \App\Models\Category::factory()->create()->id,
            'location_id' => \App\Models\Location::first()?->id ?? \App\Models\Location::factory()->create()->id,
        ]);

        $data = [
            'name' => 'Caja Nueva Ok',
            'code' => 'BOX-NEW-OK',
            'type' => 'individual',
            'cost' => 10,
            'price' => 20,
            'stock' => 0,
            'min_stock' => 1,
            'is_active' => true,
            'unit_id' => $child->unit_id,
            'category_id' => $child->category_id,
            'location_id' => $child->location_id,
            'is_fraction_parent' => true,
            'child_product_id' => $child->id,
            'conversion_factor' => 24,
        ];

        $response = $this->post(route('admin.products.store'), $data);
        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('code', 'BOX-NEW-OK')->first();
        expect($product)->not->toBeNull();
        expect($product->isFractionParent())->toBeTrue();
        expect($product->childFraction->child_product_id)->toBe($child->id);
        expect($product->childFraction->conversion_factor)->toBe(24);
    });

    test('actualizar producto eliminando relacion de fraccionamiento', function () {
        $parent = Product::factory()->create([
            'unit_id' => \App\Models\Unit::first()?->id ?? \App\Models\Unit::factory()->create()->id,
            'category_id' => \App\Models\Category::first()?->id ?? \App\Models\Category::factory()->create()->id,
            'location_id' => \App\Models\Location::first()?->id ?? \App\Models\Location::factory()->create()->id,
        ]);
        $child = Product::factory()->create([
            'unit_id' => $parent->unit_id,
            'category_id' => $parent->category_id,
            'location_id' => $parent->location_id,
        ]);

        ProductFraction::create([
            'parent_product_id' => $parent->id,
            'child_product_id' => $child->id,
            'conversion_factor' => 50,
        ]);

        expect($parent->isFractionParent())->toBeTrue();

        $data = [
            'name' => $parent->name,
            'code' => $parent->code,
            'type' => $parent->type,
            'cost' => $parent->cost,
            'price' => $parent->price,
            'stock' => $parent->stock,
            'min_stock' => $parent->min_stock,
            'is_active' => $parent->is_active,
            'unit_id' => $parent->unit_id,
            'category_id' => $parent->category_id,
            'location_id' => $parent->location_id,
            'is_fraction_parent' => false,
        ];

        $response = $this->put(route('admin.products.update', $parent), $data);
        $response->assertRedirect(route('admin.products.index'));

        expect($parent->fresh()->isFractionParent())->toBeFalse();
    });

    test('validar que un producto no puede ser su propio hijo en edicion', function () {
        $product = Product::factory()->create([
            'unit_id' => \App\Models\Unit::first()?->id ?? \App\Models\Unit::factory()->create()->id,
            'category_id' => \App\Models\Category::first()?->id ?? \App\Models\Category::factory()->create()->id,
            'location_id' => \App\Models\Location::first()?->id ?? \App\Models\Location::factory()->create()->id,
        ]);

        $data = [
            'name' => $product->name,
            'code' => $product->code,
            'type' => $product->type,
            'cost' => $product->cost,
            'price' => $product->price,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'is_active' => $product->is_active,
            'unit_id' => $product->unit_id,
            'category_id' => $product->category_id,
            'location_id' => $product->location_id,
            'is_fraction_parent' => true,
            'child_product_id' => $product->id,
            'conversion_factor' => 10,
        ];

        $response = $this->put(route('admin.products.update', $product), $data);
        $response->assertSessionHasErrors(['child_product_id']);
    });
});
