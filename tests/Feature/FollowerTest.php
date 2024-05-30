<?php

namespace Tests\Feature;

use App\Models\API\V1\Followers;
use App\Models\API\V1\Tokens;
use App\Models\API\V1\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FollowerTest extends TestCase
{
    use DatabaseMigrations;

    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $exampleUserId = 1;
        $token = Str::random(30);

        // Create a user and generate a token
        Tokens::factory()->create([
            'user_id' => $exampleUserId,
            'token' => $token,
        ]);

        $this->token = $token;
    }

    /** @test */
    public function it_follows_and_unfollows_user()
    {
        $followData = [
            'account_id' => 2,
        ];
        $str1 =Str::random(5);
        $user1 = [
            'email' => $str1.'@example.com',
            'password' => Hash::make("password1234"),
            'name' => 'John',
            'surname' => 'Doe',
        ];
        $str2 =Str::random(5);

        $user2 = [
            'email' => $str2.'@example.com',
            'password' => Hash::make("password1234"),
            'name' => 'John',
            'surname' => 'Doe',
        ];
        User::factory()->createMany([$user1, $user2]);

        try {
            $response = $this->withHeaders([
                'Authorization' => $this->token,
            ])->postJson('api/v1/follow/toggle', $followData);

            $response->assertStatus(201);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /** @test */
    public function it_removes_follower()
    {
        // Make like I have some follower
        Followers::factory()->create([
            'account_id' => 1,
            'user_id' => 2,
        ]);

        $followData = [
            'follower_id' => 2,
        ];

        $response = $this->withHeaders([
            'Authorization' => $this->token,
        ])->postJson('api/v1/follow/remove', $followData);

        $response->assertStatus(201);
    }
}
