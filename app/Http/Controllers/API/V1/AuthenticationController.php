<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\UserFilter;
use App\Http\Controllers\Controller;
use App\Filters\RequestFilter;
use App\Http\Resources\V1\UsersCollection;
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
            "email"=> "required|max:20|min:5",
            "password"=> "required|max:20|min:9",
            "surname"=>"required|max:15|min:2",
            "name"=>"required|max:15|min:2",

        ]);
        //Set data
        $data = $request->input();
        $email = $data['email'];
        $password = $data['password'];
        $name= $data['name'];
        $surname = $data["surname"];
        $user = [
            "email"=>$email,
            "password"=>$password,
            "name"=> $name,
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
            'password'=> Hash::make($data['password']),
            'surname'=> $data["surname"],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        $request->validate([
            "email"=> "required|max:20|min:5",
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
            ];
            Tokens::create($token);
            return response()->json(["success"=>"OK", "link"=>"home", "user"=>$newToken],200 );
        }else{
            $errors = [
                "invalid" =>'Username or Passeord is incorrect'
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
            "email"=> "max:20|min:5",
            "name"=> "max:20|min:5",
            "password"=> "max:20|min:9",
            "surname"=>"max:20|min:5"
        ]);
        $data = new RequestFilter(["email", "name", "password", "surname"]);
        $data = $data->filter($request);
       if(User::where("id", Session::get("user_id"))->update($data)){
           return response()->json(
               [
                   "statuss"=>200,
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
}
