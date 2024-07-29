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
        // return Socialite::driver('google')
        //     ->with(['hd' => 'http://localhost:8000/auth/google/callback'])
        //     ->redirect("http://localhost:8000/auth/google/callback");
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Check if the user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            //updates or create user account info
           
            $user = User::updateOrCreate([
                'email' => $googleUser->email,
            ], [
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(Str::random(24)),
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);
            Auth::login($user);

            //generates token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token], 200);
        } catch (\Throwable $th) {
            return response()->json(["errors" => "Unable to log in at the moment, try again"]);
        }

        // return redirect('/welcome', 302);
    }
}
