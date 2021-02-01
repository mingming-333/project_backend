<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\User;
use App\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\RegistrationFormRequest;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Username' => ['required', 'string', 'max:255'],
            'UserEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'regex:/@(mail.)?(ntu|ntust|ntnu).edu.tw/'],
            'UserPassword' => ['required', 'string', 'min:8', 'confirmed'],
            'UserPhone' => ['required', 'regex:/(0)[0-9]{9}/'],
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 400);
        }

        $user =  User::create([
            'name' => $request->input('Username'),
            'email' => $request->input('UserEmail'),
            'password' => Hash::make($request->input('UserPassword')),
            'phone' => $request->input('UserPhone'),
            'role' => 1,
            'gender' => 1,
            'created_at' => carbon::now(),
            'updated_at' => carbon::now(),
            'UserAvatarPath' => 'preset.png'
        ]);

        event(new Registered($user));

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['user' => $user, 'access_token' => $accessToken]);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'UserEmail' => 'email|required',
            'UserPassword' => 'required'
        ]);

        if($validator->fails())
        {
            return \response($validator->errors(), 400);
        }

        $data = array(
            'email' => $request->input('UserEmail'),
            'password' => $request->input('UserPassword')
        );

        $token = Auth::guard('api')->attempt($data);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        try {
            Auth::guard('api')->invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }

}
