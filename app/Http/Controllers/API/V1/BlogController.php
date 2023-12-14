<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\BlogFilter;
use App\Functions\ImagesFunctions;
use App\Http\Controllers\Controller;
use App\Models\API\V1\SavedBlogs;
use App\Models\API\V1\Tokens;
use App\Http\Middleware\CheckToken;
use App\Http\Controllers\API\V1\TokenController;
use App\Models\API\V1\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\V1\StoreBlogRequest;
use App\Http\Resources\V1\BlogsCollection;
use App\Models\API\V1\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    private $imagesFunctions;
    public function __construct(){
        $this->imagesFunctions = new ImagesFunctions();
    }
    public function index(Request $request)
    {

        $data = $request->all();
        $filter = new BlogFilter();
        $filterItems = $filter->transform($request);
        $blogs = Blog::with('user')->where($filterItems)->get();
        if($blogs->isEmpty()){
            return response()->json(
                [
                    "status"=>200,
                    "message"=>"Could not find any blogs, please check given params",
                ]
            );
        }
        return $blogs;
    }
    /**
     * Show the form for creating a new resource.
     */

    public function create(Request $request)
    {
        $data = $request->input();
            $request->validate([
                "title"=> "required|max:50|min:4",
                "description"=> "required|max:4000|min:4",
                "category"=> "required|max:20|min:4",
                "phone"=> "max:13",
                "email"=> "max:20",
                "img"=> "required"
            ]);
            $img = $request->file("img");
            $blog = [
                "title" => $data["title"],
                "description" => $data['description'],
                "category" => $data['category'],
                "email" => $data['email'],
                "phone" => $data['phone'],
                "author"=>Session::get("user_id"),
                "user_id"=>Session::get("user_id"),
                "img" => $this->imagesFunctions->compress($img, 15),
            ];
            if(Blog::create($blog)){
                return response()->json(["message"=> "Blog created successfully"], 200);
            }else{
                return response()->json(["message"=> "Something gone wrong", "error"=>Blog::create($blog)], 300);
            }

    }

    /**
     * Store a newly created resource in storage.
     */
//    public function save(Request $request)
//    {
//        $data = [
//            "user_id"=> Session::get("user_id"),
//            "blog_id"=> request("blog_id"),
//        ];
//        if(SavedBlogs::create($data)){
//            return response()->json([
//                "message"=>"Saved successfully!",
//                "status"=>200,
//            ]);
//        }
//        return response()->json([
//            "message"=>"Error",
//            "status"=>300,
//        ]);
//    }
    public function getSaved(Request $request){
        $userId = 2;

        $blogs = Blog::with(['savedBlogsForCurrentUser' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->find(1);

        return $blogs;
    }


    public function update(Request $request)
    {
        $updatedData = [
            'id' => $request->input('id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
        ];
            $img = $request->file("img");
            $blog = Blog::findOrFail($updatedData["id"]);
            if(isset($img)) {
                unlink(storage_path('app/public/' . $blog->img));
                $blog->img = $this->imagesFunctions->compress($img, 15);;
            }
            $blog->title = $updatedData['title'];
            $blog->description = $updatedData['description'];
            $blog->category = $updatedData['category'];

            if ($blog->save()) {
                return response()->json([
                    "message" => "Blog updated successfuly!",
                    "error" => "No"
                ]);

            } else {
                return response()->json([
                    "message" => "Something gone wrong!",
                    "error" => $blog->save(),
                ]);
            }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        //
    }
}
