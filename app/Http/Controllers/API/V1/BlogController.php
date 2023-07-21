<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\BlogFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreBlogRequest;
use App\Http\Resources\V1\BlogsCollection;
use App\Models\API\V1\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = new BlogFilter();
        $filterItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $blogs = Blog::where($filterItems);
        return new BlogsCollection($blogs->paginate()->appends($request->query()));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = $request->input();
        $img = $request->file("img");
        $blog=[
            "title" => $data["title"],
            "description" => $data['description'],
            "category"=> $data['category'],
            "img" => $img->store('images', ['disk' => 'public']),
        ];
        return Blog::create($blog);
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
    public function update(Request $request, $id)
    {
        $updatedData = [
            'id' => $request->input('id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'img'=>$request->file('img'),
        ];
        $blog = Blog::findOrFail($updatedData["id"]);

        $blog->title = $updatedData['title'];
        $blog->description = $updatedData['description'];
        $blog->category = $updatedData['category'];

        if($blog->save()){
            return response()->json([
                "message"=>"Blog updated successfuly!",
                "error"=> "No"
                ]);

        }else{
            return response()->json([
                "message"=>"Something gone wrong!",
                "error"=> $blog->save(),
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
