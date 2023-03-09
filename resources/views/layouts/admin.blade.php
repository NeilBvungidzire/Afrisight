<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'AfriSight'))</title>

    <!-- SEO alt language -->
    @foreach(LaravelLocalization::getSupportedLocales() as $key => $specs)
        <link rel="alternate" hreflang="{{ $key }}" href="{{ LaravelLocalization::getLocalizedURL($key) }}" />
    @endforeach

    {{-- Manifest --}}
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png"/>
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png"/>
    <link rel="manifest" href="/manifest.json"/>
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5"/>
    <meta name="msapplication-TileColor" content="#00aba9"/>
    <meta name="theme-color" content="#ffffff"/>

    <link href="{{ mix('css/admin.css') }}" rel="stylesheet" type="text/css">

    @stack('js-head')
</head>
<body>
<main>
    <header>
        @include('navbars.admin')
    </header>
    <main style="margin-top: 56px;">
        <div class="container-fluid py-3">
            @yield('content')
        </div>
    </main>
</main>

<script type="text/javascript" src="{{ mix('js/admin.js') }}"></script>
@stack('js')
</body>
</html>
