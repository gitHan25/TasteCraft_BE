<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Bookmark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $recipe;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Create test recipe
        $this->recipe = Recipe::factory()->create();

        // Login and get token
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->token = $response->json('X-API-TOKEN');
    }

    /** @test */
    public function user_can_get_all_bookmarks()
    {
        // Create some bookmarks
        Bookmark::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'recipe_id' => $this->recipe->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/bookmarks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'recipe_id',
                        'created_at',
                        'updated_at',
                        'recipe' => [
                            'id',
                            'title',
                            'description',
                            'cooking_time',
                            'category',
                            'image_url'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_bookmark_recipe()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/bookmarks', [
            'recipe_id' => $this->recipe->id
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'recipe_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'Recipe bookmarked successfully'
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->user->id,
            'recipe_id' => $this->recipe->id
        ]);
    }

    /** @test */
    public function user_cannot_bookmark_nonexistent_recipe()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/bookmarks', [
            'recipe_id' => '00000000-0000-0000-0000-000000000000'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',

            ]);
    }

    /** @test */
    public function user_cannot_bookmark_without_recipe_id()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/bookmarks', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',

            ]);
    }

    /** @test */
    public function user_can_remove_bookmark()
    {
        // Create a bookmark first
        $bookmark = Bookmark::factory()->create([
            'user_id' => $this->user->id,
            'recipe_id' => $this->recipe->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/bookmarks/' . $bookmark->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Bookmark removed successfully'
            ]);

        $this->assertDatabaseMissing('bookmarks', [
            'id' => $bookmark->id
        ]);
    }

    /** @test */
    public function user_cannot_remove_nonexistent_bookmark()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/bookmarks/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Bookmark not found'
            ]);
    }

    /** @test */
    public function user_cannot_access_bookmarks_without_authentication()
    {
        $response = $this->getJson('/api/bookmarks');

        $response->assertStatus(401)
            ->assertJson([

                'message' => 'Unauthorized'
            ]);
    }

    /** @test */
    public function user_cannot_bookmark_recipe_without_authentication()
    {
        $response = $this->postJson('/api/bookmarks', [
            'recipe_id' => $this->recipe->id
        ]);

        $response->assertStatus(401)
            ->assertJson([

                'message' => 'Unauthorized'
            ]);
    }

    /** @test */
    public function user_cannot_remove_bookmark_without_authentication()
    {
        $bookmark = Bookmark::factory()->create([
            'user_id' => $this->user->id,
            'recipe_id' => $this->recipe->id
        ]);

        $response = $this->deleteJson('/api/bookmarks/' . $bookmark->id);

        $response->assertStatus(401)
            ->assertJson([

                'message' => 'Unauthorized'
            ]);
    }
}
