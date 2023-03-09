{{-- @todo translate --}}

@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">Transfer as mobile top-up</h1>
    <p class="small">Get paid out as mobile top-up by entering the mobile number, select the mobile operator for the
        given mobile number and, last but not least, select the amount you want to redeem as mobile top-up from your
        balance.</p>

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">Mobile operator</h2>
        <p class="small">Select the mobile operator for phone number {{ $phoneNumber }}.</p>

        <form method="post"
              action="{{ url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.handle-operator', now()->addMinutes(30)) }}">
            @csrf

            @php
                $fieldName = 'operator_id';
                $value = old($fieldName);
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    Operator
                </label>

                <div class="col-sm-7 col-lg-8">
                    @if(empty($foundOperator))
                        <p class="form-control-plaintext">Could not find mobile operator for given phone number!</p>
                        <p class="form-control-plaintext">Please double check the phone number you have set. You can go back to previous step and change the phone number. If still not found, this could be because the mobile operator for this phone number is not yet supported.</p>
                    @else
                        <p class="form-control-plaintext">{{ $foundOperator['name'] }}</p>
                        <p class="form-control-plaintext">Is this the mobile operator for the phone number you entered? If yes, click "Next". Otherwise, click "Previous" and double check the phone number you have entered.</p>
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
                        Previous
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-block" {{ empty($foundOperator) ? 'disabled' : null }}>
                        Next
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
