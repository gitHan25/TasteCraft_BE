<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;
use Illuminate\Support\Str;
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

        ], [
            'email.unique' => 'Email sudah terdaftar',
            'email.required' => 'Email harus diisi',

            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',

        ]);

        try {
            $profile_image = null;
            if ($request->profile_image) {
                $profile_image = $this->uploadBase64Image($request->profile_image);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'File gambar tidak diizinkan',
            ], 400);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $profile_image,
        ]);
        $user->save();

        return response()->json([
            'message' => 'Akun berhasil dibuat!',
            'user' => $user,

        ], 201);
    }

    public function uploadBase64Image($base64Image)
    {
        $decoder = new Base64ImageDecoder($base64Image, $allowedMimeTypes = ['jpg', 'png', 'gif', 'jpeg']);

        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        $decodedImage = $decoder->getDecodedContent();
        if (strlen($decodedImage) > 2 * 1024 * 1024) {
            throw new \Exception('Ukuran gambar maksimal 2MB');
        }

        $format = $decoder->getFormat();
        $image = Str::random(10) . '.' . $format;
        Storage::disk('public')->put($image, $decodedImage);
        return $image;
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
        ], 200);
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
