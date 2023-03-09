@extends('layouts.website')

@section('title', __('survey_redirects.closed.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--rewards'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('survey_redirects.closed.heading') }}</h1>
                <p class="lead">{{ __('survey_redirects.closed.subheading') }}</p>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <p>{{ __('survey_redirects.closed.line_1') }}</p>
        </div>
    </section>
@endsection()
