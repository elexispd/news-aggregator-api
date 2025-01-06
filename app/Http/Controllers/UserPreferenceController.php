<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Cache;




class UserPreferenceController extends Controller
{
    public function index()
    {
        $preferences = auth()->user()->preferences;

        return response()->json($preferences, 200);
    }

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

    return response()->json($preferences, 200);
    }





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
