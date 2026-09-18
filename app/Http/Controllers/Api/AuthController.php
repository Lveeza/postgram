<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function user(Request $request)
    {
        return $request->user();
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Registration successful',
            'token' => $user->createToken('auth_token', ['posts:create', 'posts:update', 'posts:delete'])->plainTextToken,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->createToken('auth_token', [
            'posts:create',
            'posts:update',
            'posts:delete',
            'stories:create',
            'stories:delete',
        ])->plainTextToken;


        return response()->json([
            'message' => 'Login successful!',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    // Handle Logout (Revoke Token)
    public function logout(Request $request)
    {
        // Revoke the token used for the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
