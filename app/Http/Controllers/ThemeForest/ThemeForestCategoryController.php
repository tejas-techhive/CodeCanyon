<?php

namespace App\Http\Controllers\ThemeForest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\PopularItem;
use App\Models\PopularItemTheme;
use App\Models\PortfolioItem;
use App\Models\PortfolioItemThemeForest;
use App\Models\ThemeForestCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class ThemeForestCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ThemeForestCategory::whereNull('parent_id')->get();
        $categories->each(function ($category) {
            // dd($category->hasPopularItemToday() );
            $category->flat = $category->hasPopularItemToday() ? 'yes' : 'no';
        });
        // Return the index view and pass the categories to it
        return view('themes.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = ThemeForestCategory::all(); // Get all categories to list them as parent options
        return view('themes.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:theme_forest_categories,slug',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        // Set default value for site_type if it's not provided
        if (!isset($validatedData['site_type'])) {
            $validatedData['site_type'] = 0;
        }

        ThemeForestCategory::create($validatedData);

        return redirect()->route('theme-categories.index')->with('success', ' ThemeForest Category created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Category $category)
    {
        $categories = ThemeForestCategory::where('id', '!=', $category->id)->get(); // Prevent parent from being self
        return view('themes.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category->update($request->all());

        return redirect()->route('theme-categories.index')->with('success', ' ThemeForest Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showPopularItems(Request $request)
    {
        $query = PopularItemTheme::query()->with('category');

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
            $query->where('category_id', $categoryId);
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
        return view('popular_items.index', compact('popularItems', 'searchTerm', 'categories', 'categoryId', 'sortOrder', 'startDate', 'endDate'));
    }

    public function showPortfolioItems(Request $request, $author_name = null)
    {
        if (isset($author_name)) {
            $query = PortfolioItem::query()->where('author_name', $author_name);
        } else {
            $query = PortfolioItem::query();
        }

        if ($searchTerm = $request->input('search')) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('selected_date')) {
            $query->whereDate('created_at', $request->input('selected_date'));
        }

        $sortBy = $request->input('sort_by'); // Get sort option
        if ($sortBy === 'sales') {
            $query->orderBy('sales', 'desc'); // Sort by Sales
        } elseif ($sortBy === 'ratings') {
            $query->orderBy('ratings', 'desc'); // Sort by Ratings
        } else {
            $query->latest(); // Default sorting
        }


        $latest = $query->latest()->first();
        $popularItems = $query->paginate(30);

        $popularItems->appends($request->except('page'));

        return view('portfolio_items.index', compact('popularItems', 'request', 'latest', 'author_name'));
    }

    public function showPortfolioItemsThemeForest(Request $request, $author_name = null)
    {
        if (isset($author_name)) {
            $query = PortfolioItemThemeForest::query()->where('author_name', $author_name);
        } else {
            $query = PortfolioItemThemeForest::query();
        }

        if ($searchTerm = $request->input('search')) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('selected_date')) {
            $query->whereDate('created_at', $request->input('selected_date'));
        }

        $sortBy = $request->input('sort_by'); // Get sort option
        if ($sortBy === 'sales') {
            $query->orderBy('sales', 'desc'); // Sort by Sales
        } elseif ($sortBy === 'ratings') {
            $query->orderBy('ratings', 'desc'); // Sort by Ratings
        } else {
            $query->latest(); // Default sorting
        }


        $latest = $query->latest()->first();
        $popularItems = $query->paginate(30);

        $popularItems->appends($request->except('page'));

        return view('portfolio_items.index', compact('popularItems', 'request', 'latest', 'author_name'));
    }
}
