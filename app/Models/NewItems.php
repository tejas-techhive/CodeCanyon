<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewItems extends Model
{
    use HasFactory;
    protected $table = 'new_items';

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
