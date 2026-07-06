<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'user_id' => 1,
                'name' => 'Artículos de Oficina',
                'description' => 'Suministros, equipos menores y materiales de uso diario para el correcto funcionamiento de las actividades administrativas y de escritorio.',
            ],
            [
                'user_id' => 1,
                'name' => 'Papelería',
                'description' => 'Materiales basados en papel, sobres, carpetas e insumos de impresión destinados al registro, archivo y gestión de documentos.',
            ],
            [
                'user_id' => 1,
                'name' => 'Artículos de Limpieza',
                'description' => 'Productos químicos, desinfectantes e implementos destinados a la higiene, esterilización y mantenimiento de los espacios institucionales.',
            ],
            [
                'user_id' => 1,
                'name' => 'Ferretería',
                'description' => 'Herramientas, repuestos, componentes eléctricos y materiales necesarios para el mantenimiento preventivo y correctivo de las instalaciones.',
            ],
            [
                'user_id' => 1,
                'name' => 'Laboratorio',
                'description' => 'Reactivos, insumos médicos, material de vidrio y equipos especializados para uso clínico, pruebas de inmunología e investigaciones científicas.',
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}