<?php

namespace App\Console\Commands;

use App\Services\ArticleFetchService;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update articles from news APIs';

    protected $articleFetchService;

    public function __construct(ArticleFetchService $articleFetchService)
    {
        parent::__construct();
        $this->articleFetchService = $articleFetchService;
    }

    /**
     * Execute the console command.
     */


    public function handle()
    {
        $this->info('Fetching articles from NewsAPI...');
        $newsApiArticles = $this->articleFetchService->fetchFromNewsAPI();
        $this->articleFetchService->saveArticlesToDatabase($newsApiArticles, 'Newsapi');

        $this->info('Fetching articles from The Guardian...');
        $guardianArticles = $this->articleFetchService->fetchFromGuardian();
        $this->articleFetchService->saveArticlesToDatabase($guardianArticles, 'The Guardian');

        // $this->info('Fetching articles from OpenNews...');
        // $openNewsArticles = $this->articleFetchService->fetchFromOpenNews();
        // $this->articleFetchService->saveArticlesToDatabase($openNewsArticles, 'OpenNews');
        $this->info('Articles fetching completed.');
    }






}
