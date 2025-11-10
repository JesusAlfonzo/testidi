<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;

        // Regla de unicidad condicional para el código (SKU)
        $codeRules = ['required', 'string', 'max:50'];
        if ($productId) {
            $codeRules[] = 'unique:products,code,' . $productId;
        } else {
            $codeRules[] = 'unique:products,code';
        }

        return [
            // Maestros (Todas las FK deben existir en sus respectivas tablas)
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],

            // Información General
            'code' => $codeRules,
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Gestión y Stock
            'cost' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0', 'gte:cost'], // El precio debe ser >= al costo
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.gte' => 'El precio de venta debe ser mayor o igual que el precio de costo.',
        ];
    }
}
