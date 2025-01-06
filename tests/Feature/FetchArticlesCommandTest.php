<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FetchArticlesCommandTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function testFetchArticlesCommand()
    {
        $this->artisan('articles:fetch')
            ->expectsOutput('Fetching articles from NewsAPI...')
            ->expectsOutput('Fetching articles from The Guardian...')
            ->expectsOutput('Articles fetching completed.')
            ->assertExitCode(0);
    }

}
