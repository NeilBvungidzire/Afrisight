@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('payout.method.mobile_top_up.short_name') }}</h1>
    <p class="small">{{ __('payout.method.mobile_top_up.intro') }}</p>
    <p class="small">{{ __('payout.method.mobile_top_up.intro_extra') }}</p>

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.method.mobile_top_up.page_2.title') }}</h2>
        <p class="small">{{ __('payout.method.mobile_top_up.page_2.instructions', ['phone_number' => $phoneNumber]) }}</p>

        <form method="post"
              action="{{ url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.handle-operator', now()->addMinutes(30)) }}">
            @csrf

            @php
                $fieldName = 'operator_id';
                $value = old($fieldName);
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.mobile_top_up.form.operator.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    @if(empty($foundOperator))
                        <p class="form-control-plaintext">{{ __('payout.method.mobile_top_up.operator_not_found.message') }}</p>
                        <p class="form-control-plaintext">{{ __('payout.method.mobile_top_up.operator_not_found.instructions') }}</p>
                    @else
                        <p class="form-control-plaintext">{{ $foundOperator['name'] }}</p>
                        <p class="form-control-plaintext">{{ __('payout.method.mobile_top_up.operator_found.instructions') }}</p>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <a href="{{ route('profile.payout-v2.options') }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <a href="{{ url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30)) }}"
                       class="btn btn-outline-primary btn-block">
                        {{ __('payout.method.general.previous_step_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-block" {{ empty($foundOperator) ? 'disabled' : null }}>
                        {{ __('payout.method.general.next_step_request_payout_cta') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
