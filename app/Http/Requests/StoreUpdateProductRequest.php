<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('type') ?: 'individual',
            'requires_serial' => $this->has('requires_serial') ? $this->boolean('requires_serial') : false,
            'price' => $this->filled('price') ? $this->input('price') : 0.00,
        ]);
    }

    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;
        $isGeneric = $this->boolean('is_generic');

        // Regla de unicidad condicional para el código (SKU)
        $codeRules = ['required', 'string', 'max:50'];
        if ($productId) {
            $codeRules[] = 'unique:products,code,' . $productId;
        } else {
            $codeRules[] = 'unique:products,code';
        }

        return [
            'is_generic' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'in:individual,composite_kit'],
            'requires_serial' => ['nullable', 'boolean'],

            // Maestros — Condicionales según is_generic
            'category_id' => [Rule::requiredIf(!$isGeneric), 'nullable', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'], // Siempre requerido
            'location_id' => [Rule::requiredIf(!$isGeneric), 'nullable', 'exists:locations,id'],
            'brand_id' => ['nullable', 'exists:brands,id'], // Siempre opcional

            // Información General
            'code' => $codeRules,
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],

            // Gestión y Stock
            'cost' => ['required', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'], // El precio es opcional
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],

            // Vencimiento y FIFO
            'track_expiry' => ['nullable', 'boolean'],
            'expiry_warning_days' => ['nullable', 'integer', 'min:1', 'max:365'],

            // Componentes para Kits
            'components' => ['required_if:type,composite_kit', 'array'],
            'components.*.child_id' => ['required_if:type,composite_kit', 'exists:products,id'],
            'components.*.quantity' => ['required_if:type,composite_kit', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La categoría es obligatoria para productos estrictos.',
            'location_id.required' => 'La ubicación es obligatoria para productos estrictos.',
        ];
    }
}
