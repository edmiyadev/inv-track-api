<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'tax_id' => $this->faker->unique()->numerify('#########'),
            'phone_number' => $this->faker->phoneNumber(),
        ];
    }
}
