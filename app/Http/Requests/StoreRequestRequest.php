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