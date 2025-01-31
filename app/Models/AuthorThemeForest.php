<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorThemeForest extends Model
{
    use HasFactory;
    protected $table = 'authors_themes_forest';

    
    protected $fillable = [
        'name',
        'is_complete'
    ];
}
