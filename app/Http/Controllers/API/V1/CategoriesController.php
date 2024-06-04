<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\API\V1\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function get(){
        $categories = Categories::all();
        if($categories){
            return response()->json(
                [
                    "categories"=>$categories,
                    "status"=>200,
                ]
            );
        }else{
            return response()->json(
                [
                    "message"=>"Something gone wrong!",
                    "status"=>300,
                ]
            );
        }
    }
}
