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


    public function fetchArticlesFromAPI()
    {
        $createdArticlesCount = 0;

        // Fetch from NewsAPI
        // $newsApiArticles = $this->fetchArticlesFromNewsAPI();
        // $createdArticlesCount += $this->saveArticlesToDatabase($newsApiArticles, 'Newsapi');

         // Fetch from New Your Times
        $newYorkArticles = $this->fetchArticlesFromnewYorkArticles();
        $createdArticlesCount += $this->saveArticlesToDatabase($newYorkArticles, 'NY Times');

        // // Fetch from The Guardian
        // $theGuardianArticles = $this->fetchArticlesFromGuardian();
        // $createdArticlesCount += $this->saveArticlesToDatabase($theGuardianArticles, 'The Guardian');

        return response()->json([
            'message' => 'Articles fetched and saved successfully.',
            'articles_created' =>$createdArticlesCount,
        ], 200);
    }

    // Fetch articles from NewsAPI
    // private function fetchArticlesFromNewsAPI()
    // {
    //     $response = Http::get('https://newsapi.org/v2/top-headlines', [
    //         'apiKey' => env('NEWS_API_KEY'),
    //         'country' => 'us',
    //     ]);

    //     return $response->successful() ? $response->json()['articles'] : [];
    // }

    // // Fetch articles from OpenNews
    // private function fetchArticlesFromOpenNews()
    // {
    //     $response = Http::get('https://api.opennews.com/articles', [
    //         'apiKey' => env('OPENNEWS_API_KEY'),
    //         'country' => 'us',
    //     ]);

    //     return $response->successful() ? $response->json()['articles'] : [];
    // }

    // Fetch articles from The Guardian
    // private function fetchArticlesFromnewYorkArticles()
    // {
    //     $response = Http::get('https://api.nytimes.com/svc/mostpopular/v2/shared/1/facebook.json', [
    //     'api-key' => env('NY_API_KEY'),
    // ]);
    //     return $response->successful() ? $response->json()['results'] : [];
    // }


    // // Fetch articles from The Guardian
    // private function fetchArticlesFromGuardian()
    // {
    //     $response = Http::get('https://content.guardianapis.com/search', [
    //         'api-key' => env('THE_GUARDIAN_API_KEY'),
    //         'order-by' => 'newest',
    //         'page-size' => 3,
    //     ]);

    //     return $response->successful() ? $response->json()['response']['results'] : [];
    // }


    // private function saveArticlesToDatabase($articles, $sourceName)
    // {
    //     $articles = array_slice($articles, 0, 3); // Limit to the first 3 articles
    //     $createdArticlesCount = 0;

    //     // Find the source by its name
    //     $source = Source::where('name', $sourceName)->first();

    //     if (!$source) {
    //         throw new Exception("Source '{$sourceName}' not found.");
    //     }

    //     foreach ($articles as $article) {
    //         $category = Categories::firstOrCreate(['name' => 'General']); // Ensure a default category

    //         // Extract article data depending on the source
    //         $articleData = $this->getArticleDataBySource($article, $sourceName);

    //         // Add the source_id to the article data
    //         $articleData['source_id'] = $source->id;

    //         // Create article record
    //         Article::create($articleData);

    //         $createdArticlesCount++;
    //     }

    //     return $createdArticlesCount;
    // }



    // private function getArticleDataBySource($article, $source)
    // {
    //     $articles = array_slice($article, 0, 3);
    //     $category = Categories::inRandomOrder()->first();
    //     if($category) {
    //         $category = $category->id;
    //     } else {
    //         $category = Categories::firstOrCreate(['name' => 'General'])->id;
    //     }
    //     switch ($source) {
    //         case 'Newsapi':
    //             return [
    //                 'title' => $article['title'],
    //                 'description' => $article['description'] ?? 'No description available',
    //                 'content' => $article['content'] ?? 'No content available',
    //                 'author' => $article['author'] ?? 'Unknown',
    //                 'published_at' => $article['publishedAt'] ?? now(),
    //                 'source' => $article['source']['name'] ?? 'Unknown',
    //                 'category_id' => $category,
    //             ];

    //         case 'The Guardian':
    //             return [
    //                 'title' => $article['webTitle'],
    //                 'description' => $article['fields']['bodyText'] ?? 'No description available',
    //                 'content' => $article['fields']['bodyText'] ?? 'No content available', // Add content (bodyText)
    //                 'author' => $article['byline'] ?? 'Unknown',
    //                 'published_at' => $article['webPublicationDate'] ?? now(),
    //                 'source' => $article['tags'][0]['webTitle'] ?? 'Unknown',
    //                 'category_id' => $category,
    //             ];

    //         case 'NY Times':
    //             return [
    //                 'title' => $article['title'],
    //                 'description' => $article['abstract'] ?? 'No description available',
    //                 'content' => $article['adx_keywords'] ?? 'No content available',
    //                 'author' => $article['byline'] ?? 'Unknown',
    //                 'published_at' => $article['published_date'] ?? now(),
    //                 'source' => $article['source'] ?? 'Unknown',
    //                 'category_id' => $category,
    //             ];

    //         default:
    //             return [];
    //     }
    // }






}
