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
            'email' => 'required|email',
        ]);
        $token = Str::random(32);
        Cache::put('password-reset-token:' . $token, true, 60);
        $data =
            [
                'email' => request("email"),
                "username" =>request("name"),
                "resetUrl" => "http://localhost/api/v1/user/password-reset/".$token,
            ];
        $this->data = $data;
        $this->subject = request("subject");
        $this->to = 'printmiidesign@info.com';
        $this->from = request("email");

    }
    public function store()
    {
        Mail::send('reset_password', $this->data, function($message)
        {
            $message->from("blogit@info.lv");
            $message->to($this->from)->subject("Password reset");
        });

    }
}
