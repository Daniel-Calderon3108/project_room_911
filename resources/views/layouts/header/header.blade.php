<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- FontAwesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    {{-- JQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Sweet Alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('../css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('../css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('../css/main-panel.css') }}">
    <title>@yield('title')</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <span></span>
            <span></span>
            <span></span>
        </nav>
        <section>
            <h1>@yield('title_header')</h1>
        </section>
    </header>
    <section class="container">
        @yield('content')
    </section>
</body>
</html>