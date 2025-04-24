<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = \App\Models\Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'transaction_type'=>$this->faker->randomElement(['same_bank', 'transfer_out','fund']),
            'transaction_nature' => $this->faker->randomElement(['credit', 'debit']),
            'reference_number' => strtoupper(Str::random(10)),
            'recipient_bank_details' => json_encode([
                'account_name' => $this->faker->name,
                'account_number' => $this->faker->bankAccountNumber,
                'bank' => $this->faker->company,
            ]),
            'sender_bank_detail' => $this->faker->phoneNumber,
        ];
    }
}
