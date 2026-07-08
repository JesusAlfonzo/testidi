<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        if (is_array($items)) {
            foreach ($items as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $uomId = $item['uom_id'] ?? null;
                $qtyUom = $item['quantity_uom'] ?? null;
                $costUom = $item['unit_cost_uom'] ?? null;

                if ($productId) {
                    $product = \App\Models\Product::find($productId);
                    if ($product) {
                        $factor = $product->getConversionFactorFor($uomId ?? $product->unit_id);
                        
                        if ($qtyUom !== null) {
                            $items[$index]['quantity'] = (int) round($qtyUom * $factor);
                        } else {
                            $items[$index]['quantity_uom'] = $item['quantity'] ?? null;
                        }
                        
                        if ($costUom !== null) {
                            $items[$index]['unit_cost'] = $factor > 0 ? round($costUom / $factor, 4) : $costUom;
                        } else {
                            $items[$index]['unit_cost_uom'] = $item['unit_cost'] ?? null;
                        }
                    }
                }
            }
            $this->merge(['items' => $items]);
        }
    }

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
            
            // Reglas de UoM
            'items.*.uom_id' => 'nullable|exists:units,id',
            'items.*.quantity_uom' => 'nullable|numeric|min:0.0001',
            'items.*.unit_cost_uom' => 'nullable|numeric|min:0',
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
