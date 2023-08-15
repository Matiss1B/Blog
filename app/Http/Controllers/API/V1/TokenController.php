<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\API\V1\Tokens;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function check(Request $request){
        $data = $request->input();
        $check = Tokens::where("token", $data["token"])->first();
        if($check){
            return response()->json(["success"=>"OK", "link"=>"home"], 200);
        }else{
            return response()->json(["success"=>"ERR", "message"=>"You do not have permissions to this data"], 300);
        }
    }
}
