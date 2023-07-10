<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\APIHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\User;


class ApiAuthController extends Controller
{

    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $request['password']=Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
    }


    public function show (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('');
                $response = ['token' => $token->plainTextToken];

                $responseData = [
                    'user' => $user,
                    'token' => $token->plainTextToken
                ];
                $responseSuccess = true;
                $responseMessage = "User logged in successfully";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);


            } else {
                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "Password mismatch";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 422);
            }
        } else {
            $responseData = [];
            $responseSuccess = false;
            $responseMessage = "User does not exist";

            return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 422);
          
        }
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}
