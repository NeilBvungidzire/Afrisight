@extends('layouts.website')

@section('title', (($hasPassword) ? __('pages.password-management.heading.change') : __('pages.password-management.heading.set')) . ' - ' . config('app.name'))

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-3 mb-3">{{ ($hasPassword) ? __('pages.password-management.heading.change') : __('pages.password-management.heading.set') }}</h1>

                    @if (session('status'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <hr/>

                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('put')

                        @if ( ! $hasPassword)
                            <div class="form-group">
                                <p>{{ __('auth.password-not-set-yet') }}</p>
                            </div>
                        @endif

                        @php
                            $fieldName = 'old_password';
                        @endphp
                        <div class="form-group{{ $hasPassword ? '' : ' d-none' }}">
                            <input id="{{ $fieldName }}"
                                   type="{{ $hasPassword ? 'password' : 'hidden' }}"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}" required
                                   placeholder="{{ __('pages.password-management.field.current-password.placeholder') }}">

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'password';
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}" type="password"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}" required
                                   placeholder="{{ ($hasPassword) ? __('pages.password-management.field.new-password.placeholder.new') : __('pages.password-management.field.new-password.placeholder.fresh') }}">

                            <small class="form-text text-muted">
                                {{ __('pages.password-management.field.new-password.info') }}
                            </small>

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'password_confirmation';
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}" type="password"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}" required
                                   placeholder="{{ __('pages.password-management.field.password-confirmation.placeholder') }}">

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                {{ __('pages.password-management.submit') }}
                            </button>
                        </div>

                        <div class="form-group">
                            <a class="btn btn-outline-warning btn-block" href="{{ route('profile.security') }}">
                                {{ __('pages.password-management.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
