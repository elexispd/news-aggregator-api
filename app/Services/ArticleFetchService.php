<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Article;
use App\Models\User;
use App\Models\Source;
use App\Models\Categories;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\ArticleRequest;
use Exception;

class ArticleFetchService
{
    public function fetchFromNewsAPI()
    {
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => env('NEWS_API_KEY'),
            'country' => 'us',
        ]);

        return $response->successful() ? $response->json()['articles'] : [];
    }

    public function fetchFromGuardian()
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => env('THE_GUARDIAN_API_KEY'),
            'order-by' => 'newest',
            'page-size' => 3,
        ]);

        return $response->successful() ? $response->json()['response']['results'] : [];
    }

    public function fetchFromOpenNews()
    {
        $response = Http::get('https://api.opennews.com/articles', [
            'apiKey' => env('OPENNEWS_API_KEY'),
            'country' => 'us',
        ]);

        return $response->successful() ? $response->json()['articles'] : [];
    }

    public function fetchArticlesFromnewYorkArticles()
    {
        $response = Http::get('https://api.nytimes.com/svc/mostpopular/v2/shared/1/facebook.json', [
        'api-key' => env('NY_API_KEY'),
    ]);
        return $response->successful() ? $response->json()['results'] : [];
    }

    private function getArticleDataBySource($article, $source)
    {
        $articles = array_slice($article, 0, 3);
        $category = Categories::inRandomOrder()->first();
        if($category) {
            $category = $category->id;
        } else {
            $category = Categories::firstOrCreate(['name' => 'General'])->id;
        }
        switch ($source) {
            case 'Newsapi':
                return [
                    'title' => $article['title'],
                    'description' => $article['description'] ?? 'No description available',
                    'content' => $article['content'] ?? 'No content available',
                    'author' => $article['author'] ?? 'Unknown',
                    'published_at' => $article['publishedAt'] ?? now(),
                    'source' => $article['source']['name'] ?? 'Unknown',
                    'category_id' => $category,
                ];

            case 'The Guardian':
                return [
                    'title' => $article['webTitle'],
                    'description' => $article['fields']['bodyText'] ?? 'No description available',
                    'content' => $article['fields']['bodyText'] ?? 'No content available', // Add content (bodyText)
                    'author' => $article['byline'] ?? 'Unknown',
                    'published_at' => $article['webPublicationDate'] ?? now(),
                    'source' => $article['tags'][0]['webTitle'] ?? 'Unknown',
                    'category_id' => $category,
                ];

            case 'NY Times':
                    return [
                        'title' => $article['title'],
                        'description' => $article['abstract'] ?? 'No description available',
                        'content' => $article['adx_keywords'] ?? 'No content available',
                        'author' => $article['byline'] ?? 'Unknown',
                        'published_at' => $article['published_date'] ?? now(),
                        'source' => $article['source'] ?? 'Unknown',
                        'category_id' => $category,
                    ];

            default:
                return [];
        }
    }

    public function saveArticlesToDatabase($articles, $sourceName)
    {
        $articles = array_slice($articles, 0, 3); // Limit to the first 3 articles
        $createdArticlesCount = 0;

        // Find the source by its name
        $source = Source::where('name', $sourceName)->first();

        if (!$source) {
            throw new Exception("Source '{$sourceName}' not found.");
        }

        foreach ($articles as $article) {
            $category = Categories::firstOrCreate(['name' => 'General']); // Ensure a default category

            // Extract article data depending on the source
            $articleData = $this->getArticleDataBySource($article, $sourceName);

            // Add the source_id to the article data
            $articleData['source_id'] = $source->id;

            // Create article record
            Article::create($articleData);

            $createdArticlesCount++;
        }

        return $createdArticlesCount;
    }


}
