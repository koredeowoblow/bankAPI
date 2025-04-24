<?php

namespace Database\Factories;

use App\Models\LoanData;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanDataFactory extends Factory
{
    protected $model = LoanData::class;

    public function definition()
    {
        $principal = $this->faker->randomFloat(2, 1000, 10000);
        $rate = 0.05;
        $duration = $this->faker->numberBetween(1, 12);
        $interest = $principal * $rate * $duration;

        return [
            'user_id' => \App\Models\User::factory(),
            'principal' => $principal,
            'fixed_interest_rate' => $rate,
            'duration' => $duration,
            'duration_type' => 'months',
            'next_of_kin' => $this->faker->name,
            'next_of_kin_phone' => $this->faker->phoneNumber,
            'interest' => $interest,
            'total_amount' => $principal + $interest,
            'status' => "pending"
        ];
    }
}
