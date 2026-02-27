<?php

namespace Database\Factories;

use App\Models\PurchaseQuote;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseQuoteFactory extends Factory
{
    protected $model = PurchaseQuote::class;

    public function definition(): array
    {
        return [
            'code' => PurchaseQuote::generateCode(),
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? Supplier::factory()->create()->id,
            'user_id' => User::factory(),
            'supplier_reference' => 'REF-' . strtoupper(fake()->lexify('????')),
            'date_issued' => fake()->dateTimeBetween('-30 days', 'now'),
            'valid_until' => fake()->dateTimeBetween('now', '+30 days'),
            'delivery_date' => fake()->dateTimeBetween('now', '+45 days'),
            'currency' => 'USD',
            'exchange_rate' => 17.15,
            'subtotal' => fake()->randomFloat(2, 100, 10000),
            'tax_amount' => 0,
            'total' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->randomElement(['pending', 'selected', 'approved', 'rejected', 'converted']),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
