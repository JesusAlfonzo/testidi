<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()->id;
        
        $categories = [
            ['name' => 'Reactivos de Laboratorio', 'description' => 'Reactivos químicos y soluciones', 'user_id' => $userId],
            ['name' => 'Material de Vidrio', 'description' => 'Material de vidrio de laboratorio', 'user_id' => $userId],
            ['name' => 'Material Plástico', 'description' => 'Material plástico descartable', 'user_id' => $userId],
            ['name' => 'Equipos de Laboratorio', 'description' => 'Equipos y aparatología', 'user_id' => $userId],
            ['name' => 'Kits de Diagnóstico', 'description' => 'Kits para diagnóstico clínico', 'user_id' => $userId],
            ['name' => 'Consumibles Generales', 'description' => 'Consumibles de uso general', 'user_id' => $userId],
            ['name' => 'Medios de Cultivo', 'description' => 'Medios de cultivo microbiológico', 'user_id' => $userId],
            ['name' => 'Instrumental', 'description' => 'Instrumental médico y de laboratorio', 'user_id' => $userId],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
