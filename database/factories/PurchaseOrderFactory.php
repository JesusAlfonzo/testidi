<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'code' => PurchaseOrder::generateCode(),
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? Supplier::create([
                'name' => fake()->company(),
                'tax_id' => 'TAX-' . fake()->unique()->numerify('####'),
                'email' => fake()->companyEmail(),
                'phone' => fake()->phoneNumber(),
            ])->id,
            'date_issued' => fake()->dateTimeBetween('-30 days', 'now'),
            'delivery_date' => fake()->dateTimeBetween('now', '+45 days'),
            'delivery_address' => fake()->address(),
            'currency' => 'USD',
            'exchange_rate' => 17.15,
            'subtotal' => fake()->randomFloat(2, 100, 10000),
            'tax_amount' => 0,
            'total' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->randomElement(['draft', 'issued', 'completed', 'cancelled']),
            'terms' => 'NET ' . fake()->randomElement([15, 30, 45, 60]),
            'notes' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
        ];
    }
}
