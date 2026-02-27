<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => 'PROD-' . fake()->unique()->numerify('####'),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'unit_id' => Unit::factory(),
            'location_id' => Location::factory(),
            'stock' => fake()->numberBetween(0, 500),
            'min_stock' => fake()->numberBetween(5, 20),
            'cost' => fake()->randomFloat(2, 10, 1000),
            'price' => fake()->randomFloat(2, 15, 1500),
            'is_active' => true,
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
