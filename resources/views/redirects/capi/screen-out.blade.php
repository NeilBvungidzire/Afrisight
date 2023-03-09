@extends('layouts.website')

@section('title', __('survey_redirects.completed.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--rewards'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">Screen-out</h1>
                <p class="lead">You can close this page</p>
            </div>
        </div>
    @endcomponent()
@endsection
