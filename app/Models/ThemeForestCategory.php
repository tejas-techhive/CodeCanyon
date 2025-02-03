<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeForestCategory extends Model
{
    use HasFactory;

    protected $table = 'theme_forest_categories';


    protected $fillable = ['name', 'parent_id', 'slug','is_complete'];

    public function parent()
    {
        return $this->belongsTo(ThemeForestCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ThemeForestCategory::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function popularItems()
    {
        return $this->hasMany(PopularItemTheme::class, 'theme_category_id');
    }

    public function hasPopularItemToday()
    {
        // dd($this->popularItems()->get());
        return $this->popularItems()
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }
}
