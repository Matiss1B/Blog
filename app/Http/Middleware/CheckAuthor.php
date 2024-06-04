<?php

namespace App\Http\Middleware;

use App\Models\API\V1\Blog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for 'blog_id' in the request input or 'id' in the query string or route parameters
        if ($request->has('blog_id') || $request->query('id') || $request->route('id')) {
            $blog_id = $request->has('blog_id') ? $request->input('blog_id') : ($request->query('id') ?: $request->route('id'));
            $blog = Blog::query()->find($blog_id);
            if(!$blog || $blog->user_id !== Session::get("user_id")){
                return response()->json([
                    "message"=> "You are not owner or blog doesnt exsist",
                    "status" =>300,
                    "type" => "owner"

                ],300);
            }
            return $next($request);
        } else {
            return response('Blog ID not found', 400);
        }
    }
}
