<?php

namespace App\Console\Commands;

use App\Http\Controllers\CodecanyonController;
use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\PopularItem;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\DB;

class FetchPopularItems extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:popular-items';
    protected $client;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch popular items from categories and save them to the database';


    /**
     * Execute the console command.
     */

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }


    public function handle()
    {
        $allItems = (new CodecanyonController())->getPopularItems();
        $this->info('Popular items fetched successfully!');
    }
}
