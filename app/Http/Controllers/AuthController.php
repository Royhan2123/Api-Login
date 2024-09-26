<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get credentials from request
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $token = auth('api')->claims([
            'exp' => Carbon::now()->addSeconds((int)config('jwt.ttl_in_seconds'))->timestamp,
        ])->attempt($credentials, [
            'exp' => Carbon::now()->addSeconds((int)config('jwt.ttl_in_seconds'))->timestamp,
        ]);

        if (!$token) {
            throw new \Exception('Email or password does not match.', 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Succesfully',
            'data' => new LoginResource(
                $token
            )
        ], 200);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'message' => 'Login Succesfully',
            'data' =>  auth('api')->user()
        ], 200);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email'     => 'required|unique:users',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        };

        $create = User::create([
            'name'  => $request->name,
            'email'     => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $token = auth('api')->claims([
            'exp' => Carbon::now()->addSeconds((int)config('jwt.ttl_in_seconds'))->timestamp,
        ])->attempt($credentials, [
            'exp' => Carbon::now()->addSeconds((int)config('jwt.ttl_in_seconds'))->timestamp,
        ]);

        if (!$token) {
            throw new \Exception('Email or password does not match.', 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Succesfully',
            'data' => new LoginResource(
                $token
            )
        ], 200);
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if($removeToken) {
            //return response JSON
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',  
            ], 200);
        }
    }
}
