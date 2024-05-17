<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\BlogFilter;
use App\Functions\ImagesFunctions;
use App\Http\Controllers\Controller;
use App\Models\API\V1\BlogTag;
use App\Models\API\V1\Comments;
use App\Models\API\V1\SavedBlogs;
use App\Models\API\V1\Tag;
use App\Models\API\V1\UserTags;
use http\Env\Response;
use PhpOffice\PhpWord\IOFactory;
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
    function hasMatchingLetters($str, $array) {
        foreach ($array as $item) {
            if ($str === $item) {
                return true;
            }
        }
        foreach ($array as $item) {
            $len1 = strlen($str);
            $len2 = strlen($item);

            // Iterate through the characters of the first string
            for ($i = 0; $i <= $len1 - 3; $i++) {
                $substring1 = substr($str, $i, 3);

                // Iterate through the characters of the second string
                for ($j = 0; $j <= $len2 - 3; $j++) {
                    $substring2 = substr($item, $j, 3);

                    // Compare the substrings for a match
                    if ($substring1 === $substring2) {
                        return true;
                    }
                }
            }
        }

        return false;
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
        foreach ($blogs as $blog){
            $blogTags = BlogTag::query()->where("blog_id", "=", $blog->id)->pluck("tag_id");
            $tags = Tag::query()->whereIn("id", $blogTags)->get();
            $blog->tags = $tags;
        }
        return $blogs;
    }
    /**
     * Show the form for creat  ing a new resource.
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
                "author"=>Session::get("user_id"),
                "user_id"=>Session::get("user_id"),
                "img" => $this->imagesFunctions->compress($img, 15),
            ];
            if($request->input("email")){
                $data["email"] = $request->input("email");
            }
            if($request->input("phone")){
                $data["phone"] = $request->input("phone");
            }
            $create = Blog::create($blog);
            if($create){
                if($request->input("tags")){
                    foreach (explode(",", $request->input("tags")) as $tag) {
                        $exist = Tag::query()->where("tag", "=", $tag)->first();

                        if (!$exist) {
                            $newTag = Tag::query()->create([
                                "tag" => $tag
                            ]);
                            $tagId = $newTag->id;
                        } else {
                            $tagId = $exist->id;
                        }

                        BlogTag::query()->create([
                            "tag_id" => $tagId,
                            "blog_id" => $create->id
                        ]);
                    }

                }
                return response()->json(["message"=> "Blog created successfully"], 200);
            }else{
                return response()->json(["message"=> "Something gone wrong", "error"=>Blog::create($blog)], 300);
            }

    }
    public function getForYou()
    {
        //Get last user whatched tags
        $id = Session::get("user_id");
        $userTags = UserTags::query()
            ->where('user_id', $id)
            ->latest()
            ->take(20)
            ->get()
            ->unique('tag')
            ->pluck('tag')
            ->take(10);
        //Take also similar tags for
        $allTags = Tag::query()->get();
        $similarTags = [];
        foreach ($allTags as $tag) {
            if($this->hasMatchingLetters($tag->tag, $userTags)){
                array_push($similarTags, $tag->id);
            }
        }
        //Take unique blogs
        $uniqueBlogIds = BlogTag::query()
            ->whereIn('tag_id', $similarTags)
            ->pluck('blog_id')
            ->unique()
            ->values();
        $blogs = Blog::with('user')->whereIn("id", $uniqueBlogIds)
            ->whereNot("user_id", Session::get("user_id"))
            ->get();

        return $blogs;


    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request)
    {
        $data = [
            "user_id"=> Session::get("user_id"),
            "blog_id"=> request("blog_id"),
        ];
        $record = SavedBlogs::where('user_id', $data['user_id'])
            ->where('blog_id', $data['blog_id'])
            ->first();

        if ($record) {
            if($record->delete()){
                return response()->json([
                    "message"=>"Saved successfully!",
                    "status"=>200,
                ]);
            }
            return response()->json([
                "message"=>"Error",
                "status"=>300,
            ]);
        }
        if(SavedBlogs::create($data)){
            return response()->json([
                "message"=>"Saved successfully!",
                "status"=>200,
            ]);
        }
        return response()->json([
            "message"=>"Error",
            "status"=>300,
        ]);
    }
    public function getSaved(Request $request, $id){
        $userId = Session::get("user_id");

        $blogs = Blog::with(['savedBlogsForCurrentUser' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->with("user")
            ->with("saves")
            ->with("comments")
            ->find($id);
        $blogTags = BlogTag::query()->where("blog_id", "=", $blogs->id)->pluck("tag_id");
        $tags = Tag::query()->whereIn("id", $blogTags)->get();

        return response()->json([
            "data"=> $blogs,
            "tags"=>$tags,
            "status"=> 201,
        ], 201);
    }
    public function getAllSaved(Request $request){
        $saved = SavedBlogs::where("user_id",'=', Session::get("user_id"))
            ->get()
            ->pluck("blog_id");
        if($saved){
            $blogs = Blog::whereIn('id', $saved)
                ->with("user")
                ->get();
            foreach ($blogs as $blog){
                $blogTags = BlogTag::query()->where("blog_id", "=", $blog->id)->pluck("tag_id");
                $tags = Tag::query()->whereIn("id", $blogTags)->get();
                $blog->tags = $tags;
            }
            return $blogs;
        }
    }

    public function test(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt,docx|max:10240', // Adjust max file size as needed
        ]);

        // Get the content of the uploaded file
        $file = $request->file('file');
        $content = $this->readDocxFile($file);

        // You can now use $content as needed, e.g., display it or process it
        return response()->json(['content' => $content]);
    }
    private function readDocxFile($file)
    {
        $phpWord = IOFactory::load($file->getPathname());

        // Debugging: Dump sections
        dd($phpWord->getSections());

        // Extract the text content from the Word document
        $content = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $content .= $element->getText() . ' ';
            }
        }

        return $content;
    }

        public function update(Request $request)
    {
        $request->validate([
            "title"=> "required|max:50|min:4",
            "description"=> "required|max:4000|min:4",
            "category"=> "required|max:20|min:4",
            "phone"=> "min:8|max:13",
            "email"=> "min:5|max:20",
        ]);
        $updatedData = [
            'id' => $request->input('id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
        ];
            $img = $request->file("img");
            $blog = Blog::findOrFail($updatedData["id"]);
            if(isset($img)) {
                $imagePath = storage_path('app/public/' . $blog->img);
                unlink($imagePath);
                $blog->img = $this->imagesFunctions->compress($img, 15);
            }
            $blog->title = $updatedData['title'];
            $blog->description = $updatedData['description'];
            $blog->category = $updatedData['category'];
            if($request->input("tags")){
                $blogTags = BlogTag::query()->where("blog_id", $blog->id);
                if($blogTags->exists()) {
                    if (!$blogTags->delete()) {
                        return response()->json([
                            "message" => "Failed to delete blog tags",
                            "status" => 300
                        ], 300);
                    }
                }
                foreach (explode(",", $request->input("tags")) as $tag) {
                    $exist = Tag::query()->where("tag", "=", $tag)->first();

                    if (!$exist) {
                        $newTag = Tag::query()->create([
                            "tag" => $tag
                        ]);
                        $tagId = $newTag->id;
                    } else {
                        $tagId = $exist->id;
                    }

                    BlogTag::query()->create([
                        "tag_id" => $tagId,
                        "blog_id" => $blog->id
                    ]);
                }

            }

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
    public function destroy($id)
    {
        $blog = Blog::find($id);

        if ($blog) {
            Comments::where("blog_id", "=", $id)->delete();
            SavedBlogs::where("blog_id", "=", $id)->delete();
            $imagePath = storage_path('app/public/' . $blog->img);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $blog->delete();
            return response()->json([
                "message"=>"Blog deleted successfully!",
                "status"=>200,
            ]);
        }

        return response()->json([
            "message"=>"Something went wrong!",
            "status"=>300,
        ]);
    }

}
