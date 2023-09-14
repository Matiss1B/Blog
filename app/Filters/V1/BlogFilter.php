<?php
namespace App\Filters\V1;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class BlogFilter extends ApiFilter{
    protected $allowed = [
        'id',
        'title',
        'category',
        'description',
    ];
}

