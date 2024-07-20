<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Http\Request;

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

    public function register(StoreRequest $request ){

        $validatedData = $request->validated();
      
        $register = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => $validatedData['password'],
        ]);

        return response()
        ->json($register, 201);
    }

        
}
