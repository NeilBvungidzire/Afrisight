@extends('layouts.website')

@section('title', __('survey_redirects.disqualified.heading'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--rewards'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('survey_redirects.disqualified.heading') }}</h1>
                <p class="lead">{{ __('survey_redirects.disqualified.subheading') }}</p>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <p>{{ __('survey_redirects.disqualified.line_1') }}</p>
            @php($loginUrl = '<a href="' . route('login') . '">' . __('pages.attributes.here') . '</a>')
            <p>{!! __('survey_redirects.disqualified.line_2', ['url' => $loginUrl]) !!}</p>
        </div>
    </section>
@endsection()
