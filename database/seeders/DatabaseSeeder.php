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
    // Solo llama a los seeders, no crees usuarios aquí
    $this->call([
        RolesAndPermissionsSeeder::class, // Primero los roles y el usuario admin
        CategorySeeder::class,
        UnitSeeder::class,
        SupplierSeeder::class,
        LocationSeeder::class,
        ProductSeeder::class,
    ]);
}
}
