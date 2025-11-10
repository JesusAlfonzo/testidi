<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // 1. Obtener el ID de la unidad solo si existe en la ruta (edición)
        $unitId = $this->route('unit') ? $this->route('unit')->id : null;

        // 2. Definir reglas de unicidad base
        $uniqueName = ['required', 'string', 'max:50'];
        $uniqueAbbreviation = ['required', 'string', 'max:10'];

        if ($unitId) {
            // Modo Edición: Excluir el ID actual de la validación
            $uniqueName[] = 'unique:units,name,' . $unitId;
            $uniqueAbbreviation[] = 'unique:units,abbreviation,' . $unitId;
        } else {
            // Modo Creación: Solo verificar la unicidad
            $uniqueName[] = 'unique:units,name';
            $uniqueAbbreviation[] = 'unique:units,abbreviation';
        }

        return [
            // Aplicamos las reglas de unicidad condicional
            'name' => $uniqueName,
            'abbreviation' => $uniqueAbbreviation,
        ];
    }
}
