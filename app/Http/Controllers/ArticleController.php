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
use Illuminate\Database\Eloquent\ModelNotFoundException;



 class ArticleController extends Controller
 {

     /**
      * @OA\Get(
      *     path="/api/v1/articles",
      *     tags={"Articles"},
      *     summary="Retrieve a list of articles",
      *     security={ {"sanctum": {} }},
      *     description="Fetch articles with pagination",
      *     operationId="index",
      *     @OA\Parameter(
      *         name="per_page",
      *         in="query",
      *         description="Number of articles per page",
      *         required=false,
      *         @OA\Schema(type="integer", default=10)
      *     ),
      *     @OA\Parameter(
      *         name="page",
      *         in="query",
      *         description="Page number",
      *         required=false,
      *         @OA\Schema(type="integer", default=1)
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Successful retrieval of articles",
      *         @OA\JsonContent(
      *             type="object",
      *
      *             @OA\Property(property="current_page", type="integer"),
      *             @OA\Property(property="per_page", type="integer"),
      *             @OA\Property(property="total", type="integer"),
      *             @OA\Property(property="last_page", type="integer")
      *         )
      *     ),
      *     @OA\Response(
      *         response=400,
      *         description="Bad request"
      *     )
      * )
      */
     public function index(ArticleRequest $request)
     {
         $perPage = $request->input('per_page', 10);
         $page = $request->input('page', 1);
         $articles = Article::paginate($perPage, ['*'], 'page', $page);
         return response()->json($articles);
     }





    /**
     * @OA\Get(
     *     path="/api/v1/articles/{article}",
     *     tags={"Articles"},
     *     security={ {"sanctum": {} }},
     *     summary="Retrieve a single article",
     *     description="Fetch a specific article by its ID",
     *     operationId="show",
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="Article ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of article",
     *
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $article = Article::findOrFail($id);  // This will throw ModelNotFoundException if not found
            return response()->json($article);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Article not found'], 404);  // Custom 404 message
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/articles/search",
     *     tags={"Articles"},
     *     security={ {"sanctum": {} }},
     *     summary="Search for articles",
     *     description="Fetch articles based on search criteria with pagination",
     *     operationId="search",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of articles per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Search filters",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "category": "Technology",
     *                 "source": "Tech News"
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful search of articles",
     *         @OA\JsonContent(
     *             type="object",
     *
     *
     *             ),
     *             @OA\Property(property="total_pages", type="integer"),
     *             @OA\Property(property="total_articles", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
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
