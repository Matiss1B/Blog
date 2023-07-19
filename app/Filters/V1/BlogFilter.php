<?php
namespace App\Filters\V1;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class BlogFilter extends ApiFilter{
    protected $allowed = [
        'id'=> ['eq'],
        'title'=> ['eq'],
        'category'=> ['eq'],
        'description '=>['eq'],
    ];
    protected $columnMap = [
        'id'=> 'id',
        'title'=>'title',
        'description'=>'description',
    ];
}

