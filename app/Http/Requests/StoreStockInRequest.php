<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockInRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable'],
            'purchase_order_id' => ['nullable'],

            'document_type' => ['nullable', 'string', 'max:50'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'delivery_note_number' => ['nullable', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
            'items.*.batch_number' => ['nullable', 'string', 'max:50'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.serial_number' => ['nullable', 'string', 'max:100'],
            'items.*.warehouse_location' => ['nullable', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto.',
            'items.min' => 'Debe agregar al menos un producto.',
            'items.*.product_id.required' => 'Debe seleccionar un producto.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required' => 'La cantidad es requerida.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.unit_cost.required' => 'El costo unitario es requerido.',
            'entry_date.required' => 'La fecha de ingreso es requerida.',
        ];
    }
}
