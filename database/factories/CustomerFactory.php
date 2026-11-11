<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'name' => fake()->name(),
            'phone' => fake()->numerify('08##########'),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'notes' => fake()->sentence(),
            'total_orders_count' => fake()->numberBetween(0, 5),
            'total_spent' => fake()->numberBetween(50000, 1500000),
        ];
    }
}
