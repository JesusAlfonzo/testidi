<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\User;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()->id;
        
        $units = [
            ['name' => 'Unidad', 'abbreviation' => 'und', 'user_id' => $userId],
            ['name' => 'Kilogramo', 'abbreviation' => 'kg', 'user_id' => $userId],
            ['name' => 'Gramo', 'abbreviation' => 'g', 'user_id' => $userId],
            ['name' => 'Miligramo', 'abbreviation' => 'mg', 'user_id' => $userId],
            ['name' => 'Litro', 'abbreviation' => 'L', 'user_id' => $userId],
            ['name' => 'Mililitro', 'abbreviation' => 'mL', 'user_id' => $userId],
            ['name' => 'Microlitro', 'abbreviation' => 'μL', 'user_id' => $userId],
            ['name' => 'Metro', 'abbreviation' => 'm', 'user_id' => $userId],
            ['name' => 'Centímetro', 'abbreviation' => 'cm', 'user_id' => $userId],
            ['name' => 'Milímetro', 'abbreviation' => 'mm', 'user_id' => $userId],
            ['name' => 'Caja', 'abbreviation' => 'caja', 'user_id' => $userId],
            ['name' => 'Paquete', 'abbreviation' => 'paq', 'user_id' => $userId],
            ['name' => 'Rollos', 'abbreviation' => 'rol', 'user_id' => $userId],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
