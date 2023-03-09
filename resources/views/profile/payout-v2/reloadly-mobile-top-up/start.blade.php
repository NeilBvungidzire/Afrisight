@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('payout.method.mobile_top_up.short_name') }}</h1>
    <p class="small">{{ __('payout.method.mobile_top_up.intro') }}</p>
    <p class="small">{{ __('payout.method.mobile_top_up.intro_extra') }}</p>

    @if( ! $minimumThresholdReached)
        <div class="alert alert-warning" role="alert">
            <p class="small">{{ __('payout.method.general.minimum_not_reached', ['amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}</p>
        </div>
    @endif

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.method.mobile_top_up.page_1.title') }}</h2>
        <p class="small">{{ __('payout.method.mobile_top_up.page_1.instructions') }}</p>

        <form method="post"
              action="{{ url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.handle-phone-number', now()->addMinutes(30)) }}">
            @csrf

            @php
                $fieldName = 'phone_number';
                $value = session()->get('phone_number') ?? $mobileNumber;
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.mobile_top_up.form.phone_number.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}" id="{{ $fieldName }}"
                           name="{{ $fieldName }}" value="{{ $value }}">

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <a href="{{ route('profile.payout-v2.options') }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <button type="submit"
                            class="btn btn-primary btn-block"{{ $minimumThresholdReached ? null : ' disabled' }}>
                        {{ __('payout.method.general.next_step_request_payout_cta') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
