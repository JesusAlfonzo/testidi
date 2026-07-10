<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class BypassesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permiso para saltarse la regla de Martes y Miércoles
        Permission::firstOrCreate([
            'name' => 'solicitudes_fuera_horario',
            'guard_name' => 'web'
        ]);

        // 2. Permiso para saltarse el límite de 1 solicitud activa por Área
        Permission::firstOrCreate([
            'name' => 'solicitudes_sin_limite_semanal',
            'guard_name' => 'web'
        ]);
        
        // (Opcional) Si usas un Super Admin, puedes asignarle estos permisos aquí:
        // $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        // if ($adminRole) { $adminRole->givePermissionTo(['solicitudes_fuera_horario', 'solicitudes_sin_limite_area']); }
    }
}