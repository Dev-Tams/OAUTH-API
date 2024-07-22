<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\PasswordResetLink;
use App\Actions\RateLimitAction;
use App\Http\Requests\EmailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{


    //invokes the rate limit action for password reset
     protected $rateLimitAction;

    public function __construct(RateLimitAction $rateLimitAction)
    {
        $this->rateLimitAction = $rateLimitAction;
    }
    /**
     * Authorization
     * 
     * send password reset link to email
     * 
     * 
     * @response 200 {
     *  "message": "Email Sent"
     * }
     * 
     * 
     */
    public function passlink(EmailRequest $request)
    {

        $rateLimitResponse = $this->rateLimitAction
            ->rateLimit('password-reset:' .
                $request->ip(), 3, 1);

                //checks for rate limit and return response
                if ($rateLimitResponse) {
                    return $rateLimitResponse;
                }

        //fetches user mail
        if ($user = User::whereEmail($request->email)->first()) {

            //generates user token & check/replace if token already exists
            do {
                $ref = Str::random(32);
            } while (User::wherePasswordResetToken($ref)->exists());
            $user->update([
                "password_reset_token" => $ref
            ]);

            //sends reset token to user mail
            Mail::to($user->email)
                ->send(new PasswordResetLink($user));

            return response()->json(["message" => "Check your mail", 200]);

        } else {
            return response()->json(["message" => "You may want to check that mail again"]);
        }
}



    /**
     * 
     * resets user password 
     * 
     * Response 200
     */
    public function reset($request)
    {
        $user = User::whereEmail($request->email);
    }

    /**
     * 
     * updates useer password
     * 
     * Response 200
     */
    public function update() {}
}
