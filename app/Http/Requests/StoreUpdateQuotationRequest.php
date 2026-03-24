<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateQuotationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'request_for_quotation_id' => 'nullable|exists:request_for_quotations,id',
            'supplier_type' => 'required|in:registered,temp',
            'supplier_id' => 'required_if:supplier_type,registered|nullable|exists:suppliers,id',
            'temp_supplier_name' => 'required_if:supplier_type,temp|string|max:255',
            'temp_supplier_email' => 'nullable|email|max:255',
            'temp_supplier_phone' => 'nullable|string|max:50',
            'issue_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:issue_date',
            'delivery_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required_if' => 'Debe seleccionar un proveedor.',
            'temp_supplier_name.required_if' => 'El nombre del proveedor es obligatorio.',
            'valid_until.after_or_equal' => 'La fecha de validez debe ser igual o posterior a la fecha de emisión.',
            'items.min' => 'Debe agregar al menos un producto.',
        ];
    }
}
