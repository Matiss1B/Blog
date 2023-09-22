<?php
namespace App\Filters\V1;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class CommentFilter extends ApiFilter{
    protected $allowed = [
        'id',
        'comment',
        'user',
    ];
}

