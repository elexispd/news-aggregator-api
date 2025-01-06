<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;




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
    $preferences = auth()->user()->preferences;

    if (!$preferences) {
        return response()->json(['message' => 'No preferences set.'], 200);
    }

    $query = Article::query();

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

    $articles = $query->paginate(10);

    return response()->json($articles, 200);
}





}
