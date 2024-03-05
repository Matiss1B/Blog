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
    Route::get('/user/get',[\App\Http\Controllers\API\V1\AuthenticationController::class, "get"])->middleware(CheckToken::class);
    Route::put('/user/edit',[\App\Http\Controllers\API\V1\AuthenticationController::class, "edit"])->middleware(CheckToken::class);
    Route::post('/user/password-reset-mail', [\App\Http\Controllers\API\V1\ResetPasswordController::class, "store"])->middleware(CheckToken::class);
    Route::get('/user/password-reset/{token}', [\App\Http\Controllers\API\V1\AuthenticationController::class, "redirectPasswordReset"]);
    Route::post('/user/password-reset', [\App\Http\Controllers\API\V1\AuthenticationController::class, "resetPassword"])->middleware(CheckToken::class);
    //Blogs
    Route::apiResource('blogs', \App\Http\Controllers\Api\V1\BlogController::class)->middleware(CheckToken::class);
    Route::post('create', [\App\Http\Controllers\API\V1\BlogController::class, "create"])->middleware(CheckToken::class);
    Route::post("blog/edit",[\App\Http\Controllers\API\V1\BlogController::class, 'update'])->middleware(CheckToken::class);
    Route::post('blog/save',[\App\Http\Controllers\API\V1\BlogController::class, 'save'])->middleware(CheckToken::class);
    Route::get('blog/get/{id}',[\App\Http\Controllers\API\V1\BlogController::class, 'getSaved'])->middleware(CheckToken::class);
    Route::post('blog/file/test', [\App\Http\Controllers\API\V1\BlogController::class, 'test']);
    Route::get('blog/get/all/saved', [\App\Http\Controllers\API\V1\BlogController::class, 'getAllSaved'])->middleware(CheckToken::class);
    Route::get('blog/delete/{id}',[\App\Http\Controllers\API\V1\BlogController::class, 'destroy'])->middleware(CheckToken::class);
    //Comments
    Route::apiResource('comments', \App\Http\Controllers\Api\V1\CommentsController::class)->middleware(CheckToken::class);
    Route::post('/comment/create', [\App\Http\Controllers\API\V1\CommentsController::class, "create"])->middleware(CheckToken::class);
    Route::delete('/comment/delete/{comment}',[\App\Http\Controllers\API\V1\CommentsController::class, "destroy"])->middleware(CheckToken::class);
    //Categories
    Route::get("/categories/get",[\App\Http\Controllers\API\V1\CategoriesController::class, "get"])->middleware(CheckToken::class);


});
