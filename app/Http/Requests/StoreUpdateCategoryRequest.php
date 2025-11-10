<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 1. Obtener el ID de la categoría solo si existe en la ruta (es decir, estamos editando)
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        // 2. Definir la regla de unicidad de 'name'
        $nameRules = ['required', 'string', 'max:100'];

        if ($categoryId) {
            // Si estamos editando, excluimos el ID actual de la validación de unicidad.
            $nameRules[] = 'unique:categories,name,' . $categoryId;
        } else {
            // Si estamos creando, solo verificamos que sea único en toda la tabla.
            $nameRules[] = 'unique:categories,name';
        }

        return [
            // Aplicamos la regla de unicidad condicional
            'name' => $nameRules,
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
