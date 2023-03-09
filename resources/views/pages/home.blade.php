@extends('layouts.website')

@section('title', __('pages.home.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--home'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('hero_unit.home.heading') }}</h1>
                <p class="lead">{{ __('hero_unit.home.subheading') }}</p>
            </div>
            @guest
                <div class="col-lg-3">
                    <a href="{{ route('register') }}"
                       class="btn btn-primary btn-block btn-lg">{{ __('hero_unit.home.cta_text') }}</a>
                </div>
            @endguest
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5">
        <div class="container">
            <h2 class="mb-3 text-center">{{ __('pages.home.section_steps.heading') }}</h2>
            <div class="row">
                <div class="col-lg-4 py-3">
                    <div class="bullet">
                        <div class="bullet__media">1</div>
                        <div class="bullet__content">
                            <div class="bullet__title">{{ __('pages.home.section_steps.step_1.heading') }}</div>
                            <div class="bullet__body">
                                {{ __('pages.home.section_steps.step_1.description') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 py-3">
                    <div class="bullet">
                        <div class="bullet__media">2</div>
                        <div class="bullet__content">
                            <div class="bullet__title">{{ __('pages.home.section_steps.step_2.heading') }}</div>
                            <div class="bullet__body">
                                {{ __('pages.home.section_steps.step_2.description') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 py-3">
                    <div class="bullet">
                        <div class="bullet__media">3</div>
                        <div class="bullet__content">
                            <div class="bullet__title">{{ __('pages.home.section_steps.step_3.heading') }}</div>
                            <div class="bullet__body">
                                {{ __('pages.home.section_steps.step_3.description') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-3">{{ __('pages.home.section_help.heading') }}</h2>
            <p>{{ __('pages.home.section_help.line_1') }}:</p>
            <ul>
                <li>{{ __('pages.home.section_help.fields.1') }}</li>
                <li>{{ __('pages.home.section_help.fields.2') }}</li>
                <li>{{ __('pages.home.section_help.fields.3') }}</li>
                <li>{{ __('pages.home.section_help.fields.4') }}</li>
                <li>{{ __('pages.home.section_help.fields.5') }}</li>
                <li>{{ __('pages.home.section_help.fields.6') }}</li>
            </ul>
            <p>{{ __('pages.home.section_help.line_2') }}</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="mb-3">{{ __('pages.home.section_testimonials.heading') }}</h2>
            <div class="row">
                <div class="col-lg-6 py-3 py-lg-0">
                    <blockquote class="blockquote">
                        <p class="mb-0">{{ __('pages.home.section_testimonials.adeshina.testimony') }}</p>
                        <footer class="blockquote-footer">
                            Adeshina <cite>{{ __('countries.ng.label') }}</cite>
                        </footer>
                    </blockquote>
                </div>
                <div class="col-lg-6 py-3 py-lg-0">
                    <blockquote class="blockquote">
                        <p class="mb-0">{{ __('pages.home.section_testimonials.yvonne.testimony') }}</p>
                        <footer class="blockquote-footer">
                            Yvonne <cite>{{ __('countries.tz.label') }}</cite>
                        </footer>
                    </blockquote>
                </div>
                <div class="col-lg-6 py-3 py-lg-0">
                    <blockquote class="blockquote">
                        <p class="mb-0">{{ __('pages.home.section_testimonials.themba.testimony') }}</p>
                        <footer class="blockquote-footer">
                            Themba <cite>{{ __('countries.za.label') }}</cite>
                        </footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </section>
@endsection()
