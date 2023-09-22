<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\BlogFilter;
use App\Http\Controllers\Controller;
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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $data = $request->all();
        $filter = new BlogFilter();
        $filterItems = $filter->transform($request);
        dd($filterItems);
        $blogs = Blog::where($filterItems)
            ->paginate()
            ->appends($request->query());
        foreach ($blogs as $blog) {
            $author = $blog->author;
            $authorUser = User::find($author);
            if ($authorUser) {
                $blog->author_name = $authorUser->name;
            }
        }

        return new BlogsCollection($blogs);
    }
    /**
     * Show the form for creating a new resource.
     */
    private function compressImage($source, $quality)
    {
        $info = getimagesize($source);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } else {
            return false; // Unsupported image format
        }

        ob_start(); // Start output buffering
        imagejpeg($image, null, $quality); // Output the compressed image to the buffer
        imagedestroy($image);
        $compressedImage = ob_get_clean(); // Get the buffer content and clean the buffer
        return $compressedImage;
    }
    public function create(Request $request)
    {
        $data = $request->input();
            $request->validate([
                "title"=> "required|max:50|min:4",
                "description"=> "required|max:1000|min:4",
                "category"=> "required|max:20|min:4",
                "phone"=> "max:13",
                "email"=> "max:20",
                "img"=> "required"
            ]);

            $img = $request->file("img");
            $compressedImage = $this->compressImage($img, 15);
            $destinationPath = 'images/' . Str::random(60) . '.jpg'; // Replace with the desired destination path within the disk
            Storage::disk('public')->put($destinationPath, $compressedImage);
            //Select user by token


            $blog = [
                "title" => $data["title"],
                "description" => $data['description'],
                "category" => $data['category'],
                "email" => $data['email'],
                "phone" => $data['phone'],
                "author"=>Session::get("user_id"),
                "img" => $destinationPath,
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
    public function store(StoreBlogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $updatedData = [
            'id' => $request->input('id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'img' => $request->file("img")
        ];
            $img = $request->file("img");
            $compressedImage = $this->compressImage($img, 15);
            $destinationPath = 'images/' . Str::random(60) . '.jpg'; // Replace with the desired destination path within the disk
            Storage::disk('public')->put($destinationPath, $compressedImage);
            $blog = Blog::findOrFail($updatedData["id"]);
            unlink(storage_path('app/public/'. $blog->img));
            $blog->img = $destinationPath;
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
