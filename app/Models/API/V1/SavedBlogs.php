<?php

namespace App\Models\API\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedBlogs extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "blog_id"
    ];
}
