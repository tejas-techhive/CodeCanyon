<?php

namespace App\Console\Commands;

use App\Http\Controllers\CodecanyonController;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;

class DailyTaskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform daily tasks at 1 AM - set category/author is complete = 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Category::query()->update(['is_complete' => 0]);
        Author::query()->update(['is_complete' => 0]);
        (new CodecanyonController())->addAuthor();
    }
}
