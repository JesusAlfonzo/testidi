<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' ' . fake()->numerify('#'),
            'description' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
