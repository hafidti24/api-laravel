<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' =>
            ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        return response()->json(
            ['message' => 'registered', 'data' => $user],
            201
        );
    }

    public function login(Request $r)
    {
        $cred = $r->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $user = User::where('email', $cred['email'])->first();
        if (!$user || !Hash::check($cred['password'], $user->password)) {
            return response()->json(
                ['message' => 'invalid credentials'],
                401
            );
        }
        // Personal access token
        $token = $user->createToken('api')->plainTextToken;
        return response()->json([
            'message' => 'ok',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ]);
    }

    public function me(Request $r)
    {
        return response()->json(['data' => $r->user()]);
    }
    public function logout(Request $r)
    {
        // hapus token yang dipakai saat ini
        $r->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'logged out']);
    }
}
