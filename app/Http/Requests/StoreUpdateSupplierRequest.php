<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $supplierId = $this->route('supplier') ? $this->route('supplier')->id : null;

        $taxIdRules = [
            'nullable',
            'string',
            'max:50',
            // Rule::unique verifica que sea único, excluyendo el ID actual si estamos editando.
            // La cláusula 'whereNotNull' evita que los nulos causen errores.
            Rule::unique('suppliers')->ignore($supplierId)->where(fn ($query) => $query->whereNotNull('tax_id')),
        ];

        return [
            'name' => ['required', 'string', 'max:150'],
            'tax_id' => $taxIdRules,
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
