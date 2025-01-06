<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class PopularItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
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

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public function getFormattedTimestampAttribute()
    {
        return Carbon::parse($this->last_update)->format('Y-m-d h:i:s A');
    }
}
