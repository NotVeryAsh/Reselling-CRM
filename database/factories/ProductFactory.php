<?php

namespace Database\Factories;

use App\Enums\PurchasingPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'code' => Str::random(10),
            'purchased_at' => now(),
            'purchased_price' => fake()->randomFloat(2, 0, 100),
            'purchased_platform' => fake()->randomElement(PurchasingPlatform::cases()),
        ];
    }
}
