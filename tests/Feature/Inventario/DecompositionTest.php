<?php

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('Superadmin');
    $this->actingAs($this->user);
});

describe('Decomposition - Descomposición de Kits', function () {
    test('descomposición de kit exitosa con prorrateo contable y seriales null', function () {
        // 1. Crear componentes individuales
        $comp1 = Product::factory()->create([
            'name' => 'Componente A',
            'type' => 'individual',
            'requires_serial' => false,
            'cost' => 0,
            'price' => 0,
            'stock' => 0,
        ]);
        $comp2 = Product::factory()->create([
            'name' => 'Componente B',
            'type' => 'individual',
            'requires_serial' => false,
            'cost' => 0,
            'price' => 0,
            'stock' => 0,
        ]);

        // 2. Crear el Kit padre
        $kit = Product::factory()->create([
            'name' => 'Kit Compuesto',
            'type' => 'composite_kit',
            'is_kit' => true,
            'requires_serial' => false,
            'cost' => 0,
            'price' => 0,
            'stock' => 5,
        ]);

        // Asociar componentes al Kit
        $kit->components()->attach($comp1->id, ['quantity' => 2]);
        $kit->components()->attach($comp2->id, ['quantity' => 3]);

        // 3. Crear lote del Kit con costo y precio
        $kitBatch = ProductBatch::create([
            'product_id' => $kit->id,
            'batch_number' => 'LOT-KIT-001',
            'expiration_date' => now()->addDays(30),
            'quantity' => 5,
            'unit_cost' => 12.00, // Costo total del kit
            'price' => 24.00,     // Precio total del kit
            'currency' => 'USD',
            'tax_status' => 'exento',
            'serial_number' => 'SERIAL-KIT-PADRE',
        ]);

        // 4. Invocar endpoint de descomposición (descomponer 2 unidades del Kit)
        $response = $this->postJson(route('admin.products.decompose', $kit), [
            'batch_id' => $kitBatch->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 5. Verificar stock resultante del Kit
        expect($kit->fresh()->stock)->toBe(3);
        expect($kitBatch->fresh()->quantity)->toBe(3);

        // 6. Verificar lotes de componentes resultantes
        // Comp1: quantity should be 2 * 2 = 4
        // Prorated unit cost = 12.00 / 2 = 6.00
        // Prorated price = 24.00 / 2 = 12.00
        $comp1Batch = ProductBatch::where('product_id', $comp1->id)->first();
        expect($comp1Batch)->not->toBeNull();
        expect($comp1Batch->quantity)->toBe(4);
        expect((float) $comp1Batch->unit_cost)->toBe(6.00);
        expect((float) $comp1Batch->price)->toBe(12.00);
        expect($comp1Batch->serial_number)->toBeNull(); // No debe heredar el serial del padre

        // Comp2: quantity should be 2 * 3 = 6
        // Prorated unit cost = 12.00 / 3 = 4.00
        // Prorated price = 24.00 / 3 = 8.00
        $comp2Batch = ProductBatch::where('product_id', $comp2->id)->first();
        expect($comp2Batch)->not->toBeNull();
        expect($comp2Batch->quantity)->toBe(6);
        expect((float) $comp2Batch->unit_cost)->toBe(4.00);
        expect((float) $comp2Batch->price)->toBe(8.00);
        expect($comp2Batch->serial_number)->toBeNull();
    });

    test('descomposición de kit exige seriales obligatorios para componentes que los requieran', function () {
        $compSerial = Product::factory()->create([
            'name' => 'Componente Serializado',
            'type' => 'individual',
            'requires_serial' => true,
            'stock' => 0,
        ]);

        $kit = Product::factory()->create([
            'name' => 'Kit Especial',
            'type' => 'composite_kit',
            'is_kit' => true,
            'stock' => 1,
        ]);

        $kit->components()->attach($compSerial->id, ['quantity' => 2]);

        $kitBatch = ProductBatch::create([
            'product_id' => $kit->id,
            'batch_number' => 'LOT-KIT-002',
            'expiration_date' => now()->addDays(30),
            'quantity' => 1,
            'unit_cost' => 100.00,
            'price' => 200.00,
        ]);

        // Descomponer 1 kit de 2 componentes serializados -> requiere 2 seriales.
        // Si no enviamos seriales: debe fallar con 422
        $response = $this->postJson(route('admin.products.decompose', $kit), [
            'batch_id' => $kitBatch->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonFragment([
            'message' => "El componente '{$compSerial->name}' requiere número de serie. Por favor, ingrese 2 seriales únicos."
        ]);

        // Si enviamos seriales insuficientes: debe fallar con 422
        $response = $this->postJson(route('admin.products.decompose', $kit), [
            'batch_id' => $kitBatch->id,
            'quantity' => 1,
            'serials' => [
                $compSerial->id => ['SERIAL1']
            ]
        ]);
        $response->assertStatus(422);

        // Si enviamos seriales duplicados: debe fallar con 422
        $response = $this->postJson(route('admin.products.decompose', $kit), [
            'batch_id' => $kitBatch->id,
            'quantity' => 1,
            'serials' => [
                $compSerial->id => ['SERIAL1', 'SERIAL1']
            ]
        ]);
        $response->assertStatus(422);

        // Si enviamos seriales que ya existen en el inventario: debe fallar con 422
        ProductBatch::create([
            'product_id' => $compSerial->id,
            'batch_number' => 'LOT-EXISTING',
            'quantity' => 1,
            'serial_number' => 'SERIAL-EXISTENTE',
        ]);

        $response = $this->postJson(route('admin.products.decompose', $kit), [
            'batch_id' => $kitBatch->id,
            'quantity' => 1,
            'serials' => [
                $compSerial->id => ['SERIAL1', 'SERIAL-EXISTENTE']
            ]
        ]);
        $response->assertStatus(422);

        // Si enviamos seriales válidos: debe tener éxito y crear lotes individuales de cantidad 1
        $response = $this->postJson(route('admin.products.decompose', $kit), [
            'batch_id' => $kitBatch->id,
            'quantity' => 1,
            'serials' => [
                $compSerial->id => ['SERIAL-NUEVO-1', 'SERIAL-NUEVO-2']
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Debe haber 2 lotes creados para el componente serializado
        $compBatches = ProductBatch::where('product_id', $compSerial->id)
            ->whereIn('serial_number', ['SERIAL-NUEVO-1', 'SERIAL-NUEVO-2'])
            ->get();

        expect($compBatches)->toHaveCount(2);
        expect($compBatches->first()->quantity)->toBe(1);
        expect((float) $compBatches->first()->unit_cost)->toBe(50.00); // 100 / 2
    });
});
