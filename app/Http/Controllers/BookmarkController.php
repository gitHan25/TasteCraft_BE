<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $bookmarks = $user->bookmarks()->with('recipe')->get();
        return response()->json([
            'status' => 'success',
            'data' => $bookmarks
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'recipe_id' => 'required|exists:recipes,id'
            ]);

            $user = $request->user();
            $bookmark = $user->bookmarks()->firstOrCreate([
                'recipe_id' => $request->input('recipe_id')
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Recipe bookmarked successfully',
                'data' => $bookmark
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $bookmark = $user->bookmarks()->find($id);
        if (!$bookmark) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ], 404);
        }
        $bookmark->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark removed successfully'
        ], 200);
    }
}
