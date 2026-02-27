<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\User;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()->id;
        
        $locations = [
            ['name' => 'Almacén Principal', 'details' => 'Bodega principal del laboratorio', 'user_id' => $userId],
            ['name' => 'Refrigerador 1', 'details' => 'Refrigerador de reactivos 2-8°C', 'user_id' => $userId],
            ['name' => 'Congelador -20°C', 'details' => 'Congelador de almacenamiento', 'user_id' => $userId],
            ['name' => 'Congelador -80°C', 'details' => 'Congelador de baja temperatura', 'user_id' => $userId],
            ['name' => 'Laboratorio A', 'details' => 'Área de trabajo A', 'user_id' => $userId],
            ['name' => 'Laboratorio B', 'details' => 'Área de trabajo B', 'user_id' => $userId],
            ['name' => 'Sala de Cultivos', 'details' => 'Sala estéril de cultivos', 'user_id' => $userId],
            ['name' => 'Área de Lavado', 'details' => 'Área de limpieza de material', 'user_id' => $userId],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
