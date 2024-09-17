<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function me() {
        return response()->json([
            'success' => true,
            'message' => 'Login Succesfully',
            'data' =>  auth('api')->user()
        ], 200);
    }
}
