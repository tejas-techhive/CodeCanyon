<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItemThemeForest extends Model
{
    use HasFactory;
    protected $table = 'portfolio_items_themeforest';

    protected $fillable = [
        'author_name',
        'image',
        'name',
        'category',
        'price',
        'ratings',
        'sales',
        'rating',
        'total_ratings',
        'total_sales',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;
}
