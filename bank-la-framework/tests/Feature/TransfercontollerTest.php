<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Bank;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class TransfercontollerTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_bank_transfer_success()
    {
        $sender = User::factory()->create([
            'account_balance' => 10000,
            'pin' => Hash::make('1234'),
        ]);

        $recipient = User::factory()->create([
            'account_balance' => 2000,
        ]);

        $response = $this->actingAs($sender, 'api')->postJson('/api/transfer/same-bank-transfer', [
            'account_numbered' => $recipient->phone_number,
            'amounted' => 1000,
            'pin' => '1234',
        ]);



        $response->assertStatus(200);
        $response->assertJsonFragment(['result' => 'success']);
    }

    public function test_transfer_flow_using_jwt()
    {
        // Create user with hashed pin
        $user = User::factory()->create([
            'pin' => Hash::make('1234'),
            'account_balance' => 10000
        ]);

        // Create dummy bank
        $bank = Bank::create([
            'bank_name' => 'Test Bank',
            'bank_code' => '123'
        ]);

        // Create JWT token
        $token = auth()->login($user);

        // Set headers
        $headers = ['Authorization' => "Bearer $token"];

        // Mock Paystack account resolution
        Http::fake([
            'https://api.paystack.co/bank/resolve*' => Http::response([
                'status' => true,
                'message' => 'Account number resolved',
                'data' => [
                    'account_name' => 'John Doe',
                    'account_number' => '0001234567',
                    'bank_id' => 123
                ]
            ], 200)
        ]);

        // Step 1: Resolve account
        $resolveResponse = $this->getJson('/api/transfer/find-account-other?bankCode=123&acct_number=0001234567', $headers);

        $resolveResponse->assertStatus(200)->assertJson([
            'result' => 'success'
        ]);

        // Mock Paystack recipient creation
        Http::fake([
            'https://api.paystack.co/transferrecipient' => Http::response([
                'status' => true,
                'message' => 'Recipient created',
                'data' => [
                    'recipient_code' => 'RCP_abc123'
                ]
            ], 200)
        ]);

        // Step 2: Create transfer recipient
        $recipientResponse = $this->postJson('/api/transfer/create-transfer-recipient', [
            'bankCode' => '123',
            'acct_number' => '0001234567',
            'acct_name' => 'John Doe'
        ], $headers);


        $recipientResponse->assertStatus(200)->assertJson([
            'result' => 'success'
        ]);

        $recipientCode = $recipientResponse['data']['data']['recipient_code'];

        // Mock Paystack transfer
        Http::fake([
            'https://api.paystack.co/transfer' => Http::response([
                'status' => true,
                'message' => 'Transfer successful',
                'data' => [
                    'reference' => 'ref_123456'
                ]
            ], 200)
        ]);

        // Step 3: Create transfer
        $transferResponse = $this->postJson('/api/transfer/other-bank-transfer', [
            'pin' => '1234',
            'amount' => 1000,
            'recipient_code' => $recipientCode,
            'acct_number' => '0001234567',
            'acct_name' => 'John Doe',
            'bank_code' => '123',
            'reason' => 'Test transfer'
        ], $headers);
        // dd($transferResponse->json());
        $transferResponse->assertStatus(200)->assertJson([
            'result' => 'success',
            'message' => 'Transfer successful'
        ]);
    }

    public function test_find_account_success()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/transfer/find-account-same?account_number=' . $user->phone_number);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
           ]);
    }


    public function test_check_amount_success()
    {
        $user = User::factory()->create(['account_balance' => 10000]);

        $response = $this->actingAs($user)->getJson('/api/transfer/check-amount?amount=5000');

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
    }

    public function test_check_amount_failure()
    {
        $user = User::factory()->create(['account_balance' => 1000]);

        $response = $this->actingAs($user)->getJson('/api/transfer/check-amount?amount=2000');

        $response->assertStatus(400);
        $response->assertJson(['status' => false]);
    }
}
