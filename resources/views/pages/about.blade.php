@extends('layouts.website')

@section('title', __('pages.about.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--about'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('pages.about.heading') }}</h1>
                <p class="lead">{{ __('pages.about.subheading') }}</p>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <p>{{ __('pages.about.content.line_1') }}</p>
            <p>{{ __('pages.about.content.line_2') }}</p>
            <p>{{ __('pages.about.content.line_3') }}</p>
            <ul>
                <li>{{ __('pages.about.content.line_3_1') }}</li>
                <li>{{ __('pages.about.content.line_3_2') }}</li>
                <li>{{ __('pages.about.content.line_3_3') }}</li>
            </ul>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="mb-3 text-center">{{ __('pages.about.mission.heading') }}</h2>
            <div class="row">
                <div class="col-lg-4 py-3 py-lg-0">
                    <p>
                        <strong>{{ __('pages.about.mission.block_1.heading') }}</strong>
                    </p>
                    <p>{{ __('pages.about.mission.block_1.line_1') }}</p>
                    <p>{{ __('pages.about.mission.block_1.line_2') }}</p>
                </div>
                <div class="col-lg-4 py-3 py-lg-0">
                    <p>
                        <strong>{{ __('pages.about.mission.block_2.heading') }}</strong>
                    </p>
                    <p>{{ __('pages.about.mission.block_2.line_1') }}</p>
                    <p>{{ __('pages.about.mission.block_2.line_2') }}</p>
                    <p>{{ __('pages.about.mission.block_2.line_3') }}</p>
                </div>
                <div class="col-lg-4 py-3 py-lg-0">
                    <p>
                        <strong>{{ __('pages.about.mission.block_3.heading') }}</strong>
                    </p>
                    <p>{{ __('pages.about.mission.block_3.line_1') }}</p>
                    <p>{{ __('pages.about.mission.block_3.line_2') }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection()
