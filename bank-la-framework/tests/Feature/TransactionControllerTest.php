<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_transactions_returns_success_response()
    {
        $user = User::factory()->create();
        Transaction::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/transaction/fetch-transactions?y=0');

        $response->assertStatus(200)
            ->assertJson([
                'result' => 'success',
            ])
            ->assertJsonStructure([
                'transactions',
                'result',
                'message',
            ]);
    }


    public function test_fetch_transactions_returns_empty_response_when_no_transactions()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/transaction/fetch-transactions?y=0');

        $response->assertStatus(200)
            ->assertJson([
                'result' => 'empty',
                'message' => 'No transactions found.',
                'transactions' => [],
            ]);
    }


    public function test_fetch_single_transaction_returns_success_response()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->getJson('/api/transaction/fetch-transaction-single?id=' . $transaction->id);

        $response->assertStatus(200)
            ->assertJson([
                'result' => 'success',
                'transaction' => [
                    'id' => $transaction->id,
                ],
            ]);
    }

    public function test_fetch_single_transaction_returns_404_when_not_found()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/transaction/fetch-transaction-single?id=9999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Transaction not found.',
            ]);
    }
}
