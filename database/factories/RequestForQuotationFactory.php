<?php

namespace Database\Factories;

use App\Models\RequestForQuotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestForQuotationFactory extends Factory
{
    protected $model = RequestForQuotation::class;

    public function definition(): array
    {
        return [
            'code' => RequestForQuotation::generateCode(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'date_required' => fake()->dateTimeBetween('now', '+30 days'),
            'delivery_deadline' => fake()->dateTimeBetween('+31 days', '+60 days'),
            'status' => fake()->randomElement(['draft', 'sent', 'closed', 'cancelled']),
            'notes' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }
}
