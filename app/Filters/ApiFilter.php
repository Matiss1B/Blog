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
        //Loop through all keys what are allowed for filtration
        foreach ($this->allowed as $param) {
            $query = $request->query($param);
            //If key what is allowed to filtration is in url, then save it
           if ($query == null) {
                continue;
            }else{
                $queries[$param] = $query;
            }
        }
        //Loop through all alowed url queries, transform and push into $finalQueries array
        foreach ($queries as $queryKey => $query){
            //if query is an array, it means, that there is special symbol, like ">" or "<", Example(id[>]=1)
            if(is_array($query)){
                foreach ($query as $key => $value) {
                    $symbol = array_search($key, $this->allowedSymbols);
                    if ($symbol !== false) {
                        $finalQueries[] = [$queryKey, $key, $value];
                    }
                    break;
                }
                //If its not an array, that means, we need return "=", Example(id=1)
            }else{
                $finalQueries[] = [$queryKey, "=", $query];
            }
        }
        return $finalQueries;

    }
}


