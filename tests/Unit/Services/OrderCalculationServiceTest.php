<?php

use App\Services\OrderCalculationService;

it('calcula totales correctamente en USD', function () {
    $service = new OrderCalculationService();
    $items = [
        ['product_id' => 1, 'quantity' => 10, 'unit_cost' => 100.00],
        ['product_id' => 2, 'quantity' => 5, 'unit_cost' => 50.00],
    ];

    $result = $service->calculate($items, 'USD', 17.15);

    expect($result['subtotal'])->toBe(1250.00);
    expect($result['exchange_rate'])->toBe(17.15);
});

it('calcula totales correctamente en Bs', function () {
    $service = new OrderCalculationService();
    $items = [
        ['product_id' => 1, 'quantity' => 10, 'unit_cost' => 100.00],
    ];

    $result = $service->calculate($items, 'Bs', 1);

    expect($result['subtotal'])->toBe(1000.00);
    expect($result['exchange_rate'])->toBe(1);
});

it('convierte USD a Bs correctamente', function () {
    $service = new OrderCalculationService();
    $result = $service->calculateItemEquivalentBs(100.00, 'USD', 17.15);
    expect(round($result, 2))->toBe(1715.00);
});

it('retorna mismo valor cuando es Bs', function () {
    $service = new OrderCalculationService();
    $result = $service->calculateItemEquivalentBs(100.00, 'Bs', 1);
    expect($result)->toBe(100.00);
});

it('calcula totales con algunos items exentos de IVA', function () {
    $service = new OrderCalculationService();
    $items = [
        ['product_id' => 1, 'quantity' => 2, 'unit_cost' => 100.00, 'is_exempt' => true],
        ['product_id' => 2, 'quantity' => 3, 'unit_cost' => 50.00, 'is_exempt' => false],
    ];

    $result = $service->calculate($items, 'USD', 10.00);

    expect($result['subtotal'])->toBe(350.00);
    expect($result['tax_amount'])->toBe(24.00);
    expect($result['total'])->toBe(374.00);
    expect($result['subtotal_bs'])->toBe(3500.00);
    expect($result['tax_amount_bs'])->toBe(240.00);
    expect($result['total_bs'])->toBe(3740.00);
});
