<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedCategoryTheme extends Model
{
    use HasFactory;
    protected $table = 'failed_categories_theme';

    protected $fillable = ['category_id', 'error_message', 'attempted_at'];
}


