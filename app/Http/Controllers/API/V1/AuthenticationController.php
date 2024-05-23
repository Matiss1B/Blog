<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\UserFilter;
use App\Functions\ImagesFunctions;
use App\Models\API\V1\Blog;
use App\Models\API\V1\Followers;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Filters\RequestFilter;
use App\Http\Resources\V1\UsersCllection;
use Illuminate\Support\Facades\Auth;
use App\Models\API\V1\Tokens;
use App\Models\API\V1\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthenticationController extends Controller
{
    protected $imagesFunctions;
    public function __construct(){
        $this->imagesFunctions = new ImagesFunctions();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = new UserFilter();
        $filterItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $users = User::where($filterItems);
        return new UsersCollection($users->paginate()->appends($request->query()));
    }
    public function register(Request $request){
        //Validate
        $request->validate([
            "email"=> "required|max:20|min:5|email",
            "password"=> "required|max:20|min:9",
            "surname"=>"required|max:15|min:2",
            "name"=>"required|max:15|min:2",

        ]);
        //Set data
        $data = $request->input();
        $email = $data['email'];
        $password = $data['password'];
        $name = $data['name'];
        $surname = $data["surname"];
        $user = [
            "email"=>$email,
            "password"=>$password,
            "name"=> $name,
            "img"=> "images/DefaultProfileImage.jpg",
            "surname"=> $surname,
        ];
        //Take user
        $userCheck = User::where('email', $email)->first();
        //If user exists, returns Err
        if($userCheck) {
            $errors = [
                "email" => "This email is taken"
            ];
            return response()->json(["success"=>"ERR", "errors"=>$errors], 422);
        }else {
            //Create new user
                $newToken = Str::random(60);
                $this->create($user);
                $newUser = User::where('email', $email)->first();
                //Return in user is created
                return response()->json(["success"=>"OK", "link"=>"home", "user"=>$newToken, "id"=>$newUser->id], 200);

        }
    }
    public function create(array $data){
        return User::create([
            'name'=>$data["name"],
            'email'=> $data['email'],
            'img'=>"images/DefaultProfileImage.jpg",
            'password'=> Hash::make($data['password']),
            'surname'=> $data["surname"],
        ]);
    }
public function redirectPasswordReset($token){
    \Illuminate\Support\Facades\Log::info('Received token: ' . $token);

    if (Cache::get('password-reset-token:' . $token)) {
        \Illuminate\Support\Facades\Log::info('Token found in cache. Removing...');
        Cache::forget('password-reset-token:' . $token);
        return Redirect::to('http://localhost:3000/password-reset/'.$token);
    } else {
        \Illuminate\Support\Facades\Log::info('Token not found in cache. Invalid token.');
        return response()->json(['valid' => false]);
    }
}
public function resetPassword(Request $request){
    $request->validate([
        "password"=> "required|max:20|min:9",
        "confirm_password"=>"required|max:20|min:9",
    ]);
    if($request->input("password") == $request->input("confirm_password")){
        $newPassword = Hash::make($request->input("password"));
        if(User::find(Session::get("user_id"))->update(["password"=>$newPassword])){
            return response()->json(
                [
                    "message" => "Password changed successfully",
                    "status"=>201,
                ],201
            );
        }else{
            return response()->json(
                [
                    "message" => "Something went wrong!",
                    "status"=>300
                ],300
            );
        }
    }else{
            throw \Illuminate\Validation\ValidationException::withMessages([
                'confirm_password' => "Password doesn't match",
            ]);
    }

}


    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        $request->validate([
            "email"=> "required|max:20|min:5|email",
            "password"=> "required|max:20|min:5",
        ]);
        $newToken = Str::random(60);
        $credentials = $request->only("email", "password");
        $userCheck = User::where('email', $request->only('email'))->first();
        if(Auth::attempt($credentials)) {
            $userCheck->update([
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $token = [
                "user_id"=> Auth::id(),
                "token"=>$newToken,
                "created_at"=>date('Y-m-d H:i:s'),
                "updated_at"=>date('Y-m-d H:i:s'),
            ];
            Tokens::create($token);
            return response()->json(["success"=>"OK", "link"=>"home", "user"=>$newToken],200 );
        }else{
            $errors = [
                "invalid" =>'Username or password is incorrect'
            ];
            return response()->json(["success"=>"ERR", 'errors'=>$errors], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function logout(Request $request){
        $data = $request->all();
        if(Tokens::where("token", $data["user"])->delete()) {
            Auth::logout();
            return response()->json(["status" => "ok", "link"=>""], 200);
        }else{
            return response()->json(["status" => "err", "message" => "Something gone wrong"], 403);

        }
    }
    public function edit(Request $request){
         $request->validate([
            "email"=> "max:20|min:5|email",
            "name"=> "max:15|min:2",
            "password"=> "max:20|min:9",
            "surname"=>"max:15|min:2"
        ]);
         $user = User::where("id", Session::get("user_id"))->first();
        $data = new RequestFilter(["email", "name", "password", "surname", "img"]);
        $data = $data->filter($request);
        if (isset($data["password"])){
            $data["password"] = Hash::make($data['password']);
        }
        if (isset($data["img"])){
            $data["img"] = $this->imagesFunctions->compress($data["img"], 15);
            if(!empty($user->img) && $user->img !== "images/DefaultProfileImage.jpg" ) {
                unlink(storage_path('app/public/' . $user->img));
            }
        }
       if(User::where("id", Session::get("user_id"))->update($data)){
           return response()->json(
               [
                   "status"=>200,
                   "message"=>"Profile updated successfully!"
               ]
           );
       }else{
           return response()->json(
               [
                   "statuss"=>300,
                   "message"=>"Something gone wrong!"
               ]
           );
       }
    }

    /**
     * Update the specified resource in storage.
     */
    public function googleredirect(Request $request){
        return Socialite::driver('google')->redirect();
    }
    public function googlecallback(Request $request){

        $user = Socialite::driver('google')->stateless()->user();
        $userCheck = User::where('google_id', $user->getId())->first();
        if(!$userCheck){
            User::create([
                'name'=>$user->getName(),
                'email'=> $user->getEmail(),
                'google_id'=>$user->getId(),
            ]);
            return response()->json(["message" => "User logged in"], 300);
        }else{
            return response()->json(["message" => "User logged in"], 300);
        }
    }
    public function Fbredirect(Request $request){
        return Socialite::driver('facebook')->redirect();
    }
    public function Fbcallback(Request $request){
        $user = Socialite::driver('facebook')->stateless()->user();
        $userCheck = User::where('facebook_id', $user->getId())->first();
        if(!$userCheck){
            User::create([
                'name'=>$user->getName(),
                'email'=> $user->getEmail(),
                'facebook_id'=>$user->getId(),
            ]);
            return response()->json(["message" => "User logged in"], 300);
        }else{
            return response()->json(["message" => "User logged in"], 300);
        }

    }
    public function get()
    {
        return User::with('blogs')->find(Session::get("user_id"));
    }
    public function getUserAccount(Request $request)
    {
        $user_id =Session::get("user_id");
        $profile = User::with('blogs')->find(request("account_id"));
        $followers = Followers::query()->where("account_id", request("account_id"))->with("user")->get();
        $following = Followers::query()->where("user_id", request("account_id"))->with("account")->get();
        $profile->followers = $followers;
        $profile->following = $following;
        $isFollower = $followers->contains(function ($follower) use ($user_id) {
            return $follower->user_id == $user_id;
        });

        return response()->json([
            "profile"=>$profile,
            "isFollower"=>$isFollower,
            "status" =>201,
        ],201);

    }
    public function getProfile(Request $request)
    {
        $user_id =Session::get("user_id");
        $profile = User::with('blogs')->find($user_id);
        $followers = Followers::query()->where("account_id", $user_id)->with("user")->get();
        $following = Followers::query()->where("user_id", $user_id)->with("account")->get();
        $profile->followers = $followers;
        $profile->following = $following;

        return response()->json([
            "profile"=>$profile,
            "status" =>201,
        ],201);

    }
}
