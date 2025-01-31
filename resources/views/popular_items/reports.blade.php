@extends('layouts.app')
@section('title', 'reports List')

@section('content')

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Sell By Date</title>
        <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('asset/css/flatpickr.min.css') }}">
    </head>

        <div class="container mt-4">
            <a href="{{ url('/') }}" class="btn btn-secondary mb-3">Back</a>
            <h1 class="mb-4">Sell By Date</h1>

            <!-- Filters Form -->
            <form action="{{ route('popular.items.reports') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ ucfirst($category->slug) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search"
                            value="{{ $searchTerm }}">
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <div class="input-group">
                            <input type="text" name="start_date" class="form-control datepicker" placeholder="Start Date"
                                value="{{ request('start_date') }}">
                            <button type="button" id="clear-start-date"
                                class="btn btn-sm btn-outline-secondary">Clear</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <div class="input-group">
                            <input type="text" name="end_date" class="form-control datepicker" placeholder="End Date"
                                value="{{ request('end_date') }}">
                            <button type="button" id="clear-end-date"
                                class="btn btn-sm btn-outline-secondary">Clear</button>
                        </div>
                    </div>

                    <!-- Total Sales Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Sort Sales</label>
                        <select name="sort_order" class="form-select">
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>High to Low
                            </option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Low to High
                            </option>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>

            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Sales Difference</th>
                            <th>Trending</th>
                            <th>Author Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paginatedResults as $item)
                            <tr>
                                <td>{{ $item['item_id'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>${{ number_format($item['price'], 2) }}</td>
                                <td>
                                    <p><span>{{ $item['last_total_sales'] }}</span> -
                                        <span>{{ $item['first_total_sales'] }}</span>
                                    </p>
                                    </p><b>{{ $item['total_sales_difference'] }}</b>
                                </td>
                                <td>{{ $item['trending'] }}</td>
                                <td><a
                                        href="{{ route('portfolio.items', $item['author_name']) }}">{{ $item['author_name'] }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination -->
                <div class="mt-4 w-100">
                    @if ($paginatedResults->lastPage() > 1)
                        <nav>
                            <ul class="pagination">
                                <!-- Previous Page Link -->
                                <li class="page-item {{ $paginatedResults->currentPage() == 1 ? 'disabled' : '' }}">
                                    <a class="page-link"
                                        href="{{ route('popular.items.reports', array_merge(request()->all(), ['page' => 1])) }}">First</a>
                                </li>
                                <li class="page-item {{ $paginatedResults->currentPage() == 1 ? 'disabled' : '' }}">
                                    <a class="page-link"
                                        href="{{ route('popular.items.reports', array_merge(request()->all(), ['page' => $paginatedResults->currentPage() - 1])) }}">Previous</a>
                                </li>

                                <!-- Pagination Links -->
                                @for ($i = 1; $i <= $paginatedResults->lastPage(); $i++)
                                    <li class="page-item {{ $paginatedResults->currentPage() == $i ? 'active' : '' }}">
                                        <a class="page-link"
                                            href="{{ route('popular.items.reports', array_merge(request()->all(), ['page' => $i])) }}">{{ $i }}</a>
                                    </li>
                                @endfor

                                <!-- Next Page Link -->
                                <li
                                    class="page-item {{ $paginatedResults->currentPage() == $paginatedResults->lastPage() ? 'disabled' : '' }}">
                                    <a class="page-link"
                                        href="{{ route('popular.items.reports', array_merge(request()->all(), ['page' => $paginatedResults->currentPage() + 1])) }}">Next</a>
                                </li>
                                <li
                                    class="page-item {{ $paginatedResults->currentPage() == $paginatedResults->lastPage() ? 'disabled' : '' }}">
                                    <a class="page-link"
                                        href="{{ route('popular.items.reports', array_merge(request()->all(), ['page' => $paginatedResults->lastPage()])) }}">Last</a>
                                </li>
                            </ul>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
@endsection

@section('scripts')
    <script>
        flatpickr('input[name="start_date"]', {
            enableTime: false,
            dateFormat: 'Y-m-d',
            defaultDate: "{{ request('start_date') }}"
        });
        document.getElementById('clear-start-date').addEventListener('click', function() {
            const endDateInput = document.querySelector('input[name="start_date"]');
            endDateInput.value = '';
            endDateInput._flatpickr.clear();
        });
        flatpickr('input[name="end_date"]', {
            enableTime: false,
            dateFormat: 'Y-m-d',
            defaultDate: "{{ request('end_date') }}"
        });
        document.getElementById('clear-end-date').addEventListener('click', function() {
            const endDateInput = document.querySelector('input[name="end_date"]');
            endDateInput.value = '';
            endDateInput._flatpickr.clear();
        });
    </script>
@endsection
