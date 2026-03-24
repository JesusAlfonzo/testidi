<?php

use App\Http\Requests\StoreUpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->product = Product::factory()->create();
});

describe('StoreUpdateProductRequest - Validaciones', function () {
    test('valida campos requeridos', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('category_id'))->toBeTrue();
        expect($validator->errors()->has('unit_id'))->toBeTrue();
        expect($validator->errors()->has('location_id'))->toBeTrue();
        expect($validator->errors()->has('code'))->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    test('valida precio mayor o igual a costo', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'cost' => 100,
            'price' => 50,
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('price'))->toBeTrue();
    });

    test('acepta precio igual a costo', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'cost' => 100,
            'price' => 100,
        ], $request->rules());
        
        expect($validator->errors()->has('price'))->toBeFalse();
    });

    test('acepta precio mayor a costo', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'cost' => 100,
            'price' => 150,
        ], $request->rules());
        
        expect($validator->errors()->has('price'))->toBeFalse();
    });

    test('valida código único', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'code' => $this->product->code,
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
    });

    test('valida stock no negativo', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'stock' => -1,
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('stock'))->toBeTrue();
    });

    test('valida min_stock no negativo', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'min_stock' => -5,
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
    });

    test('valida is_active sea booleano', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'is_active' => 'yes',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
    });

    test('acepta valores booleanos válidos', function () {
        $request = new StoreUpdateProductRequest();
        
        $validator = Validator::make([
            'is_active' => true,
        ], $request->rules());
        
        expect($validator->errors()->has('is_active'))->toBeFalse();
    });
});
