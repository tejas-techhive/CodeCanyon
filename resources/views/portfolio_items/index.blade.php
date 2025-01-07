<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Page</title>
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/flatpickr.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .nav-tabs .nav-link {
            color: #333;
        }

        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #007bff;
        }

        .card-title {
            font-size: 1rem;
            font-weight: bold;
        }

        .card-text {
            font-size: 0.875rem;
        }

        footer p {
            margin: 0;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <section class="bg-light p-4 border-bottom">
        <a href="{{ url('/') }}" class="btn btn-secondary mb-3">Back</a>
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="https://via.placeholder.com/80" alt="Logo" class="rounded-circle me-3">
                <div>
                    <h2 class="mb-0">{{ $latest->author_name ?? $author_name }} - Portfolio</h2>
                </div>
            </div>
            <div>
                <h6>Author Rating</h6>
                <p>@php
                    $rating = $latest->rating ?? 0;
                    $fullStars = floor($rating);
                    $halfStars = $rating - $fullStars >= 0.5 ? 1 : 0;
                    $emptyStars = 5 - ($fullStars + $halfStars);
                @endphp
                    <span>
                        @for ($i = 0; $i < $fullStars; $i++)
                            ⭐
                        @endfor
                        @for ($i = 0; $i < $halfStars; $i++)
                            ⭐️
                        @endfor
                        @for ($i = 0; $i < $emptyStars; $i++)
                            ☆
                        @endfor
                    </span> ({{ $latest->total_ratings ?? '' }} ratings)
                </p>
                <h6>Sales</h6>
                <p><strong>{{ $latest->total_sales ?? '' }}</strong></p>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <!-- Sorting Dropdown -->
                    <div class="d-flex justify-content-between mb-3">
                        <h5>Sales</h5>
                        <div><label for="select_date">Select Date: </label><input type="text" name="select_date"
                                id="select_date"></div>
                        <select class="form-select w-auto" id="sort_by" onchange="sortItems()">
                            <option value="">Sort By: Default</option>
                            <option value="sales" {{ request('sort_by') == 'sales' ? 'selected' : '' }}>Sort By: Sales
                            </option>
                            <option value="ratings" {{ request('sort_by') == 'ratings' ? 'selected' : '' }}>Sort By:
                                Ratings</option>
                        </select>
                    </div>

                    <!-- Product Cards -->
                    @foreach ($popularItems as $item)
                        <div class="card mb-3 shadow-sm">
                            <div class="row g-0">
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <img src="{{ $item->image }}" class="img-fluid rounded-start"
                                        alt="{{ $item->name }}">
                                </div>
                                <div class="col-md-10">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->name }}</h5>
                                        <p class="card-text"><small class="text-muted">Software Version:
                                                {{ $item->category }}</small></p>
                                        <div class="d-flex justify-content-between">
                                            <span>${{ $item->price }}</span>
                                            {{ $item->ratings }} ratings | {{ $item->sales }} Sales
                                            </span>
                                        </div>
                                        <b>{{ $item->created_at->format('Y-m-d') }}</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    {{ $popularItems->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light text-center p-3 mt-4">
        <p>&copy; 2025. | All Rights Reserved.</p>
    </footer>
    <script src="{{ asset('asset/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/flatpickr.js') }}"></script>
</body>
<script>
    function sortItems() {
        let sortBy = document.getElementById('sort_by').value;
        let url = new URL(window.location.href);

        // Update query string
        if (sortBy) {
            url.searchParams.set('sort_by', sortBy);
        } else {
            url.searchParams.delete('sort_by');
        }

        window.location.href = url.toString();
    }

    flatpickr('#select_date', {
        enableTime: false,
        dateFormat: 'Y-m-d',
        defaultDate: "{{ request('selected_date') }}",
        onChange: function(selectedDates, dateStr, instance) {
            let url = new URL(window.location.href);
            url.searchParams.set('selected_date', dateStr);
            window.location.href = url.toString();
        }
    });
</script>
</html>
