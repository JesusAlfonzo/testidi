<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización real la maneja el middleware/gate en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // 1. Campos de la Cabecera
            'reference' => ['required', 'string', 'max:255'],
            'justification' => ['required', 'string', 'min:5', 'max:500'],
            'destination_area' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'in:alta,media,baja'],

            // 2. Validación del Array de Ítems
            'items' => ['required', 'array', 'min:1'],

            // 3. Validación de cada Ítem dentro del Array
            'items.*.item_type' => ['required', 'in:product'],
            
            'items.*.product_id' => [
                'required',
                'exists:products,id'
            ],

            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Validaciones adicionales que requieren acceso a la DB
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $validator->getData()['items'] ?? [];
            
            foreach ($items as $index => $item) {
                if (($item['item_type'] ?? '') === 'product' && !empty($item['product_id'])) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if (!$product) {
                        $validator->errors()->add("items.{$index}.product_id", 'El producto no existe.');
                    } elseif (!$product->is_active) {
                        $validator->errors()->add("items.{$index}.product_id", 'El producto está inactivo.');
                    } elseif (($item['quantity'] ?? 0) > $product->stock) {
                        $validator->errors()->add("items.{$index}.quantity", "Stock insuficiente. Stock actual: {$product->stock}");
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'items.required' => 'Debe agregar al menos un ítem a la solicitud.',
            'items.min' => 'Debe agregar al menos un ítem a la solicitud.',
            'items.*.product_id.required' => 'Debe seleccionar un producto válido.',
        ];
    }
}