@extends('layouts.website')

@section('title', __('survey_redirects.completed.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--rewards'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('survey_redirects.completed.heading') }}</h1>
                <p class="lead">{{ __('survey_redirects.completed.subheading') }}</p>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <p>{{ __('survey_redirects.completed.line_1') }}</p>
            <p>
                @if( ! isset($noRewardMentioning) || ! $noRewardMentioning)
                    {{ __('survey_redirects.completed.line_2') }}
                @endif
                {{ __('survey_redirects.completed.line_3') }}
            </p>
        </div>
    </section>
@endsection()
