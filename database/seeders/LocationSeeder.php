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
            ['name' => 'Biblioteca', 'details' => 'Bodega principal de productos', 'user_id' => $userId],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
