@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">
        {{ __('payout.method.mobile_top_up.long_name') }}
    </h1>

    <div class="bg-light my-3 py-3 px-4">
        <p class="small">intro</p>

        <form action="{{ url()->temporarySignedRoute('profile.payout.mobile_top_up.request', now()->addMinutes(30)) }}"
              class="form" method="post">
            @csrf

            <input type="hidden" name="step" value="{{ $step }}">
            <input type="hidden" name="verified_mobile_number" value="{{ $verifiedMobileNumber }}">

            @php
                $fieldName = 'mobile_number';
                $value = old($fieldName) ?? $mobileNumber;
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    Mobile number
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}"
                           value="{{ $value }}">

                    <small class="form-text text-muted">
                        {{ __('payout.method.mobile_top_up.mobile_number.info') }}
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @if ($step == 2)
                <div class="form-group row">
                    <label for="operator" class="col-sm-5 col-lg-4 col-form-label">
                        Operator name
                    </label>
                    <div class="col-sm-7 col-lg-8">
                        <input type="text" readonly class="form-control-plaintext" id="operator" value="{{ $operatorName }}">
                    </div>
                </div>

                {{-- Fixed --}}
                @php
                    $fieldName = 'fixed_amount';
                    $value = old($fieldName) ?? '';
                @endphp
                <div class="form-group row">
                    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                        Choose an top-up option
                    </label>
                    <div class="col-sm-7 col-lg-8">
                        @foreach ($ranges as $usdAmount => $localAmount)
                            <div class="form-check">
                                <input class="form-check-input{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                       type="radio" name="{{ $fieldName }}" id="{{ $fieldName }}"
                                       value="{{ $usdAmount }}" required>
                                <label class="form-check-label" for="{{ $fieldName }}">
                                    {{ $localAmount }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Range --}}
            @endif

            <div class="row">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <a href="{{ route('profile.payout') }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <button type="submit" class="btn btn-primary btn-block">
                        @if ($step === 3)
                            {{ __('payout.method.general.request_payout_cta') }}
                        @else
                            Next step
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
