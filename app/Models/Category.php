<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id', 'slug','is_complete'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function popularItems()
    {
        return $this->hasMany(PopularItem::class, 'category_id');
    }

    public function hasPopularItemToday()
    {
        return $this->popularItems()
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

}
