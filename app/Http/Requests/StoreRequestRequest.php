<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules()
    {
        return [
            'justification' => ['required', 'string', 'max:500'],
            'destination_area' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                // 🔑 CLAVE: La regla 'exists' ahora debe apuntar a la tabla correcta si aplica.
                // En este caso, solo necesitamos que exista el producto.
                'exists:products,id'
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'items.min' => 'Debe agregar al menos un producto a la solicitud.',
            'items.*.quantity.min' => 'La cantidad solicitada debe ser al menos 1.',
        ];
    }
}
