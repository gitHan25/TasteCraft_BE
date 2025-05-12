<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    
    
    public function getProfileImage(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }


        return response()->json([

            'profile_image_url' => $user->profile_image ? Storage::url($user->profile_image) : null,
        ]);
    }
}
