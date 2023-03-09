@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">
        {{ __('payout.method.mobile_money.long_name') }}
    </h1>

    <div class="bg-light my-3 py-3 px-4">
        <p class="small">{{ __('payout.method.mobile_money.page_1_intro', ['amount' => $usdAmount, 'currency' => 'USD']) }}</p>

        <form action="{{ url()->temporarySignedRoute('profile.payout.mobile_money.request', now()->addMinutes(30)) }}"
              class="form" method="post">
            @csrf

            @php
                $fieldName = 'amount';
                $value = old($fieldName) ?? $usdAmount;
            @endphp
            <div class="form-group row">
                <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $fieldName }}">
                    Amount to redeem
                </label>
                <div class="col-sm-7 col-lg-8">
                    <div class="input-group">
                        <input type="number" step="0.1"
                               class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                               id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required
                               min="{{ $minAmount }}" max="{{ $maxAmount }}">

                        <div class="input-group-append">
                            <span class="input-group-text">USD</span>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        {{ __('payout.method.bank_account.form.amount_to_redeem.info', ['minimum_amount' => number_format($minAmount, 2), 'maximum_amount' => number_format($maxAmount, 2)]) }}
                    </small>
                </div>
            </div>

            @php
                $fieldName = 'mobile_number';
                $value = old($fieldName) ?? $mobileNumber;
            @endphp
            <div class="form-group row">
                <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $fieldName }}">
                    Mobile number
                </label>
                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control" id="{{ $fieldName }}" value="{{ $value }}"
                           name="{{ $fieldName }}">

                    <small class="form-text text-muted">
                        We will transfer the money to this mobile number account. Please fill in with country code. For example, +123936438354.
                    </small>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <button type="submit" class="btn btn-primary btn-block">
                        {{ __('payout.method.general.request_payout_cta') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
