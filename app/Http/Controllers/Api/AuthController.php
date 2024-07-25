<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Transformers\UserTransformer;

class AuthController extends Controller
{
    /**
     * @ api endpoint to create a user
     * 
     * @transformer 200 App\Transformers\Api\UserTransformer
     * @transformerModel App\Models\User resourceKey=user
     * 
     * @response 401 {
     *    "errors": {
     *      "key": ["Wrong input"]
     *    }
     *  }
     **/

    public function register(StoreRequest $request, UserTransformer $transformer)
    {

        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        $responseData = $transformer->transform($user);

        return response()->json($responseData, 201)->header(
            'Authorization',
            "Bearer $token",

        )->header("Access-Control-Expose-Headers", "Authorization");
    }


    /**
     * @ Logins a user
     * 
     * @transformer 200 App\Transformers\Api\UserTransformer
     * @transformerModel App\Models\User resourceKey=user
     * 
     * @response 401 {
     *    "errors": {
     *      "key": ["Wrong input"]
     *    }
     *  }
     **/

    public function store(LoginRequest $request, UserTransformer $transformer)
    {
        //fetch user details,
        $validatedData = $request->validated();
        //verify details
        if (Auth::attempt($validatedData)) {
            /** @var User $user */

            //login user,  return auth token
            $user = Auth::user();
            $token = $user->createToken('API Token')->plainTextToken;
            $responseData = $transformer->transform($user);


            return response()->json($responseData, 200)->header(
                'Authorization',
                "Bearer $token",
            )->header("Access-Control-Expose-Headers", "Authorization");
        } else {

            //returns errror
            return response()->json(["error" => "Invalid login details"], 401);
        };
    }


    /**
     * @ Logs out a user
     * 
     * @authenticated
     *invalidates session
     * @ deletes token 
     * @response 200 {
     *  "message": "Successfully logged out"
     * }
     **/
    public function logout(Request $request)
    {

        /** @var User $user */

        $request->user()->tokens()->delete();
       // $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);

    }
}
