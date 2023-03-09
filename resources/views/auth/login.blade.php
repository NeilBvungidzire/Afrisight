@extends('layouts.website')

@section('title', __('pages.login.heading') . ' - ' . config('app.name'))

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-3 mb-3">{{ __('pages.login.heading') }}</h1>

                    @if (session('status'))
                        <div class="alert alert-info" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <hr/>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        @if ((config('services.facebook.enabled') && ! $isOperaMini) || config('services.google.enabled'))
                            <div class="form-group">

                                {{-- Facebook --}}
                                @if (config('services.facebook.enabled') && ! $isOperaMini)
                                    <a class="btn btn-block btn-lg btn-facebook" href="{{ route('facebook.login') }}">
                                        <span class="btn__icon">@include('svg.facebook-f')</span>
                                        <span>{{ __('pages.login.social-media.facebook-cta') }}</span>
                                    </a>
                                @endif

                                {{-- Google --}}
                                @if (config('services.google.enabled'))
                                    <a class="btn btn-block btn-lg btn-google" href="{{ route('google.login') }}">
                                        <span class="btn__icon">@include('svg.google')</span>
                                        <span>{{ __('pages.login.social-media.google-cta') }}</span>
                                    </a>
                                @endif
                                <hr/>
                            </div>
                        @endif

                        <div class="form-group">
                            <input id="email" type="email"
                                   class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                   name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="{{ __('model/person.email.label') }}">

                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group form-group-password">
                            <input id="password" type="password"
                                   class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                   name="password" required autocomplete="current-password"
                                   placeholder="{{ __('model/person.password.label') }}">

                            <button id="toggle-password" class="btn btn-sm" type="button"
                                    data-text-show="{{ __('general.password-toggle.show') }}"
                                    data-text-hide="{{ __('general.password-toggle.hide') }}">
                                {{ __('general.password-toggle.show') }}
                            </button>

                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                {{ __('pages.login.submit-button') }}
                            </button>
                        </div>

                        <div class="form-group">
                            {{ __('pages.login.forgot-password.part-1') }}
                            <a href="{{ route('password.request') }}">
                                {{ __('pages.login.forgot-password.part-2') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
