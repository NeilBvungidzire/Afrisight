@push('css')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
@endpush

@push('js')
    <script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
@endpush

@component('layouts.partials.base')
    <header>
        @include('navbars.default')
    </header>
    <main>
        {{ $slot }}
    </main>
@endcomponent
