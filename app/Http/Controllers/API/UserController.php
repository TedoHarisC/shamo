<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name'=> $request->name,
                'username'=> $request->username,
                'email'=> $request->email,
                'phone'=> $request->phone,
                'password'=> Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return CustomResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User Registered');

        } catch (Exception $error) {
            return CustomResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }
}
