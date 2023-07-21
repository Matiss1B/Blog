<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\API\V1\Tokens;
use App\Models\API\V1\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
                //Create access token
                $token = [
                    "user_id"=> $newUser->id,
                    "token"=>$newToken,
                ];
                Tokens::create($token);
                //Return in user is created
                return response()->json(["success"=>"OK", "link"=>"home", "user"=>$newToken], 200);

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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
