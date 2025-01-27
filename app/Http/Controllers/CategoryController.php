<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\PopularItem;
use App\Models\PortfolioItem;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::whereNull('parent_id')->get();
        $categories->each(function ($category) {
            $category->flat = $category->hasPopularItemToday() ? 'yes' : 'no';
        });
        // Return the index view and pass the categories to it
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::all(); // Get all categories to list them as parent options
        return view('categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
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
        $categories = Category::where('id', '!=', $category->id)->get(); // Prevent parent from being self
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
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
        $query = PopularItem::query()->with('category');

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
        $categories = Category::select('id', 'slug')->get();

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

    public function showPopularReports(Request $request)
    {
        // Initialize query
        $items = PopularItem::query();

        // Category filter
        $categoryId = $request->category;
        if (!empty($categoryId)) {
            $items->where('category_id', $categoryId);
        }

        // Search filter
        $searchTerm = $request->search;
        if (!empty($searchTerm)) {
            $items->where(function ($q) use ($searchTerm) {
                $q->where('language_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('item_id', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Date range filter
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        if (!empty($startDate) && !empty($endDate)) {
            $items->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Get the data and group it by 'item_id'
        $items = $items->orderBy('created_at', 'asc')->get()->groupBy('item_id');

        // Process data to calculate total sales difference
        $result = $items->map(function ($itemGroup) {
            $first = $itemGroup->whereNotNull('total_sales')->first() ?? $itemGroup->first(); // First record
            $last = $itemGroup->whereNotNull('total_sales')->last() ?? $itemGroup->last();   // Last record

            return [
                'item_id' => $first->item_id,
                'name' => $first->name,
                'total_sales_difference' => (
                    (float) str_replace(',', '', $last->total_sales ?? 0) -
                    (float) str_replace(',', '', $first->total_sales ?? 0)
                ),
                'first_total_sales' => $first->total_sales,
                'last_total_sales' => $last->total_sales,
                'price' => $last->price,
                'image' => $last->image,
                'author_name' => $last->author_name,
                'published' => $last->published,
                'trending' => $itemGroup->where('trending', 'Yes')->count(),
            ];
        });
        // Sort by total_sales_difference (ascending or descending based on the user input)

        $sortOrder = $request->sort_order ?? 'desc';

        $result = $result->sortBy(function ($item) {
            return $item['total_sales_difference']; // Sort by 'total_sales_difference' field
        }, SORT_REGULAR, $sortOrder === 'desc')->values();

        // Pagination logic for the result
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentResults = $result->slice(($currentPage - 1) * $perPage, $perPage);
        $paginatedResults = new LengthAwarePaginator($currentResults, $result->count(), $perPage, $currentPage);

        // Fetch categories
        $categories = Category::all();

        // Return view with required data
        return view('popular_items.reports', compact('paginatedResults', 'searchTerm', 'categories', 'categoryId'));
    }
}
