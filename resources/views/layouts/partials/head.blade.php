<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'AfriSight'))</title>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    @if (config('app.env') === 'production')
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-111507824-1"></script>
        <script>
          window.dataLayer = window.dataLayer || [];

          function gtag () {dataLayer.push(arguments);}

          gtag('js', new Date());

          gtag('config', 'UA-111507824-1');
        </script>
    @endif

    {{-- Manifest --}}
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png"/>
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png"/>
    <link rel="manifest" href="/manifest.json"/>
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5"/>
    <meta name="msapplication-TileColor" content="#00aba9"/>
    <meta name="theme-color" content="#ffffff"/>

    <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
</head>
