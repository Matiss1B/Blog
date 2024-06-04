<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\API\V1\User;
use App\Models\API\V1\Tokens;

class CommentTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
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
    /**
     * @test
     * @return void
     */
    public function it_inserts_comment()
    {
        $commentData = [
            "comment" => "Example comment",
            "blog_id" => 1,
        ];
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->postJson('api/v1/comment/create', $commentData);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_return_all_comments()
    {
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->get('api/v1/comments');

        $response->assertStatus(200);
    }
}
