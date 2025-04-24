<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ProfileControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function test_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/profile/get-user-profile?' . $user->id);
        // dd($response->getContent());
        $response->assertStatus(200)
            ->assertJson([
                'result'=>'success',
                'user'=>[
                    'fullname'=>$user->fullname
                ],
            ]);
    }

    public function test_update_profile(){
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->postJson('/api/profile/update-profile',[
            'fullname'=>'ade',
            'email'=>'ade@email.com'
        ]);
        $response->assertStatus(200)
        ->assertJson([
            'result'=> 'success',
            'message' => 'Profile updated successfully.',
            ]);
    }
}
