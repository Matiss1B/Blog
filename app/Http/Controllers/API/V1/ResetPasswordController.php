<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
class ResetPasswordController extends Controller
{
    public function __construct(Request $request){
        $request->validate([
            'email' => 'required|email|min:5|max:20',
        ]);
        if (!filter_var($request->input("email"), FILTER_VALIDATE_EMAIL)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Email is not valid',
            ]);
        }
        $randomNumber = mt_rand(1000000000, 9999999999);
        $token = substr(strval($randomNumber), 0, 10);
        Cache::put('password-reset-token:' . $token, true, 60);
        $data =
            [
                'email' => request("email"),
                "username" =>request("name"),
                "resetUrl" => "http://localhost/api/v1/user/password-reset/".$token,
            ];
        $this->data = $data;
        $this->subject = request("subject");
        $this->to = request("email");
        $this->from = 'blogit@info.com';

    }
    public function store()
    {
        if(Mail::send('reset_password', $this->data, function($message)
        {
            $message->from($this->from);
            $message->to($this->to)->subject("Password reset");
        })){
            return response()->json([
                "status"=>200,
                "message"=>"successfully sent",
            ]);
        }

    }
}
