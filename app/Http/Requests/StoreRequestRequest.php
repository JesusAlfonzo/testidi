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

            // 2. Validación del Array de Ítems
            'items' => ['required', 'array', 'min:1'],

            // 3. Validación de cada Ítem dentro del Array
            'items.*.item_type' => ['required', 'in:product,kit'],
            
            // Validación condicional: product_id es requerido si item_type es 'product'
            'items.*.product_id' => [
                'nullable',
                'required_if:items.*.item_type,product',
                'exists:products,id'
            ],

            // Validación condicional: kit_id es requerido si item_type es 'kit'
            'items.*.kit_id' => [
                'nullable',
                'required_if:items.*.item_type,kit',
                'exists:kits,id'
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
            
            $kitIds = collect($items)->where('item_type', 'kit')->pluck('kit_id')->filter()->toArray();
            $kitsDict = \App\Models\Kit::with('components')->whereIn('id', $kitIds)->get()->keyBy('id');
            
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
                
                if (($item['item_type'] ?? '') === 'kit' && !empty($item['kit_id'])) {
                    $kit = $kitsDict->get($item['kit_id']);
                    if (!$kit) {
                        $validator->errors()->add("items.{$index}.kit_id", 'El kit no existe.');
                    } elseif (!$kit->is_active) {
                        $validator->errors()->add("items.{$index}.kit_id", 'El kit está inactivo.');
                    } else {
                        $requestedQty = $item['quantity'] ?? 0;
                        $canAssemble = true;
                        $insufficientComponents = [];
                        
                        foreach ($kit->components as $component) {
                            $required = $component->pivot->quantity_required * $requestedQty;
                            if ($component->stock < $required) {
                                $canAssemble = false;
                                $insufficientComponents[] = $component->name;
                            }
                        }
                        
                        if (!$canAssemble) {
                            $validator->errors()->add("items.{$index}.quantity", "No hay stock suficiente para ensamblar el kit. Faltan: " . implode(', ', $insufficientComponents));
                        }
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
            'items.*.product_id.required_if' => 'Debe seleccionar un producto válido.',
            'items.*.kit_id.required_if' => 'Debe seleccionar un kit válido.',
        ];
    }
}