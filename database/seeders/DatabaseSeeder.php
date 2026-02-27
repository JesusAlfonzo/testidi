<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Permisos y roles (debe ejecutarse primero)
        $this->call(RolesAndPermissionsSeeder::class);

        // Datos base y módulo de compras
        $this->call(ComprasSeeder::class);
    }
}
