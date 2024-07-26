<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->stateless() 
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $th) {
            return response()->json(["errors" => "Unable to log in at the moment, try again"]);
        }
       

          // Check if the user exists by email
          $user = User::where('email', $googleUser->getEmail())->first();

        //updates or create user account info
          $user = User::updateOrCreate([
            'google_id' => $googleUser->id,
        ], [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'password' => Hash::make(Str::random(24)),
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);

        Auth::login($user);
        
        //generates token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token], 200);
 
       // return redirect('/welcome', 302);
    }
}
