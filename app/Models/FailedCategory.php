<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'error_message', 'attempted_at'];
}
