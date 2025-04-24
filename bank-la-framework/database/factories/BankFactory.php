<?php

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        return [
            'bank_name' => $this->faker->company . ' Bank',
            'bank_code' => $this->faker->unique()->numerify('###'), // e.g., 123
        ];

    }
}
