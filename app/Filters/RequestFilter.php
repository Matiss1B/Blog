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
        $filteredArray = [];
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $filteredArray['img'] = $file;
        }
        $requestInputs = json_decode(json_encode($request->input()), true);
        foreach ($requestInputs  as $key => $unit){
            if(in_array($key, $this->allowed)){
                $filteredArray[$key] = $unit;
            }
            if ($request->hasFile('img')) {
                $file = $request->file('img');
                $filteredArray['img'] = $file;
            }
        }
        return $filteredArray;
    }
}


