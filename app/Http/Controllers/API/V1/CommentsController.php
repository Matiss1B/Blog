<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Filters\V1\CommentFilter;
use App\Models\API\V1\Comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CommentsController extends Controller
{
    public $patterns = ["/</", "/>/"];
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = new CommentFilter();
        $filterItems = $filter->transform($request);
        if(Comments::where($filterItems)->get()->isEmpty()){
            return response()->json(
                [
                    "statuss"=>200,
                    "message"=>"Could not find any comments, please check given params"
                ]
            );
        }
        return Comments::where($filterItems)->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $request->validate([
            "comment"=> "required|max:1000",
        ]);
        $data =[
            "comment"=>preg_replace($this->patterns, " ", $request->input("comment")),
            "blog_id"=>$request->input("blog_id"),
            "user_id"=>Session::get('user_id'),
        ];
        if(Comments::create($data)){
            return response()->json(["message"=>"Comment created!",  "comment"=> Comments::latest()->with("user")->first(), "status" => 200], 200);
        }
        return response()->json(["message"=>"Something went wrong!", "status"=>300], 300);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Comments $comments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comments $comments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comments $comments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comments $comment)
    {
        if(Comments::destroy($comment->id)){
            return response()->json(["message"=>"Comment deleted!"], 200);
        }
        return response()->json(
            [
                "message"=>"Something went wrong!",
                "err"=>Comments::destroy($comment->id),
            ]
            , 300);
    }
}
