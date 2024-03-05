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
class BlogTest extends TestCase
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
    public function it_return_all_blogs()
    {
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->get('api/v1/blogs');

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     */
    public function it_return_blogs_with_certain_id()
    {
        //Param string with certain id
        $string = '?id=1';
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->get('api/v1/blogs'.$string);

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     **/
    public function it_return_blogs_with_certain_category()
    {
        //Param string with certain id
        $string = '?category=cars';
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->get('api/v1/blogs'.$string);

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     **/
    public function it_deletes_blog()
    {
        //Param string with certain id
        $id = 1;
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->get('api/v1/blog/delete/'.$id);

        $response->assertStatus(200);
    }
    /**
     * @test
     * @return void
     **/
    public function it_saves_blog()
    {
        //Param string with certain id
        $saveData = [
            "blog_id" => 1
        ];
        $response = $this->withHeaders([
            'Authorization' =>$this->token,
        ])->post('api/v1/blog/save', $saveData);

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
//    public function it_requires_all_fields_to_be_filled_when_edit_blog()
//    {
//        $response = $this->withHeaders([
//            'Authorization' =>$this->token,
//        ])->postJson('api/v1/blog/edit', []);
//
//        $response->assertStatus(422)
//            ->assertJsonValidationErrors(['title', 'description', 'img', 'category']);
//    }
    /**
     * @test
     * @return void
     **/
//    public function it_edit_blog()
//    {
//        //Param string with certain id
//        $blogData = [
//            "id" => 1,
//            "title" => 'Example title',
//            "description" => 'Example description',
//            "img" => 'Example img source',
//            "category" => 'Example category'
//
//        ];
//        $response = $this->withHeaders([
//            'Authorization' =>$this->token,
//        ])->postJson('api/v1/edit', $blogData);
//
//        $response->assertStatus(200);
//    }



}
