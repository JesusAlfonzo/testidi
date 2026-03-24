<?php

use App\Http\Requests\StoreUpdatePurchaseOrderRequest;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->supplier = Supplier::factory()->create();
    $this->product = Product::factory()->create();
});

describe('StoreUpdatePurchaseOrderRequest - Validaciones', function () {
    test('valida campos requeridos', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('supplier_id'))->toBeTrue();
        expect($validator->errors()->has('date_issued'))->toBeTrue();
        expect($validator->errors()->has('currency'))->toBeTrue();
        expect($validator->errors()->has('exchange_rate'))->toBeTrue();
        expect($validator->errors()->has('items'))->toBeTrue();
    });

    test('valida supplier_id existe', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'supplier_id' => 99999,
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('supplier_id'))->toBeTrue();
    });

    test('acepta supplier_id valido', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'supplier_id' => $this->supplier->id,
        ], $request->rules());
        
        expect($validator->errors()->has('supplier_id'))->toBeFalse();
    });

    test('valida items minimo uno', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'items' => [],
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('items'))->toBeTrue();
    });

    test('valida estructura de items', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'items' => [['product_id' => '', 'quantity' => '', 'unit_cost' => '']],
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('items.0.product_id'))->toBeTrue();
        expect($validator->errors()->has('items.0.quantity'))->toBeTrue();
    });

    test('valida quantity minimo 1', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'items' => [['product_id' => 1, 'quantity' => 0, 'unit_cost' => 10]],
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('items.0.quantity'))->toBeTrue();
    });

    test('valida exchange_rate no negativo', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'exchange_rate' => -1,
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
    });

    test('valida delivery_date despues de date_issued', function () {
        $request = new StoreUpdatePurchaseOrderRequest();
        
        $validator = Validator::make([
            'date_issued' => '2024-03-01',
            'delivery_date' => '2024-02-01',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('delivery_date'))->toBeTrue();
    });
});
