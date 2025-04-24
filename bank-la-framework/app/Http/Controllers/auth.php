<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class Auth extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname'      => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'phone_number'  => 'required|string|max:20|unique:users',
            'password'      => 'required|string|min:6|confirmed',
            'pin'           => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'fullname'        => $request->fullname,
            'email'           => $request->email,
            'phone_number'    => $request->phone_number,
            'password'        => Hash::make($request->password),
            'role_id'         => 2, // default user role
            'account_balance' => 0.00,
            'pin'             => Hash::make($request->pin),
            'status'          => 'active',
            'loan_amount'     => 0.00,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number'  => 'required|string|max:20|exists:users,phone_number',
            'password'      => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('phone_number', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid phone number or password'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        $user = auth()->user();

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
        ]);
    }

    // FORGOT PASSWORD (must have email set)
    public function forgotPassword(Request $request)
    {
        $request->validate(['phone_number' => 'required|string']);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !$user->email) {
            return response()->json(['message' => 'Phone number not associated with an email'], 404);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return response()->json(['message' => __($status)], $status === Password::RESET_LINK_SENT ? 200 : 400);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());

            return response()->json(['message' => 'Logged out successfully']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout, token invalid'], 500);
        }
    }
}
