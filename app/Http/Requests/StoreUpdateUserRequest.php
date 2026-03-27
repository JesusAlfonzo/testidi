<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        if ($this->isMethod('POST')) {
            return $user->can('usuarios_crear');
        }
        
        return $user->can('usuarios_editar');
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
        ];

        // 🎯 CORRECCIÓN CLAVE: Regla de unicidad de email
        $emailRules = ['required', 'string', 'email', 'max:255'];

        if ($userId) {
            // Si estamos editando, excluimos al usuario actual.
            $emailRules[] = 'unique:users,email,' . $userId;
        } else {
            // Si estamos creando, solo verificamos que sea único.
            $emailRules[] = 'unique:users,email';
        }

        $rules['email'] = $emailRules;

        // ... resto de las reglas de password ...
        if ($this->isMethod('POST')) {
            $rules['password'] = [
                'required', 
                'string', 
                'min:8',
                'confirmed',
            ];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }
    
    public function messages(): array
    {
        return [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        ];
    }
}
