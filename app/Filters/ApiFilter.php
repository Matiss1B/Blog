<?php
namespace App\Filters;
use Illuminate\Http\Request;

class ApiFilter{
    protected $allowed = [];
    protected $columnMap = [];
    protected $operatorMap = [
        'eq'=>'=',
        'lt'=>'<',
        'lte'=>'<=',
        'gt'=>'>',
        'gte'=>'>='
    ];
    public function transform (Request $request) {
        $eloQuery = [];
        foreach ($this->allowed as $parm => $operators) {
            $query = $request->query($parm);
            if (!isset($query)) {
                continue;
            }
            $column = $this->columnMap [$parm] ?? $parm;
            foreach ($operators as $operator) {
                if (isset($query [$operator])) {
                    $eloQuery [] = [$column, $this->operatorMap[$operator], $query [$operator]];
                }
            }
        }
        return $eloQuery;
    }
}

