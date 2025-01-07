<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Traits\HttpResponses;




class UserPreferenceController extends Controller
{
    use HttpResponses;

    /**
     * @OA\Get(
     *     path="/api/preferences",
     *     tags={"User Preferences"},
     *     summary="Get user preferences",
     *     description="Retrieve the preferences of the authenticated user",
     *     operationId="Preference-index",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of user preferences",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array", items=@OA\Items(type="string")),
     *             @OA\Property(property="sources", type="array", items=@OA\Items(type="string")),
     *             @OA\Property(property="authors", type="array", items=@OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $preferences = auth()->user()->preferences;
        return $this->successResponse($preferences, 'Articles Retrived');
    }


    /**
     * @OA\Post(
     *     path="/api/preferences",
     *     tags={"User Preferences"},
     *     summary="Store or update user preferences",
     *     description="Store or update the preferences for the authenticated user",
     *     operationId="preferencestore",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array", items=@OA\Items(type="string")),
     *             @OA\Property(property="sources", type="array", items=@OA\Items(type="string")),
     *             @OA\Property(property="authors", type="array", items=@OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated preferences",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array", items=@OA\Items(type="string")),
     *             @OA\Property(property="sources", type="array", items=@OA\Items(type="string")),
     *             @OA\Property(property="authors", type="array", items=@OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'array',
            'sources' => 'array',
            'authors' => 'array',
        ]);

        $preferences = auth()->user()->preferences()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'categories' => $validated['categories'],
                'sources' => $validated['sources'],
                'authors' => $validated['authors'],
            ]
        );

        return $this->successResponse($preferences, 'preferences Output');
    }


    /**
     * @OA\Get(
     *     path="/api/personalized-feed",
     *     tags={"User Preferences"},
     *     summary="Get personalized feed based on user preferences",
     *     description="Retrieve a personalized feed of articles based on the user's preferences",
     *     operationId="personalizedFeed",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of personalized feed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="data", type="array", items=@OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences set"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function personalizedFeed()
    {
        // Get the user's preferences
        $preferences = auth()->user()->preferences;

        if (!$preferences) {
            return response()->json(['message' => 'No preferences set.'], 200);
        }

        // Build a unique cache key based on user ID and preferences
        $cacheKey = 'personalized_feed_' . auth()->id() . '_' . implode('_', $preferences->categories) . '_' . implode('_', $preferences->sources) . '_' . implode('_', $preferences->authors);
        $cacheTime = 60;

        // Check if the personalized feed is cached
        $articles = Cache::remember($cacheKey, $cacheTime, function () use ($preferences) {
            $query = Article::query();

            // Apply filters based on user preferences
            $categories = is_array($preferences->categories) ? $preferences->categories : [];
            $sources = is_array($preferences->sources) ? $preferences->sources : [];
            $authors = is_array($preferences->authors) ? $preferences->authors : [];

            if (!empty($categories)) {
                $query->whereIn('category_id', $categories);
            }

            if (!empty($sources)) {
                $query->whereIn('source_id', $sources);
            }

            if (!empty($authors)) {
                $query->whereIn('author', $authors);
            }

            return $query->paginate(10);
        });

        return response()->json($articles, 200);
    }





}
