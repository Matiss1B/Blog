<?php

namespace App\Http\Middleware;

use App\Models\API\V1\Tokens;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Check the token using the checkToken method
        if (self::checkToken($request->input('user'))) {
            // Token is valid, proceed with the request
            return $next($request);
        } else {
            // Token is invalid, handle the error
            return response()->json(["Message"=> "Unauthorised!", "Status"=>401], 401);
        }
    }

    public static function checkToken($user)
    {
        if(Tokens::where("token", $user)->first()){
            return true;
        }
        return false;
    }
}
