<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisingStar extends Model
{
    use HasFactory;
    protected $table = 'rising_star';

    protected $fillable = [
        'site',
        'title',
        'link',
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
        'last_update',
        'trending',
        'item_id',
        'single_url',

    ];
}
