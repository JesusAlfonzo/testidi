<?php
namespace App\Http\Requests;

use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStockInRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable'],
            'purchase_order_id' => ['nullable'],

            'document_type' => ['required', 'string', 'max:50'],
            'document_number' => ['required', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'delivery_note_number' => ['nullable', 'string', 'max:50'],
            'reason' => ['required', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
            'items.*.batch_number' => ['required', 'string', 'max:50'],
            'items.*.expiration_date' => ['nullable', 'date'],
            'items.*.serial_number' => ['nullable', 'string', 'max:100'],
            'items.*.warehouse_location' => ['required', 'string', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.status' => ['nullable', 'in:received,rejected'],
            'items.*.rejection_reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validar fecha de vencimiento para productos perecederos
            foreach ($this->input('items', []) as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $expirationDate = $item['expiration_date'] ?? null;

                if ($productId) {
                    $product = \App\Models\Product::find($productId);
                    if ($product && $product->is_perishable) {
                        if (empty($expirationDate)) {
                            $validator->errors()->add(
                                "items.$index.expiration_date",
                                "La fecha de vencimiento es obligatoria para productos perecederos."
                            );
                        }
                    }
                }
            }

            if (!$this->filled('purchase_order_id')) {
                return;
            }

            $poItems = PurchaseOrderItem::where('purchase_order_id', $this->input('purchase_order_id'))
                ->get()
                ->keyBy('id');

            foreach ($this->input('items', []) as $index => $item) {
                $poItemId = $item['purchase_order_item_id'] ?? null;
                $productId = $item['product_id'] ?? null;
                $quantity = (int) ($item['quantity'] ?? 0);
                $status = $item['status'] ?? 'received';

                if (!$poItemId) {
                    $matchedPoItem = $poItems->where('product_id', $productId)->first();
                    if ($matchedPoItem) {
                        $poItemId = $matchedPoItem->id;
                    }
                }

                if (!$poItemId || !isset($poItems[$poItemId])) {
                    continue;
                }

                $poItem = $poItems[$poItemId];

                $maxReceivable = $poItem->quantity - $poItem->quantity_received - $poItem->quantity_replaced;

                if ($status === 'received' && $quantity > $maxReceivable) {
                    $validator->errors()->add(
                        "items.$index.quantity",
                        "La cantidad a recibir ($quantity) excede el pendiente ($maxReceivable) para \"{$poItem->product_name}\"."
                    );
                }
            }
        });
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
            'items.*.batch_number.required' => 'El número de lote es obligatorio.',
            'items.*.expiration_date.required' => 'La fecha de vencimiento es obligatoria.',
            'items.*.warehouse_location.required' => 'La ubicación en almacén es obligatoria.',
            'document_type.required' => 'El tipo de documento es obligatorio.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'reason.required' => 'La razón del ingreso es obligatoria.',
            'entry_date.required' => 'La fecha de ingreso es requerida.',
        ];
    }
}
