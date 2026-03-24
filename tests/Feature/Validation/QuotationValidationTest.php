<?php

use App\Http\Requests\StoreUpdateQuotationRequest;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->supplier = Supplier::factory()->create();
    $this->product = Product::factory()->create();
});

describe('StoreUpdateQuotationRequest - Validaciones', function () {
    test('valida campos requeridos', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('supplier_type'))->toBeTrue();
        expect($validator->errors()->has('issue_date'))->toBeTrue();
        expect($validator->errors()->has('valid_until'))->toBeTrue();
        expect($validator->errors()->has('currency'))->toBeTrue();
    });

    test('valida supplier_type sea registered o temp', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'supplier_type' => 'invalid',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('supplier_type'))->toBeTrue();
    });

    test('proveedor registrado requiere supplier_id', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'supplier_type' => 'registered',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('supplier_id'))->toBeTrue();
    });

    test('proveedor temporal requiere temp_supplier_name', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'supplier_type' => 'temp',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('temp_supplier_name'))->toBeTrue();
    });

    test('acepta proveedor registrado valido', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'supplier_type' => 'registered',
            'supplier_id' => $this->supplier->id,
        ], $request->rules());
        
        expect($validator->errors()->has('supplier_id'))->toBeFalse();
    });

    test('valida valid_until despues de issue_date', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'issue_date' => '2024-03-01',
            'valid_until' => '2024-02-01',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('valid_until'))->toBeTrue();
    });

    test('valida email de proveedor temporal', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'supplier_type' => 'temp',
            'temp_supplier_name' => 'Test',
            'temp_supplier_email' => 'not-an-email',
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('temp_supplier_email'))->toBeTrue();
    });

    test('valida items minimo uno', function () {
        $request = new StoreUpdateQuotationRequest();
        
        $validator = Validator::make([
            'items' => [],
        ], $request->rules());
        
        expect($validator->fails())->toBeTrue();
    });
});
