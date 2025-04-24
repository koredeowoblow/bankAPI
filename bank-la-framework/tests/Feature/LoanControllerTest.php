<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LoanData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_loan_successfully()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/create-loan', [
                'principal' => 10000,
                'fixedInterestRate' => 0.1,
                'duration' => 12,
                'durationType' => 'months',
                'nextOfKin' => 'Jane Doe',
                'nextOfKinPhone' => '08012345678',
            ])
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('loan_data', [
            'principal' => 10000,
            'user_id' => $user->id,
            'fixed_interest_rate' => 0.1,
            'duration' => 12,
        ]);
    }

    public function test_create_loan_with_excess_loans()
    {
        $user = User::factory()->create();

        // Create 3 loans for the user
        LoanData::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/create-loan', [
                'principal' => 10000,
                'fixedInterestRate' => 0.1,
                'duration' => 12,
                'durationType' => 'months',
                'nextOfKin' => 'Jane Doe',
                'nextOfKinPhone' => '08012345678',
            ])
            ->assertStatus(200)
            ->assertJson(['status' => 'excess']);
    }

    public function test_create_loan_validation_fail()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/create-loan', [
                'principal' => -10000, // Invalid principal
                'fixedInterestRate' => 0.1,
                'duration' => 12,
                'durationType' => 'months',
                'nextOfKin' => 'Jane Doe',
                'nextOfKinPhone' => '08012345678',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['principal']);
    }

    public function test_fetch_loan_details_successfully()
    {
        $user = User::factory()->create();
        $loan = LoanData::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'api')
     ->getJson('/api/loan/fetch-loan-details?id=' . $loan->id)
     ->assertStatus(200)
     ->assertJson([
         'status' => 'success',
         'loan' => [
             'id' => $loan->id,
             'principal' => number_format($loan->principal, 2),
             'interest' => number_format($loan->interest, 2),
             'total_amount' => '₦' . number_format($loan->total_amount, 2),
             'duration' => $loan->duration . ' ' . ucfirst($loan->duration_type),
             'next_of_kin' => $loan->next_of_kin,
             'next_of_kin_phone' => $loan->next_of_kin_phone,
             'status' => $loan->status,
         ]
     ]);
 }

    public function test_fetch_loan_details_loan_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
        ->getJson('/api/loan/fetch-loan-details?id=999')
        ->assertStatus(200)
        ->assertJson(['status' => 'failed']);

    }

    public function test_fetch_loans_successfully()
    {
        $user = User::factory()->create();
        LoanData::factory()->count(5)->create(['user_id' => $user->id]);

        $this->actingAs($user, 'api')
            ->getJson('/api/loan/fetch-loans')
            ->assertStatus(200)
            ->assertJson([
                'result' => 'success',
                'loans' => [],
                'limit' => 3,
            ]);
    }

    public function test_delete_loan_successfully()
    {
        $user = User::factory()->create();
        $loan = LoanData::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/delete-loan', ['id' => $loan->id])
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('loan_data', ['id' => $loan->id]);
    }

    public function test_delete_loan_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/delete-loan', ['id' => 999])
            ->assertStatus(200)
            ->assertJson(['status' => 'failed']);
    }

    public function test_update_loan_successfully()
    {
        $user = User::factory()->create();
        $loan = LoanData::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/update-loan', [
                'id' => $loan->id,
                'principal' => 12000, // Updated principal
            ])
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $loan->refresh();
        $this->assertEquals(12000, $loan->principal);
    }

    public function test_update_loan_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/loan/update-loan', ['id' => 999])
            ->assertStatus(200)
            ->assertJson(['status' => 'failed']);
    }
}
