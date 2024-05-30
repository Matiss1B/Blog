<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResetPasswordController extends Controller
{
    protected $from;

    public function __construct()
    {
        $this->from = 'blogit@info.com';
    }

    public function sendResetLinkEmail(Request $request)
    {
        // Validate the request inputs
        $validated = $request->validate([
            'email' => 'required|email|min:5|max:40',
        ]);

        // Generate a random token
        $randomNumber = mt_rand(1000000000, 9999999999);
        $token = substr(strval($randomNumber), 0, 10);
        Cache::put('password-reset-token:' . $token, true, 60);

        // Prepare the email data
        $data = [
            'email' => $request->input("email"),
            'username' => 'Karlis',
            'resetUrl' => url('/api/v1/user/password-reset/' . $token),
        ];

        // Send the reset password email
        return $this->sendEmail($data, "Email reset", $request->input("email"));
    }

    protected function sendEmail(array $data, string $subject, string $to)
    {
        try {
            Mail::send('reset_password', $data, function ($message) use ($subject, $to) {
                $message->from($this->from);
                $message->to($to)->subject($subject);
            });

            if (Mail::failures()) {
                Log::error('Mail failures: ', Mail::failures());
                return response()->json([
                    "status" => 500,
                    "message" => "Error sending email. Failed to deliver to recipient.",
                ], 500);
            }

            return response()->json([
                "status" => 200,
                "message" => "Email successfully sent",
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return response()->json([
                "status" => 500,
                "message" => "Error sending email: " . $e->getMessage(),
            ], 500);
        }
    }
}


