<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => fake()->uuid(),
            'details' => fake()->sentence(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
