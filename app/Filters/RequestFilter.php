<?php
namespace App\Filters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class RequestFilter{
    protected $allowed;
    public function __construct($data)
    {
        $this->allowed = $data;
    }

    public function filter($request){
        $filteredArrray = [];
        $requestArray = json_decode(json_encode($request->input()), true);
        foreach ($requestArray as $key => $unit){
            if(in_array($key, $this->allowed)){
                $filteredArrray[$key] = $unit;
            }
        }
        return $filteredArrray;
    }
}


