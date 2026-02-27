<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\User;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()->id;
        
        $brands = [
            ['name' => 'Sigma-Aldrich', 'website' => 'https://sigmaaldrich.com', 'user_id' => $userId],
            ['name' => 'Thermo Fisher', 'website' => 'https://thermofisher.com', 'user_id' => $userId],
            ['name' => 'Merck', 'website' => 'https://merck.com', 'user_id' => $userId],
            ['name' => 'Corning', 'website' => 'https://corning.com', 'user_id' => $userId],
            ['name' => 'Falcon', 'website' => 'https://falcon.com', 'user_id' => $userId],
            ['name' => 'Eppendorf', 'website' => 'https://eppendorf.com', 'user_id' => $userId],
            ['name' => 'Gilson', 'website' => 'https://gilson.com', 'user_id' => $userId],
            ['name' => 'BD Biosciences', 'website' => 'https://bd.com', 'user_id' => $userId],
            ['name' => 'Roche', 'website' => 'https://roche.com', 'user_id' => $userId],
            ['name' => 'Abbott', 'website' => 'https://abbott.com', 'user_id' => $userId],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
