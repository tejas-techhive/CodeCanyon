<?php

namespace App\Http\Controllers;

use App\Jobs\FetchPortfolioJob;
use App\Jobs\TestQueueJob;
use App\Models\Author;
use App\Models\Category;
use App\Models\FailedCategory;
use App\Models\PopularItem;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use App\Models\PortfolioItem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CodecanyonController extends Controller
{
    // protected $scrapeService;

    // public function __construct(CodecanyonScrapeService $scrapeService)
    // {
    //     $this->scrapeService = $scrapeService;
    // }
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
    }

    public function getCategoriesAndPopularItems($id)
    {
        $items = [];

        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        if ($category->is_complete == 1) {
            return response()->json(['success' => false, 'message' => 'Category data has already been processed.'], 400);
        }

        $category_name = $category->slug;
        $category_id = $category->id;

        try {
            // Start the transaction
            DB::beginTransaction();

            // Fetch category page
            $response = $this->client->request('GET', "https://codecanyon.net/popular_item/by_category?category=$category_name");
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch the page content.');
            }

            $html = $response->getBody()->getContents();
            if (empty($html)) {
                throw new \Exception('The page content is empty.');
            }

            $crawler = new Crawler($html);

            // Extract categories
            $categories = $crawler->filter('.popular_items-subcategory_nav_component__list li')->each(function (Crawler $node) {
                try {
                    $link = $node->filter('a.popular_items-subcategory_nav_component__link')->attr('href');
                    $name = trim($node->filter('a.popular_items-subcategory_nav_component__link')->text());

                    $queryString = parse_url($link, PHP_URL_QUERY);
                    parse_str($queryString, $queryParams);
                    $subcategory = $queryParams['category'] ?? null;

                    return [
                        'name' => $name ?? 'N/A',
                        'slug' => $subcategory ?? 'N/A',
                    ];
                } catch (\Exception $e) {
                    Log::error('Error parsing category node: ' . $e->getMessage());
                    return null;
                }
            });

            $categories = array_filter($categories);

            foreach ($categories as $category) {
                try {
                    Category::updateOrCreate(
                        ['name' => $category['name'], 'parent_id' => $category_id],
                        ['slug' => $category['slug']]
                    );
                } catch (\Exception $e) {
                    Log::error('Error inserting category into database: ' . $e->getMessage());
                    DB::rollBack();
                    throw $e;
                }
            }

            // Extract popular items
            $items = $crawler->filter('.shared-item_cards-card_component__root')->each(function (Crawler $node) use ($category_id) {
                try {
                    $itemId = $node->filter('.shared-item_cards-grid-image_card_component__root')->attr('data-item-id');
                    $name = $node->filter('.shared-item_cards-item_name_component__itemNameLink')->text('');
                    $href = $node->filter('.shared-item_cards-item_name_component__itemNameLink')->attr('href');
                    $imageElement = $node->filter('.shared-item_cards-preview_image_component__image');
                    $image = $imageElement->count() ? $imageElement->attr('src') : '';
                    $byInfo = $node->filter('.shared-item_cards-author_category_component__root')->text('');
                    $author_name = $node->filter('.shared-item_cards-author_category_component__root a')->eq(0)->text();
                    $author_link = $node->filter('.shared-item_cards-author_category_component__root a')->eq(0)->attr('href');

                    $language_name = $node->filter('.shared-item_cards-author_category_component__root a')->eq(1)->text();
                    $language_link = $node->filter('.shared-item_cards-author_category_component__root a')->eq(1)->attr('href');


                    $originalPriceElement = $node->filter('.shared-item_cards-price_component__originalPrice');
                    $promoPriceElement = $node->filter('.shared-item_cards-price_component__promoPrice');
                    if ($originalPriceElement->count() && $promoPriceElement->count()) {
                        $price = str_replace('$', '', $originalPriceElement->text(''));
                        $offer = str_replace('$', '', $promoPriceElement->text(''));
                    } else {
                        // Fallback to the main price in case the offer does not exist
                        $price = str_replace('$', '', $node->filter('.shared-item_cards-price_component__root')->text(''));
                        $offer = null; // No promotional offer
                    }

                    $starsRating = $node->filter('.shared-stars_rating_component__starRating')->attr('aria-label', '');
                    preg_match('/Rated ([\d.]+) out of 5, (\d+) reviews/', $starsRating, $matches);

                    $sales = $node->filter('.shared-item_cards-sales_component__root')->text('');
                    $trending = $node->filter('.shared-item_cards-sash_component__sash_trending')->count() ? 'Yes' : 'No';

                    return [
                        'item_id' => $itemId,
                        'category_id' => $category_id,
                        'name' => html_entity_decode($name),
                        'single_url' => $href,
                        'image' => $image,
                        'by' => trim(preg_replace('/\s+/', ' ', $byInfo)),
                        'author_link' => $author_link,
                        'author_name' => $author_name,
                        'language_name' => $language_name,
                        'language_link' => $language_link,
                        'price' => $price,
                        'offer' => $offer,
                        'stars' => $matches[1] ?? null,
                        'reviews' => $matches[2] ?? null,
                        'sales' => str_replace(' Sales', '', $sales),
                        'trending' => $trending,
                    ];
                } catch (\Exception $e) {
                    Log::error('Error parsing item node: ' . $e->getMessage());
                    return null;
                }
            });

            $items = array_filter($items);

            $promises = [];
            foreach ($items as $key => $item) {
                $promises[$key] = $this->client->getAsync($item['single_url']);
            }

            $results = Utils::settle($promises)->wait();

            foreach ($results as $key => $result) {
                if ($result['state'] === 'fulfilled') {
                    $productHtml = $result['value']->getBody()->getContents();
                    $singleProduct = new Crawler($productHtml);

                    $items[$key]['total_sales'] = $singleProduct->filter('.item-header__sales-count strong')->count() ?
                        $singleProduct->filter('.item-header__sales-count strong')->text('') : 0;

                    $items[$key]['last_update'] = $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--last_update time')->count() ?
                        $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--last_update time')->attr('datetime') : now();

                    $items[$key]['published'] = $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--created-at span')->count() ?
                        $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--created-at span')->text() : now();
                }
            }

            foreach ($items as $item) {
                PopularItem::create($item);
            }

            Category::where('id', $category_id)->update([
                'is_complete' => 1
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching items.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item Inserted.',
            'data' => $items
        ]);
    }

    public function getPopularItems()
    {
        // Pending - 1,7,25,54,64,81,95
        // Done    - 
        // $category_id = 1;
        // $categories = Category::where('parent_id', $category_id)->select('id', 'name', 'slug')->get();
        $categories = Category::where('is_complete', 0)->take(5)->select('id', 'name', 'slug')->get();
        $allItems = [];

        if ($categories->count() > 0) {
            foreach ($categories as $category) {
                try {
                    DB::beginTransaction();

                    // Step 1: Fetch popular items page
                    $response = $this->client->request('GET', "https://codecanyon.net/popular_item/by_category?category=$category->slug");
                    sleep(rand(15, 30)); 
                    if ($response->getStatusCode() !== 200) {
                        throw new \Exception('Failed to fetch the page content for category: ' . $category->slug);
                    }

                    $html = $response->getBody()->getContents();

                    if (empty($html)) {
                        throw new \Exception('The page content is empty for category: ' . $category->slug);
                    }

                    $crawler = new Crawler($html);

                    // Step 2: Extract popular items from the page
                    $items = $crawler->filter('.shared-item_cards-card_component__root')->each(function (Crawler $node) {
                        try {
                            $itemId = $node->filter('.shared-item_cards-grid-image_card_component__root')->attr('data-item-id');
                            $name = $node->filter('.shared-item_cards-item_name_component__itemNameLink')->text('');
                            $href = $node->filter('.shared-item_cards-item_name_component__itemNameLink')->attr('href');
                            $image = $node->filter('.shared-item_cards-preview_image_component__image')->count()
                                ? $node->filter('.shared-item_cards-preview_image_component__image')->attr('src')
                                : '';

                            $byInfo = $node->filter('.shared-item_cards-author_category_component__root')->text('');
                            $author_name = $node->filter('.shared-item_cards-author_category_component__root a')->eq(0)->text();
                            $author_link = $node->filter('.shared-item_cards-author_category_component__root a')->eq(0)->attr('href');

                            $language_name = $node->filter('.shared-item_cards-author_category_component__root a')->eq(1)->text();
                            $language_link = $node->filter('.shared-item_cards-author_category_component__root a')->eq(1)->attr('href');

                            $originalPrice = $node->filter('.shared-item_cards-price_component__originalPrice')->text('');
                            $promoPrice = $node->filter('.shared-item_cards-price_component__promoPrice')->text('');
                            $price = $originalPrice ? str_replace('$', '', $originalPrice) : str_replace('$', '', $node->filter('.shared-item_cards-price_component__root')->text(''));
                            $offer = $promoPrice ? str_replace('$', '', $promoPrice) : null;


                            $starsRating = $node->filter('.shared-stars_rating_component__starRating')->attr('aria-label', '');
                            preg_match('/Rated ([\d.]+) out of 5, (\d+) reviews/', $starsRating, $matches);

                            $sales = $node->filter('.shared-item_cards-sales_component__root')->text('');
                            $trending = $node->filter('.shared-item_cards-sash_component__sash_trending')->count() ? 'Yes' : 'No';

                            return [
                                'item_id' => $itemId,
                                'name' => html_entity_decode($name),
                                'single_url' => $href,
                                'image' => $image,
                                'by' => trim(preg_replace('/\s+/', ' ', $byInfo)),
                                'author_link' => $author_link,
                                'author_name' => $author_name,
                                'language_name' => $language_name,
                                'language_link' => $language_link,
                                'price' => $price,
                                'offer' => $offer,
                                'stars' => $matches[1] ?? null,
                                'reviews' => $matches[2] ?? null,
                                'sales' => str_replace(' Sales', '', $sales),
                                'trending' => $trending,
                            ];
                        } catch (\Exception $e) {
                            Log::error('Error parsing item node: ' . $e->getMessage());
                            return null;
                        }
                    });

                    $items = array_filter($items); // Remove null items

                    // Step 3: Fetch additional data for each item asynchronously
                    $promises = [];
                    foreach ($items as $key => $item) {
                        $promises[$key] = $this->client->getAsync($item['single_url']);
                    }

                    $results = Utils::settle($promises)->wait();

                    foreach ($results as $key => $result) {
                        if ($result['state'] === 'fulfilled') {
                            try {
                                $productHtml = $result['value']->getBody()->getContents();
                                $singleProduct = new Crawler($productHtml);

                                $items[$key]['total_sales'] = $singleProduct->filter('.item-header__sales-count strong')->count()
                                    ? $singleProduct->filter('.item-header__sales-count strong')->text()
                                    : 0;

                                $items[$key]['last_update'] = $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--last_update time')->count()
                                    ? $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--last_update time')->attr('datetime')
                                    : now();

                                $items[$key]['published'] = $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--created-at span')->count()
                                    ? $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--created-at span')->text()
                                    : now();
                            } catch (\Exception $e) {
                                Log::error('Error parsing product page for item: ' . $items[$key]['item_id'] . '. ' . $e->getMessage());
                            }
                        }
                    }

                    // Step 4: Insert or update items in the database
                    $preparedItems = array_map(function ($item) use ($category) {
                        return array_merge($item, [
                            'category_id' => $category->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }, $items);

                    PopularItem::insert($preparedItems);

                    // Store items for this category
                    $allItems = array_merge($allItems, $items);
                    Category::where('id', $category->id)->update([
                        'is_complete' => 1
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('An unexpected error occurred for category ' . $category->slug . ': ' . $e->getMessage());
                    DB::table('failed_categories')->updateOrInsert(
                        ['category_id' => $category->id], 
                        [
                            'error_message' => $e->getMessage(),
                            'attempted_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    
                }
            }
        }

        return $allItems;
    }

    public function getFailedPopularItems()
    {

        $failed_categories = FailedCategory::select('category_id')
            ->distinct()
            ->pluck('category_id');

        if ($failed_categories->isEmpty()) {
            return "No Failed Categories List Found";
        }
        $categories = Category::whereIn('id', $failed_categories)->where('is_complete', 0)->take(5)->select('id', 'name', 'slug')->get();
        $allItems = [];

        if ($categories->count() > 0) {
            foreach ($categories as $category) {
                try {
                    DB::beginTransaction();

                    // Step 1: Fetch popular items page
                    $response = $this->client->request('GET', "https://codecanyon.net/popular_item/by_category?category=$category->slug");

                    if ($response->getStatusCode() !== 200) {
                        throw new \Exception('Failed to fetch the page content for category: ' . $category->slug);
                    }

                    $html = $response->getBody()->getContents();

                    if (empty($html)) {
                        throw new \Exception('The page content is empty for category: ' . $category->slug);
                    }

                    $crawler = new Crawler($html);

                    // Step 2: Extract popular items from the page
                    $items = $crawler->filter('.shared-item_cards-card_component__root')->each(function (Crawler $node) {
                        try {
                            $itemId = $node->filter('.shared-item_cards-grid-image_card_component__root')->attr('data-item-id');
                            $name = $node->filter('.shared-item_cards-item_name_component__itemNameLink')->text('');
                            $href = $node->filter('.shared-item_cards-item_name_component__itemNameLink')->attr('href');
                            $image = $node->filter('.shared-item_cards-preview_image_component__image')->count()
                                ? $node->filter('.shared-item_cards-preview_image_component__image')->attr('src')
                                : '';

                            $byInfo = $node->filter('.shared-item_cards-author_category_component__root')->text('');
                            $author_name = $node->filter('.shared-item_cards-author_category_component__root a')->eq(0)->text();
                            $author_link = $node->filter('.shared-item_cards-author_category_component__root a')->eq(0)->attr('href');

                            $language_name = $node->filter('.shared-item_cards-author_category_component__root a')->eq(1)->text();
                            $language_link = $node->filter('.shared-item_cards-author_category_component__root a')->eq(1)->attr('href');

                            $originalPrice = $node->filter('.shared-item_cards-price_component__originalPrice')->text('');
                            $promoPrice = $node->filter('.shared-item_cards-price_component__promoPrice')->text('');
                            $price = $originalPrice ? str_replace('$', '', $originalPrice) : str_replace('$', '', $node->filter('.shared-item_cards-price_component__root')->text(''));
                            $offer = $promoPrice ? str_replace('$', '', $promoPrice) : null;


                            $starsRating = $node->filter('.shared-stars_rating_component__starRating')->attr('aria-label', '');
                            preg_match('/Rated ([\d.]+) out of 5, (\d+) reviews/', $starsRating, $matches);

                            $sales = $node->filter('.shared-item_cards-sales_component__root')->text('');
                            $trending = $node->filter('.shared-item_cards-sash_component__sash_trending')->count() ? 'Yes' : 'No';

                            return [
                                'item_id' => $itemId,
                                'name' => html_entity_decode($name),
                                'single_url' => $href,
                                'image' => $image,
                                'by' => trim(preg_replace('/\s+/', ' ', $byInfo)),
                                'author_link' => $author_link,
                                'author_name' => $author_name,
                                'language_name' => $language_name,
                                'language_link' => $language_link,
                                'price' => $price,
                                'offer' => $offer,
                                'stars' => $matches[1] ?? null,
                                'reviews' => $matches[2] ?? null,
                                'sales' => str_replace(' Sales', '', $sales),
                                'trending' => $trending,
                            ];
                        } catch (\Exception $e) {
                            Log::error('Error parsing item node: ' . $e->getMessage());
                            return null;
                        }
                    });

                    $items = array_filter($items); // Remove null items

                    // Step 3: Fetch additional data for each item asynchronously
                    $promises = [];
                    foreach ($items as $key => $item) {
                        $promises[$key] = $this->client->getAsync($item['single_url']);
                    }

                    $results = Utils::settle($promises)->wait();

                    foreach ($results as $key => $result) {
                        if ($result['state'] === 'fulfilled') {
                            try {
                                $productHtml = $result['value']->getBody()->getContents();
                                $singleProduct = new Crawler($productHtml);

                                $items[$key]['total_sales'] = $singleProduct->filter('.item-header__sales-count strong')->count()
                                    ? $singleProduct->filter('.item-header__sales-count strong')->text()
                                    : 0;

                                $items[$key]['last_update'] = $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--last_update time')->count()
                                    ? $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--last_update time')->attr('datetime')
                                    : now();

                                $items[$key]['published'] = $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--created-at span')->count()
                                    ? $singleProduct->filter('.meta-attributes__table .js-condense-item-page-info-panel--created-at span')->text()
                                    : now();
                            } catch (\Exception $e) {
                                Log::error('Error parsing product page for item: ' . $items[$key]['item_id'] . '. ' . $e->getMessage());
                            }
                        }
                    }

                    // Step 4: Insert or update items in the database
                    $preparedItems = array_map(function ($item) use ($category) {
                        return array_merge($item, [
                            'category_id' => $category->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }, $items);

                    PopularItem::insert($preparedItems);

                    // Store items for this category
                    $allItems = array_merge($allItems, $items);
                    FailedCategory::where('category_id', $category->id)->delete();
                    Category::where('id', $category->id)->update([
                        'is_complete' => 1
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('An unexpected error occurred for category ' . $category->slug . ': ' . $e->getMessage());
                    FailedCategory::updateOrCreate(
                        ['category_id' => $category->id],
                        [
                            'error_message' => $e->getMessage(),
                            'attempted_at' => now()
                        ]
                    );
                }
            }
        }

        return $allItems;
    }

    // public function portFolio()
    // {
    //     $authors = Author::select('name')->take(5)->where('is_complete', 0)->distinct()->pluck('name');
    //     if ($authors->isEmpty()) {
    //         Log::info('No authors found.');
    //         return response()->json(['message' => 'No authors found.']);
    //     }

    //     $requests = function ($authors) {
    //         foreach ($authors as $author) {
    //             $url = "https://codecanyon.net/user/{$author}/portfolio?order_by=sales";
    //             yield new Request('GET', $url);
    //         }
    //     };

    //     $results = [];

    //     // Use Guzzle Pool to make multiple requests concurrently
    //     $pool = new Pool($this->client, $requests($authors), [
    //         'concurrency' => 5,
    //         'fulfilled' => function ($response, $index) use ($authors, &$results) {
    //             $author = $authors[$index];
    //             $html = $response->getBody()->getContents();
    //             $crawler = new Crawler($html);

    //             try {
    //                 $userStats = $crawler->filter('.user-info-header__user-stats')->each(function (Crawler $node) {
    //                     $rating = 0;
    //                     $totalRatings = 0;

    //                     if ($node->filter('.star-rating .is-visually-hidden')->count() > 0) {
    //                         $ratingText = $node->filter('.star-rating .is-visually-hidden')->text();
    //                         preg_match('/(\d+\.\d+)/', $ratingText, $ratingMatch);
    //                         $rating = $ratingMatch[1] ?? 0;
    //                     }

    //                     if ($node->filter('.t-body.-size-s')->count() > 0) {
    //                         $ratingsText = $node->filter('.t-body.-size-s')->text();
    //                         preg_match('/(\d+)/', $ratingsText, $ratingsMatch);
    //                         $totalRatings = $ratingsMatch[1] ?? 0;
    //                     }

    //                     $totalSales = trim($node->filter('.user-info-header__stats-content strong')->text());

    //                     return [
    //                         'rating' => $rating,
    //                         'total_ratings' => $totalRatings,
    //                         'total_sales' => $totalSales,
    //                     ];
    //                 });

    //                 $items = $crawler->filter('ul.product-list li')->each(function (Crawler $node) use ($author, $userStats) {
    //                     try {
    //                         $image = $node->filter('.item-thumbnail__image img')->attr('src') ?? null;
    //                         $category = $node->filter('.product-list__column-category p')->text() ?? null;
    //                         $name = trim($node->filter('.product-list__heading a')->text()) ?? null;
    //                         $price = preg_replace('/[^0-9]/', '', $node->filter('.product-list__price-desktop')->text());
    //                         $ratingsText = $node->filter('.product-list__info-desktop')->text();
    //                         preg_match('/(\d+)\s+ratings/', $ratingsText, $ratingsMatch);
    //                         $ratings = $ratingsMatch[1] ?? 0;

    //                         $salesText = $node->filter('.product-list__sales-desktop')->text();
    //                         preg_match('/(\d+)\s+Sales/', $salesText, $salesMatch);
    //                         $sales = $salesMatch[1] ?? 0;

    //                         return [
    //                             'author_name' => $author->name,
    //                             'image' => $image,
    //                             'name' => $name,
    //                             'category' => $category,
    //                             'price' => $price,
    //                             'ratings' => $ratings,
    //                             'sales' => $sales,
    //                             'rating' => $userStats[0]['rating'] ?? 0,
    //                             'total_ratings' => $userStats[0]['total_ratings'] ?? 0,
    //                             'total_sales' => $userStats[0]['total_sales'] ?? 0,
    //                         ];
    //                     } catch (\Exception $e) {
    //                         return null;
    //                     }
    //                 });

    //                 $results = array_merge($results, $items);
    //             } catch (\Exception $e) {
    //                 Log::error("Error processing author: {$author}. Error: " . $e->getMessage());
    //             }
    //         },
    //         'rejected' => function ($reason, $index) use ($authors) {
    //             $author = $authors[$index];
    //             Log::error("Failed to fetch page for author: {$author}. Reason: " . $reason);
    //         },
    //     ]);

    //     $pool->promise()->wait();

    //     $results = array_filter($results); // Remove null values

    //     // Insert the items into the database in batches
    //     foreach (array_chunk($results, 100) as $batch) {
    //         $batch = array_map(function ($item) {
    //             return array_merge($item, [
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }, $batch);
    //         PortfolioItem::insert($batch);
    //     }

    //     return response()->json(['message' => 'Portfolio data successfully fetched and stored.', 'total_items' => count($results)]);
    // }


    public function portFolio()
    {
        $authorCount = Author::where('is_complete', 0)->count();

        $iterations = ceil($authorCount / 5);

        for ($i = 0; $i < $iterations; $i++) {
            Artisan::call('portfolio:fetch');
            $output = Artisan::output();
            Log::info('Portfolio Fetch Output: ' . $output);
            sleep(5);
        }
    }

    public function addAuthor()
    {
        $authors = PopularItem::select('author_name')->distinct()->pluck('author_name')->toArray();

        foreach ($authors as $author) {
            Author::updateOrCreate(
                ['name' => $author],
                ['is_complete' => 0]
            );
        }

        return response()->json(['message' => 'Authors added successfully!']);
    }
}
