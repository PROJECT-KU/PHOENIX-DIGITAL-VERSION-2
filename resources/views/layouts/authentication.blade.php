<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/app.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/iconly.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/custom.css') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body id="app">
    <main id="main" class="container d-flex align-items-center justify-content-center vh-100 mx-auto">
        {{ $slot }}
    </main>

    <script src="{{ asset('mazer/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/app.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/custom.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/DataAkun-delete.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>