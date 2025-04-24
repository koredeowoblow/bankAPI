<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class FundsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_initialize_payment_successfully()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/funds/initialize-payment', ['amount' => 1500])
            ->assertStatus(200)
            ->assertJsonStructure(['result', 'email', 'amount', 'publicKey'])
            ->assertJson(['result' => 'success']);
    }

    public function test_initialize_payment_fails_without_amount()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/funds/initialize-payment', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_verify_payment_successfully()
    {
        $user = User::factory()->create([
            'account_balance' => 0,
            'phone_number' => '08012345678',
            'fullname' => 'John Doe'
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 100000,
                    'authorization' => [
                        'bank' => 'Test Bank',
                        'channel' => 'card',
                        'card_type' => 'visa',
                        'last4' => '1234',
                    ],
                ],
            ])
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/funds/verify-payment', ['reference' => 'fake_ref_123'])
            ->assertStatus(200)
            ->assertJson(['result' => 'success']);

        $this->assertDatabaseHas('transactions', [
            'reference_number' => 'fake_ref_123',
            'user_id' => $user->id,
            'amount' => 1000.00,
            'transaction_nature' => 'credit',
        ]);

        $this->assertEquals(1000.00, $user->fresh()->account_balance);
    }

    public function test_verify_payment_fails_with_unsuccessful_status()
    {
        $user = User::factory()->create();

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'failed',
                    'amount' => 50000,
                ],
            ])
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/funds/verify-payment', ['reference' => 'bad_ref'])
            ->assertStatus(200)
            ->assertJson([
                'result' => 'fail',
                'error' => 'Payment was not successful.'
            ]);
    }

    public function test_verify_payment_fails_when_paystack_api_errors()
    {
        $user = User::factory()->create();

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([], 500)
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/funds/verify-payment', ['reference' => 'bad_ref'])
            ->assertStatus(500)
            ->assertJson([
                'result' => 'fail',
                'error' => 'Paystack API error.'
            ]);
    }
}
