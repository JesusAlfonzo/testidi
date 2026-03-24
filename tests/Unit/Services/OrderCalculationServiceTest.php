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
