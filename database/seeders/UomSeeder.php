<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UomSeeder extends Seeder
{
    public function run(): void
    {
        // Resuelve el problema del "Not null violation" en user_id
        $adminId = \App\Models\User::first()->id ?? 1;

        $units = [
            ['name' => 'Unidad', 'abbreviation' => 'und', 'user_id' => $adminId],
            ['name' => 'Par', 'abbreviation' => 'par', 'user_id' => $adminId],
            ['name' => 'Caja', 'abbreviation' => 'cj', 'user_id' => $adminId],
            ['name' => 'Bulto', 'abbreviation' => 'blt', 'user_id' => $adminId],
        ];

        foreach ($units as $u) {
            Unit::updateOrCreate(
                ['name' => $u['name']],
                ['abbreviation' => $u['abbreviation'], 'user_id' => $u['user_id']]
            );
        }
    }
}