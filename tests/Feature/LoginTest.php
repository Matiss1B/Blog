<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\API\V1\User;
class LoginTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_logs_in_user()
    {
        $str =Str::random(5);
        $password = "password123";
        $create = [
            'email' => $str.'@example.com',
            'password' => Hash::make($password),
            'name' => 'John',
            'surname' => 'Doe',
        ];
        User::factory()->create($create);

        $loginData = [
            'email' => $str.'@example.com',
            'password' => $password,
        ];

        $response = $this->postJson('api/v1/login', $loginData);

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     */
    public function it_returns_message_if_entered_incorrect_login_info()
    {
        // Create example user to test
        $str = Str::random(5);
        $password = "password123";
        $create = [
            'email' => $str.'@example.com',
            'password' => Hash::make($password),
            'name' => 'John',
            'surname' => 'Doe',
        ];
        User::factory()->create($create);

        // Invalid login data
        $loginData = [
            'email' => 'user@example.com',
            'password' => '123456781',
        ];

        $response = $this->postJson('api/v1/login', $loginData);

        $response->assertStatus(422);
    }
    /**
     * @test
     * @return void
     */
    public function it_requires_all_fields_to_be_filled()
    {
        $response = $this->postJson('api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
    /**
     * @test
     * @return void
     */
    public function it_validates_email_format()
    {
        $userData = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'name' => 'John',
            'surname' => 'Doe',
        ];

        $response = $this->postJson('api/v1/login', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


}
