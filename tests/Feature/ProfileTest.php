<?php

namespace Tests\Feature;

use App\Models\API\V1\Tokens;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\API\V1\User;
class ProfileTest extends TestCase
{
    use DatabaseMigrations;
    protected $token;
    //Create token to pass CheckToken middleware
    public function setUp(): void
    {
        parent::setUp();

        $exampleUserId = 1;
        $token = Str::random(30);

        // Create a user and generate a token
        Tokens::factory()->create([
            "user_id" => $exampleUserId,
            "token" => $token,
        ]);

        $this->token = $token;
    }

    /** @test */
    public function it_updates_profile_info()
    {
        $userData = [
          "email" => "example@example.com",
            "name" => "Example name",
            "surname" => "Example surname",
        ];
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->putJson('api/v1/user/edit', $userData);

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     */
    public function it_validates_profile_update_email_format()
    {
        $userData = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'name' => 'John',
            'surname' => 'Doe',
        ];

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->putJson('api/v1/user/edit', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
    /**
     * @test
     * @return void
     */
    public function it_validates_password_email_sending_email_format()
    {
        $userData = [
            'email' => 'invalid-email',
        ];

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('api/v1/user/password-reset-mail', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
    /**
     * @test
     * @return void
     **/
    public function it_sends_password_reset_email()
    {
        $emailData = [
            "email" => "example@example.com",
        ];
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->post('api/v1/user/password-reset-mail', $emailData);

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     **/
    public function it_logs_out_user()
    {
        $userData = [
            "user" => $this->token,
        ];
        $response = $this->post('api/v1/logout', $userData);

        $response->assertStatus(200);
    }




}
