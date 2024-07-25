<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\PasswordResetLink;
use App\Actions\RateLimitAction;
use App\Http\Requests\EmailRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;

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

            $url = "passwords/reset?token=" . $user->password_reset_token;
            //sends reset token to user mail
            Mail::to($user->email)
                ->send(new PasswordResetLink($user, $url));

            $response =  ["message" => "Email Sent",
                         "status"  =>          200];

            return response()->json([$response]);

        } else {
            return response()->json(["message" => "You may want to check that mail again"]);
        }
}



    /**
     * 
     * Resets user password 
     * 
     * Reset token expires by an hr
     * 
     * Response 200
     */
    public function resetPassword(ResetPasswordRequest $request)
    {

        //rate limits 
        $rateLimitResponse = $this->rateLimitAction
            ->rateLimit('password-reset:' .
                $request->ip(), 3, 1);

                //checks for rate limit and return response
                if ($rateLimitResponse) {
                    return $rateLimitResponse;
                }

            
        //gets token
        $reset = User::wherePasswordResetToken($request->token)->first();
       //validates token token matches 


       //checks if the token has expired (lasts for an hour)
        if(Carbon::parse($reset->created_at)->isLastHour()){
            return response()->json([
                "error" => "Sorry, password reset Link has expired"
            ]);
        }

            

       if ($reset == true){
        //updates password
        $reset->update([
            "password" => bcrypt($request->password),
            "password_reset_token" => null,
            "password_set" => true,     
        ]);
        //200 res
        return response()->json(["message" => "Password Updated Successfully", 200]);

    }else{
        return response()->json(['message' => 'We cannot match your request at this moment, try again.'], 401);
    }
  }

    /**
     * 
     * updates user password
     * 
     * Response 200
     */
    public function updatePassword(UpdatePasswordRequest $request) 
    {
           //rate limits 
        $rateLimitResponse = $this->rateLimitAction
        ->rateLimit('password-reset:' .
            $request->ip(), 3, 1);

            //checks for rate limit and return response
            if ($rateLimitResponse) {
                return $rateLimitResponse;
            }
            
        //checks if user is auth

        /** @var User $user */
   
        $user = Auth::user();
        
        
        //requests old password + new password
        $validatedData = $request->validated();

        
        //Checks for old password
            if(!Hash::check($validatedData['old_password'], $user->password)){
                return response()->json(["error" => "Sorry, old password doesn't match"]);
            }
       if($user == Auth::user($validatedData)){
            $user->update([
                "password" => bcrypt($validatedData['password']),
                "password_reset_token" => null,
                "password_set" => true,     
            ]);

            //deletes (old) and regenetate bearer token 
            $user->tokens()->delete();
            $token =  $user->createToken("API TOKEN")
                ->plainTextToken;
            return response()->json(["Password Updated", 200])
                ->header('Authorization', "Bearer $token");
        }
    }
}
