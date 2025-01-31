<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Featured extends Model
{
    use HasFactory;
    protected $table = 'featured';

    protected $fillable = [
        'featured_type',
        'site',
        'title',
        'link',
        'author',
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
        'image',
        'trending',
        'item_id',
        'single_url',

    ];
}
