<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;
use App\Models\Source;
use App\Models\Categories;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\ArticleRequest;
use Illuminate\Support\Facades\Http;
use Exception;


class ArticleController extends Controller
{

    public function index(ArticleRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $articles = Article::paginate($perPage, ['*'], 'page', $page);
        return response()->json($articles);
    }

    public function show(Article $article)
    {
        return response()->json($article);
    }



    public function search(ArticleRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = Article::query();

        // Apply filters
        $this->applyFilters($query, $request);

        // Paginate the result
        $articles = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse([
            'articles' => $articles,
            'total_pages' => $articles->lastPage(),
            'total_articles' => $articles->total(),
        ], 'Articles retrieved successfully');
    }

    protected function applyFilters($query, $request)
    {
        // Filter by keyword
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->input('keyword') . '%')
                ->orWhere('content', 'like', '%' . $request->input('keyword') . '%');
            });
        }

        // Filter by date range
        if ($request->filled(['date_from', 'date_to'])) {
            $query->whereBetween('created_at', [
                $request->input('date_from'),
                $request->input('date_to'),
            ]);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by source
        if ($request->filled('source_id')) {
            $query->where('source_id', $request->input('source_id'));
        }
    }







}
