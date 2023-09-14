<?php
namespace App\Filters;
use Illuminate\Http\Request;

class ApiFilter{
    protected $allowed = [];
    private $allowedSymbols = [
        ">",
        "<",
    ];
    public function transform (Request $request) {
        $queries = [];
        $finalQueries = [];
        foreach ($this->allowed as $param) {
            $query = $request->input($param);
           if ($query == null) {
                continue;
            }else{
                $queries[$param] = $query;
            }
        }
        foreach ($queries as $queryKey => $query){
            if(is_array($query)){
                foreach ($query as $key => $value) {
                    $symbol = array_search($key, $this->allowedSymbols);
                    if ($symbol !== false) {
                        $finalQueries[] = [$queryKey, $key, $value];
                    }
                    break;
                }
            }else{
                $finalQueries[] = [$queryKey, "=", $query];
            }
        }
        return $finalQueries;

    }
}


