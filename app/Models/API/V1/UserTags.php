<?php

namespace App\Models\API\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTags extends Model
{
    use HasFactory;
    protected $fillable = [
        "tag_id",
        "tag",
        "user_id",
    ];
}
