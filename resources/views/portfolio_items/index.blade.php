<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Items</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>

    <div class="container mt-5">
        <a href="{{ url('/') }}" class="btn btn-secondary mb-3">Back</a>
        <h1 class="text-center mb-4">Portfolio Items</h1>

        <!-- Filters Form -->
        <form action="{{ route('portfolio.items', $request->author_name) }}" method="GET" class="mb-4">
            <div class="row">
                <!-- Search Filter -->
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or category"
                        value="{{ request('search') }}">
                </div>

                <!-- Date Range Filter -->
                <div class="col-md-2 mb-2">
                    <input type="text" name="start_date" class="form-control datepicker" placeholder="Start Date"
                        value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" name="end_date" class="form-control datepicker" placeholder="End Date"
                        value="{{ request('end_date') }}">
                </div>

                <!-- Price Filter -->
                <div class="col-md-2 mb-2">
                    <input type="number" name="price" class="form-control" placeholder="Max Price"
                        value="{{ request('price') }}">
                </div>

                <!-- Ratings Filter -->
                <div class="col-md-1 mb-2">
                    <input type="number" name="ratings" class="form-control" placeholder="Min Rating"
                        value="{{ request('ratings') }}" min="1" max="5">
                </div>

                <!-- Submit Button -->
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <!-- View Toggle Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-outline-primary me-2 view-toggle" data-view="grid">Grid View</button>
            <button class="btn btn-outline-primary view-toggle" data-view="table">Table View</button>
        </div>

        <!-- Display Portfolio Items in Grid View -->
        <div id="grid-view" class="row">
            @foreach ($popularItems as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="{{ $item->image }}" class="card-img-top" alt="{{ $item->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $item->name }}</h5>
                            <p class="card-text"><strong>Author:</strong> <a href="{{ route('portfolio.items', $item->author_name) }}">{{ $item->author_name }}</a></p>
                            <p><strong>Category:</strong> {{ $item->category }}</p>
                            <div class="row">
                                <!-- Left Side -->
                                <div class="col-md-6">
                                    <p><strong>Price:</strong> ${{ $item->price }}</p>
                                    <p><strong>Ratings:</strong> {{ $item->ratings }}</p>
                                    <p><strong>Sales:</strong> {{ $item->sales }}</p>
                                </div>
                                
                                <!-- Right Side -->
                                <div class="col-md-6">
                                    <p><strong>Rating:</strong> {{ $item->rating }} / 5</p>
                                    <p><strong>Total Ratings:</strong> {{ $item->total_ratings }}</p>
                                    <p><strong>Total Sales:</strong> {{ $item->total_sales }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Display Portfolio Items in Table View -->
        <div id="table-view" class="d-none">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Ratings</th>
                        <th>Sales</th>
                        <th>Rating</th>
                        <th>Total Ratings</th>
                        <th>Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($popularItems as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td><img src="{{ $item->image }}" width="50" alt="{{ $item->name }}"></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->author_name }}</td>
                            <td>{{ $item->category }}</td>
                            <td>${{ $item->price }}</td>
                            <td>{{ $item->rating }} / 5</td>
                            <td>{{ $item->sales }}</td>
                            <td>{{ $item->ratings }}</td>
                            <td>{{ $item->total_ratings }}</td>
                            <td>{{ $item->total_sales }}</td>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Flatpickr Date Initialization
        flatpickr('input[name="start_date"], input[name="end_date"]', {
            enableTime: false,
            dateFormat: 'Y-m-d'
        });

        // View Toggle Logic
        document.querySelectorAll('.view-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const view = this.dataset.view;

                if (view === 'grid') {
                    document.getElementById('grid-view').classList.remove('d-none');
                    document.getElementById('table-view').classList.add('d-none');
                } else if (view === 'table') {
                    document.getElementById('table-view').classList.remove('d-none');
                    document.getElementById('grid-view').classList.add('d-none');
                }
            });
        });
    </script>

</body>

</html>
