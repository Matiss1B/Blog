<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
<p>Hello {{ $username }},</p>

<p>You are receiving this email because we received a password reset request for your account.</p>

<p>
    If you did not request a password reset, no further action is required.
</p>

<p>
    To reset your password, click the button below:
</p>

<a href="{{$resetUrl}}" style="
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        color: #ffffff;
        background-color: #007bff;
        border-radius: 5px;
        cursor: pointer;
    ">
    Reset Password
</a>

<p>
    If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
</p>

<p>{{$resetUrl}}</p>

<p>
    This password reset link will expire in {{ config('auth.passwords.users.expire') }} minutes.
</p>

<p>
    If you did not request a password reset, no further action is required.
</p>

<p>
    Best regards,<br>
    Your Application Name
</p>
</body>
</html>
