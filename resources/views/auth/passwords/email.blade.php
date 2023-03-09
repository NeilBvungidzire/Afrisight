@extends('layouts.website')

@section('title', __('pages.ask-reset-link.heading') . ' - ' . config('app.name'))

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-3 mb-3">{{ __('pages.ask-reset-link.heading') }}</h1>

                    @if (session('status'))
                        <div class="alert alert-primary" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <hr/>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group">
                            <input id="email" type="email"
                                   class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                   name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="{{ __('pages.ask-reset-link.email.placeholder') }}">

                            <small class="form-text text-muted">
                                {{ __('pages.ask-reset-link.email.info') }}
                            </small>

                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                {{ __('pages.ask-reset-link.submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
