<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company().' Cell & Servis';

        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'status' => 'active',
            'plan' => 'starter',
            'trial_ends_at' => now()->addDays(14),
            'settings' => [
                'receipt_header' => $name,
                'receipt_footer' => 'Terima kasih telah berkunjung.',
                'default_warranty_days' => 30,
                'thermal_paper_width' => 58,
            ],
        ];
    }
}
