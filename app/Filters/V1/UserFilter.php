<?php
namespace App\Filters\V1;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class UserFilter extends ApiFilter{
    protected $allowed = [
        'id'=> ['eq'],
        'name'=> ['eq'],
        'email'=> ['eq'],
    ];
}

