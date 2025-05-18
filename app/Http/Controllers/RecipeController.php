<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Http\Controllers\AuthController;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RecipeController extends Controller
{
    protected $authController;

    public function __construct(AuthController $authController)
    {
        $this->authController = $authController;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Recipe::query();

        //Search handling
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $category = $request->category;
            $query->where('category', $category);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cooking_time' => 'required|integer',
            'category' => 'required|string',
            'image_url' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_name' => 'required|string',
            'ingredients.*.quantity' => 'required|string',
            'ingredients.*.unit' => 'required|string',
            'steps' => 'required|array|min:1',
            'steps.*.step_number' => 'required|integer',
            'steps.*.description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create recipe
            $recipe = Recipe::create([
                'title' => $request->title,
                'description' => $request->description,
                'cooking_time' => $request->cooking_time,
                'category' => $request->category,
                'user_id' => $request->user()->id
            ]);

            // Handle image upload
            if ($request->has('image_url')) {
                $image = $request->image_url;

                // Check if image is base64
                if (strpos($image, 'data:image') === 0) {
                    // Handle base64 image
                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace('data:image/jpeg;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);

                    $imageName = 'recipe_' . Str::uuid() . '.png';
                    Storage::disk('public')->put('recipe_images/' . $imageName, base64_decode($image));

                    $recipe->image_url = 'recipe_images/' . $imageName;
                } else {

                    $imageName = 'recipe_' . time() . '.' . $request->image_url->extension();
                    $request->image_url->storeAs('public/recipe_images', $imageName);

                    $recipe->image_url = 'recipe_images/' . $imageName;
                }

                $recipe->save();
            }

            // Create ingredients
            foreach ($request->ingredients as $ingredient) {
                $recipe->ingredients()->create([
                    'name' => $ingredient['ingredient_name'],
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit']
                ]);
            }

            // Create steps
            foreach ($request->steps as $step) {
                $recipe->steps()->create([
                    'step_number' => $step['step_number'],
                    'instruction' => $step['description']
                ]);
            }

            DB::commit();

            // Load the relationships
            $recipe->load(['ingredients', 'steps']);

            return response()->json([
                'status' => 'success',
                'message' => 'Recipe created successfully',
                'data' => $recipe
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded image if exists
            if (isset($imageName)) {
                Storage::disk('public')->delete('recipe_images/' . $imageName);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }





    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        $recipe = Recipe::with('ingredients', 'steps')->findOrFail($recipe->id);

        return response()->json([
            'status' => 'success',
            'data' => $recipe
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        if ($recipe->user_id !== $request->user()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, you can only update your own recipes'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|string',
            'cooking_time' => 'sometimes|integer',
            'category' => 'sometimes|string',
            'image_url' => 'nullable|image|max:2048',
            'ingredients' => 'sometimes|array|min:1',
            'ingredients.*.ingredient_name' => 'string',
            'ingredients.*.quantity' => 'string',
            'ingredients.*.unit' => 'string',
            'steps' => 'sometimes|array|min:1',
            'steps.*.step_number' => 'integer',
            'steps.*.description' => 'string',
        ]);






        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle image upload
            if ($request->hasFile('image_url')) {
                // Delete old image
                if ($recipe->image_url) {
                    Storage::disk('public')->delete('recipe_images/' . $recipe->image_url);
                }
                $imageUrl = $request->file('image_url')->store('recipes', 'public');
                $recipe->image_url = $imageUrl;
            }

            // Update recipe
            $recipe->update($request->only([
                'title',
                'description',
                'cooking_time',
                'category'
            ]));

            // Update ingredients if provided
            if ($request->has('ingredients')) {
                $recipe->ingredients()->delete();
                foreach ($request->ingredients as $ingredient) {
                    $recipe->ingredients()->create([
                        'name' => $ingredient['ingredient_name'],
                        'quantity' => $ingredient['quantity'],
                        'unit' => $ingredient['unit']
                    ]);
                }
            }

            // Update steps if provided
            if ($request->has('steps')) {
                $recipe->steps()->delete();
                foreach ($request->steps as $step) {
                    $recipe->steps()->create([
                        'step_number' => $step['step_number'],
                        'description' => $step['description']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Recipe updated successfully',
                'data' => $recipe->load(['ingredients', 'steps'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);
        if ($recipe->user_id !== $request->user()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, you can only delete your own recipes'
            ], 403);
        }
        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($recipe->image_url) {
                Storage::disk('public')->delete('recipe_images/' . $recipe->image_url);
            }

            // Delete related records
            $recipe->ingredients()->delete();
            $recipe->steps()->delete();

            // Delete recipe
            $recipe->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Recipe deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all recipes with their ingredients and steps
     */
    public function getAllRecipe()
    {
        try {
            $recipes = Recipe::with(['ingredients', 'steps', 'user:id,first_name'])
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $recipes,

                'total' => $recipes->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch recipes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getDetailReceipt($recipeId)
    {
        try {
            $recipe = Recipe::with([
                'ingredients',
                'steps',
                'user:id,first_name,last_name,profile_image',
                'comments' => function ($query) {
                    $query->with('user:id,first_name,last_name,profile_image')
                        ->latest();
                }
            ])->findOrFail($recipeId);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'recipe' => $recipe,
                    'comments_count' => $recipe->comments->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch recipe details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
