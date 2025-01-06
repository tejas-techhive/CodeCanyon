<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\PopularItem;
use App\Models\PortfolioItem;

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

        // Paginate results
        $popularItems = $query->paginate(12);

        // Maintain filters on pagination links
        $popularItems->appends([
            'search' => $request->search,
            'category' => $request->category,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Get categories to display in the filter dropdown
        $categories = Category::all();

        // Pass the data to the view
        return view('popular_items.index', compact('popularItems', 'searchTerm', 'categories', 'categoryId'));
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

        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('created_at', ["$startDate 00:00:00", "$endDate 23:59:59"]);
        }

        if ($request->filled('price')) {
            $query->where('price', '<=', $request->input('price'));
        }

        if ($request->filled('ratings')) {
            $query->where('ratings', '>=', $request->input('ratings'));
        }

        $popularItems = $query->paginate(12);

        $popularItems->appends($request->except('page'));

        return view('portfolio_items.index', compact('popularItems', 'request'));
    }
}
