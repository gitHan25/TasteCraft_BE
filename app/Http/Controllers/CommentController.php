<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    /**
     * Display a listing of the resource.
     */
    public function getAllCommentsInRecipe($recipeId)
    {
        $comments = Comment::with('user')
            ->where('recipe_id', $recipeId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $comments
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipe_id' => 'required|exists:recipes,id',
            'content' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $comment = Comment::create([
                'user_id' => $request->user()->id(),
                'recipe_id' => $request->recipe_id,
                'content' => $request->content
            ]);

            $comment->load('user');

            return response()->json([
                'status' => 'success',
                'message' => 'Comment created successfully',
                'data' => $comment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);

            if ($comment->user_id !== $request()->user()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You can only update your own comments.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comment->update([
                'content' => $request->content
            ]);

            $comment->load('user');

            return response()->json([
                'status' => 'success',
                'message' => 'Comment updated successfully',
                'data' => $comment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);

            if ($comment->user_id !== request()->user()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You can only delete your own comments.'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
