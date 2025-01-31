<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Laravel App')</title>

    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <!-- Bootstrap CSS (Optional) -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Your custom CSS --> --}}
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/flatpickr.min.css') }}">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">My Laravel App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categories.index') }}">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('popular.items') }}">Popular Items</a>
                    </li>
                    <li
                        class="nav-item dropdown {{ request()->is('reports/*') || request()->routeIs('popular.items.reports') ? 'active' : '' }}">
                        <a class="nav-link dropdown-toggle {{ request()->is('reports/*') ? 'active' : '' }}"
                            href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Reports
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('popular.items.reports') ? 'active' : '' }}"
                                    href="{{ route('popular.items.reports') }}">Reports</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.featured') ? 'active' : '' }}"
                                    href="{{ route('reports.featured') }}">Featured</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.discount-sale') ? 'active' : '' }}"
                                    href="{{ route('reports.discount-sale') }}">Discount Sale</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.best-sellers') ? 'active' : '' }}"
                                    href="{{ route('reports.best-sellers') }}">Best Seller</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.rising-star') ? 'active' : '' }}"
                                    href="{{ route('reports.rising-star') }}">Rising Star</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.newest-item') ? 'active' : '' }}"
                                    href="{{ route('reports.newest-item') }}">Newest Item</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>





    <!-- Flash Messages -->
    <div class="container mt-3">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-4">
        <p>© {{ date('Y') }} My Laravel App. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS (Optional) -->
    <script src="{{ asset('asset/js/bootstrap.bundle.min.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    {{-- <script src="{{ asset('js/app.js') }}"></script> <!-- Your custom JS --> --}}
    <script src="{{ asset('asset/js/flatpickr.js') }}"></script>
    @yield('scripts')
</body>

</html>
