<?php
use Illuminate\Http\Request;
use App\Http\Middleware\CheckToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

    Route::get('/test-email', function () {
        try {
            Mail::raw('This is a test email', function ($message) {
                $message->from('matissbalins1@gmail.com', 'Name');
                $message->to('ip20.m.balins@vtdt.edu.lv')->subject('Test Email');
            });

            return 'Email sent successfully';
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return 'Error sending email: ' . $e->getMessage();
        }
    });
    //Token
    Route::post('/checkToken',[\App\Http\Controllers\API\V1\TokenController::class, "check"]);
    Route::post('/setup',[\App\Http\Controllers\API\V1\TokenController::class, "setup"]);
    Route::get('/online',[\App\Http\Controllers\API\V1\TokenController::class, "getOnline"])->middleware(CheckToken::class);
    //Authentification
    Route::apiResource('users', \App\Http\Controllers\Api\V1\AuthenticationController::class)->middleware(CheckToken::class);
    Route::post('/register',[\App\Http\Controllers\API\V1\AuthenticationController::class, "register"]);
    Route::post('/login',[\App\Http\Controllers\API\V1\AuthenticationController::class, "login"]);
    Route::post('/logout',[\App\Http\Controllers\API\V1\AuthenticationController::class, "logout"]);
    Route::get('/user/get',[\App\Http\Controllers\API\V1\AuthenticationController::class, "get"])->middleware(CheckToken::class);
    Route::put('/user/edit',[\App\Http\Controllers\API\V1\AuthenticationController::class, "edit"])->middleware(CheckToken::class);
    Route::post('/user/password-reset-mail', [\App\Http\Controllers\API\V1\ResetPasswordController::class, "sendResetLinkEmail"])->middleware(CheckToken::class);
    Route::get('/user/password-reset/{token}', [\App\Http\Controllers\API\V1\AuthenticationController::class, "redirectPasswordReset"]);
    Route::post('/user/password-reset', [\App\Http\Controllers\API\V1\AuthenticationController::class, "resetPassword"])->middleware(CheckToken::class);
    Route::post('/user/account', [\App\Http\Controllers\API\V1\AuthenticationController::class, "getUserAccount"])->middleware(CheckToken::class);
    Route::get('/user/profile', [\App\Http\Controllers\API\V1\AuthenticationController::class, "getProfile"])->middleware(CheckToken::class);

    //Blogs
    Route::apiResource('blogs', \App\Http\Controllers\Api\V1\BlogController::class)->middleware(CheckToken::class);
    Route::get('blog/view/{id}', [\App\Http\Controllers\API\V1\BlogController::class, "handleView"])->middleware(CheckToken::class);
    Route::get('blog/for/edit', [\App\Http\Controllers\API\V1\BlogController::class, "index"])->middleware(CheckToken::class)->middleware(\App\Http\Middleware\CheckAuthor::class);
    Route::post('create', [\App\Http\Controllers\API\V1\BlogController::class, "create"])->middleware(CheckToken::class);
    Route::get('blog/for',[\App\Http\Controllers\API\V1\BlogController::class, 'getForYou'])->middleware(CheckToken::class);
    Route::post("blog/edit",[\App\Http\Controllers\API\V1\BlogController::class, 'update'])->middleware(CheckToken::class);
    Route::post('blog/save',[\App\Http\Controllers\API\V1\BlogController::class, 'save'])->middleware(CheckToken::class);
    Route::get('blog/get/{id}',[\App\Http\Controllers\API\V1\BlogController::class, 'getSaved'])->middleware(CheckToken::class);
    Route::post('blog/file/test', [\App\Http\Controllers\API\V1\BlogController::class, 'test']);
    Route::get('blog/get/all/saved', [\App\Http\Controllers\API\V1\BlogController::class, 'getAllSaved'])->middleware(CheckToken::class);
    Route::get('blog/followers', [\App\Http\Controllers\API\V1\BlogController::class, 'getFollowers'])->middleware(CheckToken::class);
    Route::get('blog/delete/{id}',[\App\Http\Controllers\API\V1\BlogController::class, 'destroy'])->middleware(CheckToken::class);
    //Comments
    Route::apiResource('comments', \App\Http\Controllers\Api\V1\CommentsController::class)->middleware(CheckToken::class);
    Route::post('/comment/create', [\App\Http\Controllers\API\V1\CommentsController::class, "create"])->middleware(CheckToken::class);
    Route::delete('/comment/delete/{comment}',[\App\Http\Controllers\API\V1\CommentsController::class, "destroy"])->middleware(CheckToken::class);
    //Categories
    Route::get("/categories/get",[\App\Http\Controllers\API\V1\CategoriesController::class, "get"])->middleware(CheckToken::class);
    //HashTags
    Route::post('/tags/add', [\App\Http\Controllers\API\V1\HashtagsController::class, "addUserTags"])->middleware(CheckToken::class);
    //Followers
    Route::post('/follow/toggle', [\App\Http\Controllers\API\V1\FollowersController::class, "toggleFollow"])->middleware(CheckToken::class);
    Route::post('/follow/remove', [\App\Http\Controllers\API\V1\FollowersController::class, "removeFollower"])->middleware(CheckToken::class);;


});
