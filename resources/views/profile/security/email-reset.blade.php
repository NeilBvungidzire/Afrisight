@extends('layouts.website')

@section('title', __('profile.sub_pages.security.heading') . ' - ' . $name)

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-3 mb-3">{{ __('profile.sub_pages.email_change.heading') }}</h1>

                    @alert
                    <hr/>

                    <form method="POST" action="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('profile.change-email.change', (60*60)) }}">
                        @csrf

                        @php
                            $fieldName = 'password';
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}" type="password"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}"
                                   placeholder="{{ __('profile.sub_pages.email_change.form.current_password.placeholder') }}">

                            <small class="form-text text-muted">
                                {{ __('profile.sub_pages.email_change.form.current_password.info_text') }}
                            </small>

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'new_email';
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}" type="email"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}"
                                   placeholder="{{ __('profile.sub_pages.email_change.form.new_email.placeholder') }}">

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                {{ __('profile.sub_pages.email_change.form.submit.label') }}
                            </button>
                        </div>

                        <div class="form-group">
                            <a class="btn btn-outline-warning btn-block" href="{{ route('profile.security') }}">
                                {{ __('general.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection