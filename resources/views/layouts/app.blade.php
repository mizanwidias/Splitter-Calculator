<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Splitter Calculator - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
      <div class="container">
        <a class="navbar-brand" href="{{ url('/splitter') }}">Splitter Calc</a>
        <div class="collapse navbar-collapse">
        </div>
      </div>
    </nav>

    {{-- Konten dinamis halaman --}}
    <div class="container">
        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="text-center mt-5 text-muted small">
        <hr>
        &copy; {{ date('Y') }} Splitter FTTH Tools. All rights reserved.
    </footer>

    {{-- Script --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
