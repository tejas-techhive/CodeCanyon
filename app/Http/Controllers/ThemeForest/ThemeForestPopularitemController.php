<?php

namespace App\Http\Controllers\ThemeForest;

use App\Http\Controllers\Controller;
use App\Models\PopularItemTheme;
use App\Models\ThemeForestCategory;
use Illuminate\Http\Request;

class ThemeForestPopularitemController extends Controller
{
    public function themeForestPopular(Request $request)
    {
        $query = PopularItemTheme::query()->with('forest_category');

        // Search filter
        $searchTerm = $request->search;
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('language_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Category filter
        $categoryId = $request->category;
        if (!empty($categoryId)) {
            $query->where('theme_category_id', $categoryId);
        }

        // Date range filter
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Sorting logic
        $sortOrder = $request->input('sort_by_created_date', 'desc'); // Default to 'desc'
        $query->orderBy('created_at', $sortOrder);

        // Paginate results
        $popularItems = $query->paginate(50);

        // Maintain filters on pagination links
        $popularItems->appends([
            'search' => $request->search,
            'category' => $request->category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'sort_by_created_date' => $sortOrder,
        ]);

        // Get categories to display in the filter dropdown
        $categories = ThemeForestCategory::select('id', 'slug')->get();

        // Pass the data to the view
        return view('popular_items.index_forest', compact('popularItems', 'searchTerm', 'categories', 'categoryId', 'sortOrder', 'startDate', 'endDate'));
    }
}
