<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'date_issued' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date_issued',
            'delivery_address' => 'nullable|string|max:500',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'terms' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.min' => 'Debe agregar al menos un producto.',
            'delivery_date.after_or_equal' => 'La fecha de entrega debe ser igual o posterior a la fecha de emisión.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'items.*.unit_cost.min' => 'El costo unitario no puede ser negativo.',
        ];
    }
}
