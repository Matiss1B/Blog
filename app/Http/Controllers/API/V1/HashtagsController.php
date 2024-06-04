<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\API\V1\Tag;
use App\Models\API\V1\UserTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HashtagsController extends Controller
{
  public function addUserTags(Request $request)
  {
   $userTags = request("tags");
   foreach ($userTags as $tag){
       $tagDb = Tag::query()->find($tag);
       $create = UserTags::query()->create([
           "tag_id"=>$tag,
           "user_id"=> Session::get("user_id"),
           "tag" => $tagDb->tag
       ]);
       if(!$create){
           break;
       }
   }
   return response()->json([
       "message" => "Tags added",
       "status" => 201,
   ], 201);

  }
}
