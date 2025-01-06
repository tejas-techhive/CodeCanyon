<?php

namespace App\Console\Commands;

use App\Http\Controllers\CodecanyonController;
use Illuminate\Console\Command;

class FetchFailedPopularItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:failed-popular-items';

    /**
     * The console command description.
     *
     * @var string
     */
   
    protected $description = 'Retry fetching failed popular items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new CodecanyonController())->getFailedPopularItems();
    }
}
