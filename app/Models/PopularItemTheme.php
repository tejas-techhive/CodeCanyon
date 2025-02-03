<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PopularItemTheme extends Model
{
    use HasFactory;
    protected $table = 'popular_item_themes';

    protected $fillable = [
        'theme_category_id',
        'item_id',
        'name',
        'single_url',
        'image',
        'by',
        'author_link',
        'author_name',
        'language_name',
        'language_link',
        'price',
        'offer',
        'stars',
        'reviews',
        'sales',
        'trending',
        'total_sales',
        'last_update',
        'published',
    ];

    public function forest_category()
    {
        return $this->belongsTo(ThemeForestCategory::class, 'theme_category_id');
    }


    public function getFormattedTimestampAttribute()
    {
        return Carbon::parse($this->last_update)->format('Y-m-d h:i:s A');
    }

    public function getPublishedAttribute($value)
    {
        // Format the date to 'Y-m-d' (2021-04-06)
        return Carbon::parse($value)->format('Y-m-d');
    }
}
