@extends('layouts.admin')

@section('title', 'Admin' . ' - Sample Provider - ' . config('app.name'))

@section('content')

    <div class="row">
        <div class="col-12">
            @include('admin.projects.sample-provider.header', ['title' => $title])
        </div>

        <section class="col-12">
            @yield('inner-content')
        </section>
    </div>

@endsection
