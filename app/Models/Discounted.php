<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discounted extends Model
{
    use HasFactory;
    protected $table = 'discounted';

    protected $fillable = [
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
        'description',
        'last_update',
        'trending',
        'item_id',
        'single_url',

    ];
}
