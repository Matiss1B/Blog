<?php

namespace App\Models\API\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'category',
        'author',
        'user_id',
        'phone',
        'email',
        'img',
    ];
    public function user() {
        return $this->belongsTo(User::class, "user_id", "id")->select(['id', 'name', 'img', 'surname']);
    }
    public function savedBlogsForCurrentUser() {
        return $this->hasMany(SavedBlogs::class, 'blog_id')
            ->where('user_id', 1);
    }
    public function saves(){
        return $this->hasMany(SavedBlogs::class, 'blog_id');
    }
    public function comments(){
        return $this->hasMany(Comments::class, 'blog_id')->with('user')->latest('created_at');
    }
}
