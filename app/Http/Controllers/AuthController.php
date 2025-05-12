<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'profile_image' => 'image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('profile_image')) {

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $imagePath,
        ]);
        $user->save();

        return response()->json([
            'message' => 'Akun berhasil dibuat!',
            'user' => $user,
            'profile_image_url' => $imagePath ? Storage::url($imagePath) : null,
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',

        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->token = $token;
        $user->token_expires_at = now()->addHours(1);
        $user->save();
        return response()->json([
            'X-API-TOKEN' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function logout(Request $request)
    {


        $request->user()->tokens()->delete();
        $request->user()->token = null;
        $request->user()->token_expires_at = null;
        $request->user()->save();


        return response()->json([
            'message' => 'Berhasil logout',
        ], 200);
    }
}
