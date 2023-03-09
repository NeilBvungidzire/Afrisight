@extends('layouts.website')

@section('title', __('privacy_policy.heading') . ' - ' . config('app.name'))

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <h1>{{ __('privacy_policy.heading') }}</h1>
            <p>{{ __('privacy_policy.intro.line_1') }}</p>
            <p>{{ __('privacy_policy.intro.line_2') }}</p>
            <p>{{ __('privacy_policy.intro.line_3') }}</p>
            <p>{{ __('privacy_policy.intro.line_4') }}</p>

            <h2>{{ __('privacy_policy.part_1.heading') }}</h2>
            <p>{{ __('privacy_policy.part_1.line_1') }}</p>
            <p>{{ __('privacy_policy.part_1.line_2') }}</p>
            <p>{{ __('privacy_policy.part_1.line_3') }}</p>

            <h2>{{ __('privacy_policy.part_2.heading') }}</h2>
            <p>{{ __('privacy_policy.part_2.line_1') }}</p>
            <p>{{ __('privacy_policy.part_2.line_2') }}</p>
            <p>{{ __('privacy_policy.part_2.line_3') }}</p>
            <p>{{ __('privacy_policy.part_2.line_4') }}</p>

            <h2>{{ __('privacy_policy.part_3.heading') }}</h2>
            <p>{{ __('privacy_policy.part_3.line_1') }}</p>
            <p>{{ __('privacy_policy.part_3.line_2') }}</p>
            <p>{{ __('privacy_policy.part_3.line_3') }}</p>

            <h2>{{ __('privacy_policy.part_4.heading') }}</h2>
            <p>{{ __('privacy_policy.part_4.line_1') }}</p>

            <h2>{{ __('privacy_policy.part_5.heading') }}</h2>
            <p>{{ __('privacy_policy.part_5.line_1') }}</p>

            <p><strong>{{ __('privacy_policy.part_5.safari.heading') }}</strong></p>
            <p>
                {{ __('privacy_policy.part_5.safari.line_1') }}<br/>
                {{ __('privacy_policy.part_5.safari.line_2') }}<br/>
                {{ __('privacy_policy.part_5.safari.line_3') }}<br/>
                {{ __('privacy_policy.part_5.safari.line_4') }}<br/>
                {{ __('privacy_policy.part_5.safari.line_5') }}<br/>
                {{ __('privacy_policy.part_5.safari.line_6') }}
            </p>

            <p><strong>{{ __('privacy_policy.part_5.firefox.heading') }}</strong></p>
            <p>
                {{ __('privacy_policy.part_5.firefox.line_1') }}<br/>
                {{ __('privacy_policy.part_5.firefox.line_2') }}<br/>
                {{ __('privacy_policy.part_5.firefox.line_3') }}<br/>
                {{ __('privacy_policy.part_5.firefox.line_4') }}
            </p>

            <p><strong>{{ __('privacy_policy.part_5.ie9.heading') }}</strong></p>
            <p>
                {{ __('privacy_policy.part_5.ie9.line_1') }}<br/>
                {{ __('privacy_policy.part_5.ie9.line_2') }}<br/>
                {{ __('privacy_policy.part_5.ie9.line_3') }}<br/>
                {{ __('privacy_policy.part_5.ie9.line_4') }}
            </p>

            <p><strong>{{ __('privacy_policy.part_5.ie8.heading') }}</strong><br/>
            <p>
                {{ __('privacy_policy.part_5.ie8.line_1') }}<br/>
                {{ __('privacy_policy.part_5.ie8.line_2') }}<br/>
                {{ __('privacy_policy.part_5.ie8.line_3') }}
            </p>
            <p>
                {{ __('privacy_policy.part_5.ie8.line_4') }}<br/>
                {{ __('privacy_policy.part_5.ie8.line_5') }}<br/>
                {{ __('privacy_policy.part_5.ie8.line_6') }}
            </p>

            <p><strong>{{ __('privacy_policy.part_5.ie7.heading') }}</strong></p>
            <p>
                {{ __('privacy_policy.part_5.ie7.line_1') }}<br/>
                {{ __('privacy_policy.part_5.ie7.line_2') }}<br/>
                {{ __('privacy_policy.part_5.ie7.line_3') }}<br/>
                {{ __('privacy_policy.part_5.ie7.line_4') }}<br/>
                {{ __('privacy_policy.part_5.ie7.line_5') }}
            </p>

            <p><strong>{{ __('privacy_policy.part_5.iex.heading') }}</strong></p>
            <p>{{ __('privacy_policy.part_5.iex.line_1') }}</p>
            <p>{{ __('privacy_policy.part_5.iex.line_2') }}</p>

            <p><strong>{{ __('privacy_policy.part_5.aol.heading') }}</strong></p>
            <p>
                {{ __('privacy_policy.part_5.aol.line_1') }}<br/>
                {{ __('privacy_policy.part_5.aol.line_2') }}<br/>
                {{ __('privacy_policy.part_5.aol.line_3') }}<br/>
                {{ __('privacy_policy.part_5.aol.line_4') }}<br/>
                {{ __('privacy_policy.part_5.aol.line_5') }}<br/>
                {{ __('privacy_policy.part_5.aol.line_6') }}
            </p>

            <p><strong>{{ __('privacy_policy.part_5.opera.heading') }}</strong></p>
            <p>
                {{ __('privacy_policy.part_5.opera.line_1') }}<br/>
                {{ __('privacy_policy.part_5.opera.line_2') }}<br/>
                {{ __('privacy_policy.part_5.opera.line_3') }}
            </p>

            <h2>{{ __('privacy_policy.part_6.heading') }}</h2>
            <p>{{ __('privacy_policy.part_6.line_1') }}</p>
            <p>{{ __('privacy_policy.part_6.line_2') }}</p>
            <p>{{ __('privacy_policy.part_6.line_3') }}</p>

            <h2>{{ __('privacy_policy.part_7.heading') }}</h2>
            <p>{{ __('privacy_policy.part_7.line_1') }}</p>

        </div>
    </section>
@endsection()
