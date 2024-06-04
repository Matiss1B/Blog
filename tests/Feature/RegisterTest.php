<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\API\V1\User;
class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_registers_a_new_user_with_valid_data()
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'name' => 'John',
            'surname' => 'Doe',
        ];

        $response = $this->postJson('api/v1/register', $userData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'OK',
                'link' => 'home',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'John',
            'surname' => 'Doe',
        ]);
    }

    /** @test */
    public function it_returns_error_if_email_is_already_taken()
    {
        //Create example user, to test if returns that email is taken
        $str =Str::random(5);
        $create = [
            'email' => $str.'@example.com',
            'password' => 'password123',
            'name' => 'John',
            'surname' => 'Doe',
        ];
        User::factory()->create($create);

        $userData = [
            'email' => $str.'@example.com',
            'password' => 'password123',
            'name' => 'John',
            'surname' => 'Doe',
        ];

        $response = $this->postJson('api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => 'ERR',
                'errors' => [
                    'email' => 'This email is taken',
                ],
            ]);
    }

    /** @test */
    public function it_requires_all_fields_to_be_filled()
    {
        $response = $this->postJson('api/v1/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'name', 'surname']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $userData = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'name' => 'John',
            'surname' => 'Doe',
        ];

        $response = $this->postJson('api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

}
