<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->bothify('WH-??##')),
            'location' => $this->faker->address(),
            'descripcion' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being true (active)
        ];
    }
}
