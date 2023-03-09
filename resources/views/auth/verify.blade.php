@extends('layouts.website')

@section('title', __('pages.verify-email-notification-page.heading') . ' - ' . config('app.name'))

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-3 mb-3">{{ __('pages.verify-email-notification-page.heading') }}</h1>
                    <hr/>

                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('pages.verify-email-notification-page.alert.content') }}
                        </div>
                    @endif

                    <p>{{ __('pages.verify-email-notification-page.body.line-1') }}</p>
                    <p>{{ __('pages.verify-email-notification-page.body.line-2.part-1') }}, <a href="{{ route('verification.resend') }}">{{ __('pages.verify-email-notification-page.body.line-2.part-2') }}</a>.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
