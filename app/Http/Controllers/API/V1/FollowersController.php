<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\API\V1\Followers;
use App\Models\API\V1\User;
use App\Models\API\V1\UserTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FollowersController extends Controller
{
    public function toggleFollow(Request $request)
    {
        $request->validate([
            "account_id" => "required"
        ]);
        if(!User::query()->find(request("account_id"))->exists()){
            return response()->json([
                "message"=>"Account doesn't exsist!",
                "status" =>300
            ],300);
        }
        if(request("account_id") == Session::get("user_id")){
            return response()->json([
                "message"=>"Cant follow to your self",
                "status" =>300
            ],300);
        }

        $follow = Followers::query()
            ->where("account_id", request("account_id"))
            ->where("user_id", Session::get("user_id"))
            ->first();
        if($follow){
            $oldFollower = $follow;
            $follow->delete();
            return response()->json([
                "message"=>"Unfollowed succesfully!",
                "follower" => $oldFollower,
                "status" =>201
            ],201);
        }
        $create = Followers::query()->create([
            "account_id" => request("account_id"),
            "user_id" => Session::get("user_id"),
        ]);
        if(!$create){
            return response()->json([
                "message"=>"Unknown error",
                "status" =>300
            ],300);
        }
        return response()->json([
            "message"=>"Followed succesfully!",
            "follower" =>$create,
            "status" =>201,
        ],201);
    }
    function test()
    {
        return response()->json([
            "status" =>201,
            "followers"=>Followers::query()->with("account")->with("user")->get()
        ],201);
    }
    function removeFollower(Request $request)
    {
        $request->validate([
            "follower_id" => "required"
        ]);
        $follower = Followers::query()
            ->where("user_id", request("follower_id"))
            ->where("account_id", Session::get("user_id"))
            ->first();
        if($follower){
            $oldFollower = $follower;
            $follower->delete();
            return response()->json([
                "status" =>201,
                "follower"=>$oldFollower,
            ],201);
        }
        return response()->json([
            "status" =>300,
            "message" => "Follower doesnt exsist",
        ],300);
    }
}

