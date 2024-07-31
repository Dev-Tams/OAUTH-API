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
 * Create a new user.
 * 
 * @group User Management
 * 
 * @transformer 200 App\Transformers\UserTransformer
 * @transformerModel App\Models\User resourceKey=user
 * 
 * @response 201 {
 *   "name": "John Doe",
 *   "email": "johndoe@example.com",
 *   "password" : "password-any"
 *   "created_at": "2021-01-01T00:00:00.000000Z",
 *   "updated_at": "2021-01-01T00:00:00.000000Z"
 * }
 * 
 * @response 401 {
 *   "errors": "wrong input"
 * }
 */

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
 * Logs in a new user.
 * 
 * @group User Management
 * 
 * @transformer 200 App\Transformers\UserTransformer
 * @transformerModel App\Models\User resourceKey=user
 * 
 * @response 201 {
 *   "email": "johndoe@example.com",
 *   "password" : "password-any"
 * }
 * 
 * @response 401 {
 *   "errors": "invalid login details"
 * }
 */

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
     * Logs out a user
     * 
     * @authenticated
     *invalidates session
     * deletes token 
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
