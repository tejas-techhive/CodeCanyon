<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popular Items</title>
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/flatpickr.min.css') }}">
    <!-- Bootstrap CSS -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"> --}}

    <!-- Flatpickr CSS -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> --}}
</head>

<body>

    <div class="container mt-5">
        <a href="{{ url('/') }}" class="btn btn-secondary mb-3">Back</a>
        <h1 class="text-center mb-4">Popular Items</h1>

        <!-- Search and Category Filter Form -->
        <form action="{{ route('popular.items') }}" method="GET" class="mb-4">
            <div class="row">
                <!-- Category Filter -->
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ ucfirst($category->slug) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Filter -->
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search"
                        value="{{ $searchTerm }}" id="category-filter">
                </div>

                <div class="col-md-2">
                    <div class="input-group">
                        <input type="text" name="start_date" class="form-control datepicker" placeholder="Start Date"
                            value="{{ request('start_date') }}">
                        <button type="button" id="clear-start-date"
                            class="btn btn-sm btn-outline-secondary">Clear</button>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <input type="text" name="end_date" class="form-control datepicker" placeholder="End Date"
                            value="{{ request('end_date') }}">
                        <button type="button" id="clear-end-date"
                            class="btn btn-sm btn-outline-secondary">Clear</button>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100" id="search-button">Search</button>
                </div>
            </div>
        </form>
        <div class="col-md-2">
            <select name="sort_by_created_date" id="sort_by_created_date" class="form-select">
                <option value="desc" {{ request('sort_by_created_date') == 'desc' ? 'selected' : '' }}>Descending
                </option>
                <option value="asc" {{ request('sort_by_created_date') == 'asc' ? 'selected' : '' }}>Ascending
                </option>
            </select>
        </div>

        <!-- View Toggle Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-primary me-2 view-toggle" data-view="grid">Grid View</button>
            <button class="btn btn-outline-primary view-toggle active" data-view="table">Table View</button>
        </div>


        <!-- Display Popular Items in Grid View -->
        <div id="grid-view" class="row d-none">
            @foreach ($popularItems as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="{{ $item->image }}" class="card-img-top" alt="{{ $item->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->name }}</h5>
                            <p class="card-text"><strong>By:</strong> <a
                                    href="{{ route('portfolio.items', $item->author_name) }}">{{ $item->author_name }}</a>
                                in <a href="javascript:void(0);" data-id="{{ $item->language_name }}"
                                    class="category-link">{{ $item->language_name }}</a></p>
                            <p class="card-text"><strong>Price:</strong>
                                @if ($item->offer)
                                    <span class="text-danger">${{ $item->offer }}</span>
                                    <small class="text-muted text-decoration-line-through">${{ $item->price }}</small>
                                @else
                                    ${{ $item->price }}
                                @endif
                            </p>
                            <p class="card-text"><strong>Trending:</strong> {{ $item->trending }}</p>
                            <p class="card-text"><strong>Category:</strong> {{ $item->category->slug }}</p>
                            <p class="card-text"><strong>Sales:</strong> {{ $item->sales }}</p>
                            <p class="card-text"><strong>Total Sales:</strong> {{ $item->total_sales }}</p>
                            <p class="card-text"><strong>Published Date:</strong>
                                {{ $item->published }}</p>
                            <p class="card-text"><strong>Last Update:</strong> {{ $item->formatted_timestamp }}</p>
                            <p class="card-text"><strong>Created Date:</strong>
                                {{ $item->created_at->format('Y-m-d') }}</p>
                            {{-- <a href="{{ $item->single_url }}" target="_blank" class="btn btn-primary btn-sm">View
                                Item</a> --}}
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
                        <th>Trending</th>
                        <th>Category</th>
                        <th>Sales</th>
                        <th>Total Sales</th>
                        <th>Published Date</th>
                        <th>Last Updated</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($popularItems as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><img src="{{ $item->image }}" width="50" alt="{{ $item->name }}"></td>
                            <td>{{ $item->name }}</td>
                            <td><a
                                    href="{{ route('portfolio.items', $item->author_name) }}">{{ $item->author_name }}</a>
                            </td>
                            <td>${{ $item->price }}</td>
                            <td>{{ $item->trending }}</td>
                            <td>{{ $item->category->slug }}</td>
                            <td>{{ $item->sales }}</td>
                            <td>{{ $item->total_sales }}</td>
                            <td>{{ $item->published }}</td>
                            <td>{{ $item->formatted_timestamp }}</td>
                            <td>{{ $item->created_at->format('Y-m-d') }}</td>
                            <td><a href="{{ $item->single_url }}" target="_blank"
                                    class="btn btn-sm btn-primary">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $popularItems->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Bootstrap JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script src="{{ asset('asset/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Flatpickr JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> --}}
    <script src="{{ asset('asset/js/flatpickr.js') }}"></script>

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
    </script>

</body>

</html>
