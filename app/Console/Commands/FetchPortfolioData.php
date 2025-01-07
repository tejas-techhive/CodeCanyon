<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\PortfolioItem;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class FetchPortfolioData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolio:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch portfolio data for authors and store in the database';

    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $authors = Author::select('id', 'name')
            ->where('is_complete', 0)
            ->take(6)
            ->get();

        if ($authors->isEmpty()) {
            Log::info('No authors found.');
            $this->info('No authors found.');
            return;
        }

        $requests = function ($authors) {
            foreach ($authors as $author) {
                $url = "https://codecanyon.net/user/{$author->name}/portfolio?order_by=sales";
                yield new Request('GET', $url);
            }
        };

        $results = [];

        $pool = new Pool($this->client, $requests($authors), [
            'concurrency' => 2,
            'fulfilled' => function ($response, $index) use ($authors, &$results) {
                $author = $authors[$index];
                $html = $response->getBody()->getContents();
                $crawler = new Crawler($html);

                try {
                    $userStats = $crawler->filter('.user-info-header__user-stats')->each(function (Crawler $node) {
                        $rating = 0;
                        $totalRatings = 0;

                        if ($node->filter('.star-rating .is-visually-hidden')->count() > 0) {
                            $ratingText = $node->filter('.star-rating .is-visually-hidden')->text();
                            preg_match('/(\d+\.\d+)/', $ratingText, $ratingMatch);
                            $rating = $ratingMatch[1] ?? 0; 
                        }

                        if ($node->filter('.t-body.-size-s')->count() > 0) {
                            $ratingsText = $node->filter('.t-body.-size-s')->text();
                            preg_match('/(\d+)/', $ratingsText, $ratingsMatch);
                            $totalRatings = $ratingsMatch[1] ?? 0; 
                        }

                        $totalSales = trim($node->filter('.user-info-header__stats-content strong')->text());

                        return [
                            'rating' => $rating,
                            'total_ratings' => $totalRatings,
                            'total_sales' => $totalSales,
                        ];
                    });

                    $items = $crawler->filter('ul.product-list li')->each(function (Crawler $node) use ($author, $userStats) {
                        try {
                            $image = $node->filter('.item-thumbnail__image img')->attr('src') ?? null;
                            $category = $node->filter('.product-list__column-category p')->text() ?? null;
                            $name = trim($node->filter('.product-list__heading a')->text()) ?? null;
                            $price = preg_replace('/[^0-9]/', '', $node->filter('.product-list__price-desktop')->text());
                            $ratingsText = $node->filter('.product-list__info-desktop')->text();
                            preg_match('/(\d+)\s+ratings/', $ratingsText, $ratingsMatch);
                            $ratings = $ratingsMatch[1] ?? 0;

                            $salesText = $node->filter('.product-list__sales-desktop')->text();
                            preg_match('/(\d+)\s+Sales/', $salesText, $salesMatch);
                            $sales = $salesMatch[1] ?? 0;

                            return [
                                'author_name' => $author->name,
                                'image' => $image,
                                'name' => $name,
                                'category' => $category,
                                'price' => $price,
                                'ratings' => $ratings,
                                'sales' => $sales,
                                'rating' => $userStats[0]['rating'] ?? 0,
                                'total_ratings' => $userStats[0]['total_ratings'] ?? 0,
                                'total_sales' => $userStats[0]['total_sales'] ?? 0,
                            ];
                        } catch (\Exception $e) {
                            return null;
                        }
                    });

                    $results = array_merge($results, $items);

                    $author->update(['is_complete' => 1]); // Mark as complete
                } catch (\Exception $e) {
                    Log::error("Error processing author: {$author->name}. Error: " . $e->getMessage());
                }
            },
            'rejected' => function ($reason, $index) use ($authors) {
                $author = $authors[$index];
                Log::error("Failed to fetch page for author: {$author->name}. Reason: " . $reason);
            },
        ]);

        $pool->promise()->wait();

        $results = array_filter($results);

        foreach (array_chunk($results, 100) as $batch) {
            $batch = array_map(function ($item) {
                return array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, $batch);
            PortfolioItem::insert($batch);
        }

        $this->info('Portfolio data successfully fetched and stored.');
    }
}
