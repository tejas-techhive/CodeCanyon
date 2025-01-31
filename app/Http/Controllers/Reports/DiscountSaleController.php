<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Discounted;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscountSaleController extends Controller
{
    public function index(Request $request, $author_name = null)
    {
        $items = Discounted::query();
        $categoryId = $request->category;
        if (!empty($categoryId)) {
            $items->where('category_id', $categoryId);
        }

        // Search filter
        $searchTerm = $request->search;
        if (!empty($searchTerm)) {
            $items->where(function ($q) use ($searchTerm) {
                $q->where('language_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('item_id', 'LIKE', "%{$searchTerm}%");
            });
        }
        //    dd( $items);
        // Date range filter
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        if (!empty($startDate) && !empty($endDate)) {
            $items->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $site = $request->input('sites');
        if ($site != '') {
            $items->where('site', $site);
        }
        // Get the data and group it by 'item_id'
        $items = $items->orderBy('created_at', 'asc')->get()->groupBy('item_id');

        // Process data to calculate total sales difference
        $result = $items->map(function ($itemGroup) {
            $first = $itemGroup->whereNotNull('total_sales')->first() ?? $itemGroup->first(); // First record
            $last = $itemGroup->whereNotNull('total_sales')->last() ?? $itemGroup->last();   // Last record

            return [
                'item_id' => $first->item_id,
                'title' => $first->title,
                'total_sales_difference' => (
                    (float) str_replace(',', '', $last->sales ?? 0) -
                    (float) str_replace(',', '', $first->sales ?? 0)
                ),
                'first_total_sales' => $first->sales,
                'last_total_sales' => $last->sales,
                'trending' => $itemGroup->where('trending', 'Yes')->count(),
                'image' => $first->image,
                'price' => $first->price,
                'offer' => $first->offer,
                'site' => $first->site,
                'author_name' => $first->author_name,
                'language_name' => $first->language_name,
                'last_update' => $first->last_update,
                'created_at' => $first->created_at,
                'sales' => $first->sales,
                'single_url' => $first->single_url,



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
        $items = new LengthAwarePaginator($currentResults, $result->count(), $perPage, $currentPage);

        return view('main_reports.discount-items',compact('items', 'searchTerm', 'sortOrder', 'startDate', 'endDate'));
    }
}
