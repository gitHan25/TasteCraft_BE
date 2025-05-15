<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Http\Controllers\AuthController;

class UserController extends Controller
{
    protected $authController;

    public function __construct(AuthController $authController)
    {
        $this->authController = $authController;
    }

    public function getProfileImage(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }


        return response()->json([
            'profile_image_url' => $user->profile_image ? Storage::url($user->profile_image) : null,
        ]);
    }

    public function updateProfileImage(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'profile_image' => 'string',
        ], [
            'profile_image.string' => 'Format gambar tidak valid',
        ]);

        // Delete old image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        try {
            $imagePath = $this->authController->uploadBase64Image($request->profile_image);
            User::where('id', $user->id)->update(['profile_image' => $imagePath]);

            return response()->json([
                'message' => 'Gambar profil berhasil diperbarui',
                'profile_image_url' => Storage::url($imagePath)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
