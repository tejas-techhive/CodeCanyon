@extends('layouts.app')
@section('title', 'Rising star')
@section('content')

    <div class="container mt-5">
        <a href="{{ url('/') }}" class="btn btn-secondary mb-3">Back</a>
        <h1 class="text-center mb-4"> Rising star</h1>

        <!-- Search and Category Filter Form -->
        <form action="{{ route('reports.rising-star') }}" method="GET" class="mb-4">
            <div class="row">

                <!-- Search Filter -->
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search"
                        value="{{ $searchTerm }}" id="category-filter">
                </div>

                <div class="col-md-2">
                    <div class="input-group">
                        <input type="text" name="start_date" class="form-control datepicker" placeholder="Start Date"
                            value="{{ request('start_date') }}">
                        <button type="button" id="clear-start-date" class="btn btn-sm btn-outline-secondary">Clear</button>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <input type="text" name="end_date" class="form-control datepicker" placeholder="End Date"
                            value="{{ request('end_date') }}">
                        <button type="button" id="clear-end-date" class="btn btn-sm btn-outline-secondary">Clear</button>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" id="search-button">Search</button>
                </div>
            </div>
        </form>
        <div class="row">

            <div class="col-md-2">
                <select name="sort_by_created_date" id="sort_by_created_date" class="form-select">
                    <option value="desc" {{ request('sort_by_created_date') == 'desc' ? 'selected' : '' }}>Descending
                    </option>
                    <option value="asc" {{ request('sort_by_created_date') == 'asc' ? 'selected' : '' }}>Ascending
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sites" id="sites" class="form-select">
                    <option value="" {{ request('sites') == '' ? 'selected' : '' }}>Select Site</option>
                    <option value="theme_forest" {{ request('sites') == 'theme_forest' ? 'selected' : '' }}>Theme forest
                    </option>
                    <option value="codecanyon" {{ request('sites') == 'codecanyon' ? 'selected' : '' }}>Code Canyon
                    </option>
                </select>
            </div>

        </div>

        <!-- View Toggle Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-primary me-2 view-toggle" data-view="grid">Grid View</button>
            <button class="btn btn-outline-primary view-toggle active" data-view="table">Table View</button>
        </div>


        <!-- Display Popular Items in Grid View -->
        <div id="grid-view" class="row d-none">
            {{-- @dd($$items) --}}
            @foreach ($items as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="{{ $item['image'] }}" class="card-img-top" alt="{{ $item['title'] }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item['title'] }}</h5>
                            <p class="card-text"><strong>By:</strong>
                                @if ($item['site'] == 'codecanyon')
                                    <a
                                        href="{{ route('portfolio.items', $item['author_name']) }}">{{ $item['author_name'] }}</a>
                                @else
                                    <a
                                        href="{{ route('portfolio.items.themeforest', $item['author_name']) }}">{{ $item['author_name'] }}</a>
                                @endif

                                in
                                <a href="javascript:void(0);" data-id="{{ $item['language_name'] }}"
                                    class="category-link">{{ $item['language_name'] }}</a>
                            </p>



                            <p class="card-text"><strong>Price:</strong>
                                @if ($item['offer'])
                                    <span class="text-danger">${{ $item['offer'] }}</span>
                                    <small class="text-muted text-decoration-line-through">${{ $item['price'] }}</small>
                                @else
                                    ${{ $item['price'] }}
                                @endif
                            </p>


                            {{-- <p class="card-text"><strong>Sales:</strong> {{ $item->sales }}</p> --}}



                            <p class="card-text"><strong>Created Date:</strong>
                                {{ $item['created_at']->format('Y-m-d') }}</p>
                            <a href="{{ $item['single_url'] }}" target="_blank" class="btn btn-primary btn-sm">View
                                Item</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Display Popular Items in Table View -->
        <div id="table-view">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Sales Difference</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><img src="{{ $item['image'] }}" width="50" alt="{{ $item['title'] }}"></td>
                            <td>{{ $item['title'] }}</td>
                            <td>

                                @if ($item['site'] == 'codecanyon')
                                    <a
                                        href="{{ route('portfolio.items', $item['author_name']) }}">{{ $item['author_name'] }}</a>
                                @else
                                    <a
                                        href="{{ route('portfolio.items.themeforest', $item['author_name']) }}">{{ $item['author_name'] }}</a>
                                @endif

                            </td>
                            <td>
                                @if ($item['offer'])
                                    <span class="text-danger">${{ $item['offer'] }}</span>
                                    <small class="text-muted text-decoration-line-through">${{ $item['price'] }}</small>
                                @else
                                    ${{ $item['price'] }}
                                @endif
                            </td>
                            <td>
                                <p><span>{{ $item['last_total_sales'] }}</span> -
                                    <span>{{ $item['first_total_sales'] }}</span>
                                </p>
                                </p><b>{{ $item['total_sales_difference'] }}</b>
                            </td>

                            <td>{{ $item['created_at']->format('Y-m-d') }}</td>
                            <td><a href="{{ $item['single_url'] }}" target="_blank" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <!-- Pagination -->
        <div class="mt-4 w-100">
            @if ($items->lastPage() > 1)
                <nav>
                    <ul class="pagination">
                        <!-- Previous Page Link -->
                        <li class="page-item {{ $items->currentPage() == 1 ? 'disabled' : '' }}">
                            <a class="page-link"
                                href="{{ route('reports.rising-star', array_merge(request()->all(), ['page' => 1])) }}">First</a>
                        </li>
                        <li class="page-item {{ $items->currentPage() == 1 ? 'disabled' : '' }}">
                            <a class="page-link"
                                href="{{ route('reports.rising-star', array_merge(request()->all(), ['page' => $items->currentPage() - 1])) }}">Previous</a>
                        </li>

                        <!-- Pagination Links -->
                        @for ($i = 1; $i <= $items->lastPage(); $i++)
                            <li class="page-item {{ $items->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link"
                                    href="{{ route('reports.rising-star', array_merge(request()->all(), ['page' => $i])) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        <!-- Next Page Link -->
                        <li
                            class="page-item {{ $items->currentPage() == $items->lastPage() ? 'disabled' : '' }}">
                            <a class="page-link"
                                href="{{ route('reports.rising-star', array_merge(request()->all(), ['page' => $items->currentPage() + 1])) }}">Next</a>
                        </li>
                        <li
                            class="page-item {{ $items->currentPage() == $items->lastPage() ? 'disabled' : '' }}">
                            <a class="page-link"
                                href="{{ route('reports.rising-star', array_merge(request()->all(), ['page' => $items->lastPage()])) }}">Last</a>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>

@endsection

@section('scripts')

    <script>
        flatpickr('input[name="start_date"]', {
            enableTime: false,
            dateFormat: 'Y-m-d',
            defaultDate: "{{ request('start_date') }}",
        });

        document.getElementById('clear-start-date').addEventListener('click', function() {
            const startDateInput = document.querySelector('input[name="start_date"]');
            startDateInput.value = ''; // Clear the input value
            startDateInput._flatpickr.clear(); // Clear Flatpickr's selected date
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

        document.querySelectorAll('.view-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const view = this.dataset.view;

                // Remove 'active' class from all buttons
                document.querySelectorAll('.view-toggle').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add 'active' class to the clicked button
                this.classList.add('active');

                // Toggle views based on the clicked button 
                if (view === 'grid') {
                    document.getElementById('grid-view').classList.remove('d-none');
                    document.getElementById('table-view').classList.add('d-none');
                } else if (view === 'table') {
                    document.getElementById('table-view').classList.remove('d-none');
                    document.getElementById('grid-view').classList.add('d-none');
                }
            });
        });


        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                const categoryFilter = document.getElementById('category-filter');
                const searchButton = document.getElementById('search-button');

                // Set the selected category in the dropdown
                categoryFilter.value = categoryId;

                // Trigger the search button
                searchButton.click();
            });
        });

        document.getElementById('sort_by_created_date').addEventListener('change', function() {
            const selectedValue = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('sort_by_created_date', selectedValue);
            window.location.href = url.toString();
        });
        document.getElementById('sites').addEventListener('change', function() {
            const selectedValue = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('sites', selectedValue);
            window.location.href = url.toString();
        });
    </script>
@endsection
