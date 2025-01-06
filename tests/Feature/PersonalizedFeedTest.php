<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Models\Source;
use App\Models\Categories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PersonalizedFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_personalized_feed_returns_articles_based_on_preferences()
    {
        // Mock the HTTP responses for the external APIs
        Http::fake([
            'https://newsapi.org/*' => Http::response(['articles' => []], 200),
            'https://content.guardianapis.com/*' => Http::response(['response' => ['results' => []]], 200),
            'https://api.opennews.com/*' => Http::response(['articles' => []], 200),
        ]);

        // Create a user
        $user = User::factory()->create();

        // Create a category, source, and articles
        $category = Categories::factory()->create();
        $source = Source::factory()->create();

        $articles = Article::factory()->count(5)->create([
            'category_id' => $category->id,
            'source_id' => $source->id,
        ]);

        // Assign preferences to the user
        $user->preferences()->create([
            'categories' => [$category->id],
            'sources' => [$source->id],
            'authors' => ['John Doe'],
        ]);

        // Act as the user and test the personalized feed
        $response = $this->actingAs($user)->getJson('/api/personalized-feed');

        // Assert that the response is successful and contains the expected articles
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'title',
                    'description',
                    'content',
                    'author',
                    'published_at',
                    'source',
                    'category_id',
                ],
            ],
        ]);
        $response->assertJsonCount(10, 'data'); // Check pagination (10 items per page)
    }

    public function test_personalized_feed_uses_cache()
    {
        // Create a user and assign preferences
        $user = User::factory()->create();
        $category = Categories::factory()->create();
        $source = Source::factory()->create();

        $user->preferences()->create([
            'categories' => [$category->id],
            'sources' => [$source->id],
            'authors' => ['John Doe'],
        ]);

        // First request to generate cache
        $response1 = $this->actingAs($user)->getJson('/api/personalized-feed');

        // Mock the cache to ensure it's used
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($response1->json());

        // Second request should use the cache
        $response2 = $this->actingAs($user)->getJson('/api/personalized-feed');

        // Assert that the response is the same (cached)
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_personalized_feed_returns_no_articles_if_no_preferences()
    {
        // Create a user with no preferences set
        $user = User::factory()->create();

        // Act as the user and test the personalized feed
        $response = $this->actingAs($user)->getJson('/api/personalized-feed');

        // Assert that the response contains the correct message
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'No preferences set.',
        ]);
    }
}
