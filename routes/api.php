<?php
use Illuminate\Http\Request;
use App\Http\Middleware\CheckToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Blogs
Route::group(['prefix'=> 'v1', 'namespace'=>'App\Http\Controllers\Api\V1'], function () {
    //Token
    Route::post('/checkToken',[\App\Http\Controllers\API\V1\TokenController::class, "check"]);
    //Authentification
    Route::apiResource('users', \App\Http\Controllers\Api\V1\AuthenticationController::class)->middleware(CheckToken::class);
    Route::post('/register',[\App\Http\Controllers\API\V1\AuthenticationController::class, "register"]);
    Route::post('/login',[\App\Http\Controllers\API\V1\AuthenticationController::class, "login"]);
    Route::post('/logout',[\App\Http\Controllers\API\V1\AuthenticationController::class, "logout"]);
    Route::post('/user/edit',[\App\Http\Controllers\API\V1\AuthenticationController::class, "edit"])->middleware(CheckToken::class);
    //Blogs
    Route::apiResource('blogs', \App\Http\Controllers\Api\V1\BlogController::class)->middleware(CheckToken::class);;
    Route::post('create', [\App\Http\Controllers\API\V1\BlogController::class, "create"])->middleware(CheckToken::class);;
    Route::post("/edit",[\App\Http\Controllers\API\V1\BlogController::class, 'update'])->middleware(CheckToken::class);
    //Comments
    Route::apiResource('comments', \App\Http\Controllers\Api\V1\CommentsController::class)->middleware(CheckToken::class);
    Route::post('/comment/create', [\App\Http\Controllers\API\V1\CommentsController::class, "create"])->middleware(CheckToken::class);
    Route::delete('/comment/delete/{comment}',[\App\Http\Controllers\API\V1\CommentsController::class, "destroy"])->middleware(CheckToken::class);


});
