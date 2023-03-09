@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('payout.method.mobile_top_up.short_name') }}</h1>
    <p class="small">{{ __('payout.method.mobile_top_up.intro') }}</p>
    <p class="small">{{ __('payout.method.mobile_top_up.intro_extra') }}</p>

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.method.mobile_top_up.page_3.title') }}</h2>
        @if($operator['denominationType'] === 'FIXED')
            <p class="small">{{ __('payout.method.mobile_top_up.page_3.instructions.fixed') }}</p>
        @elseif($operator['denominationType'] === 'RANGE')
            <p class="small">{{ __('payout.method.mobile_top_up.page_3.instructions.range') }}</p>
        @endif

        <form method="post"
              action="{{ url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.request', now()->addMinutes(30)) }}">
            @csrf

            @php
                $fieldName = 'usd_amount';
                $value = old($fieldName);
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    @if($operator['denominationType'] === 'FIXED')
                        {{ __('payout.method.mobile_top_up.form.plan.label') }}
                    @elseif($operator['denominationType'] === 'RANGE')
                        {{ __('payout.method.mobile_top_up.form.amount.label', ['currency' => 'USD']) }}
                    @endif
                </label>

                <div class="col-sm-7 col-lg-8">
                    @if($operator['denominationType'] === 'FIXED')
                        <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                            <option value="">
                                {{ __('payout.method.mobile_top_up.form.plan.placeholder') }}
                            </option>
                            @foreach($operator['fixedAmounts'] as $plan)
                                <option value="{{ $plan['base_amount'] }}">
                                    {{ number_format($plan['base_amount'], 2) }} USD
                                    ({{ number_format($plan['local_amount'], 2) . ' ' . $operator['fx']['currencyCode'] }}
                                    )
                                </option>
                            @endforeach
                        </select>
                    @elseif($operator['denominationType'] === 'RANGE')
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <input type="number" step="0.01"
                                           class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                           id="{{ $fieldName }}" name="{{ $fieldName }}"
                                           value="{{ $operator['maxAmount'] }}" required
                                           min="{{ $operator['minAmount'] }}"
                                           max="{{ $operator['maxAmount'] }}">

                                    <div class="input-group-append">
                                        <span class="input-group-text">USD</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1 mt-lg-0">
                                <button type="button" class="btn btn-outline-success btn-block"
                                        data-max="{{ number_format($operator['maxAmount'], 2) }}"
                                        onclick="setMax('{{ $fieldName }}', {{ number_format($operator['maxAmount'], 2) }})">
                                    {{ __('payout.method.bank_account.set_max_cta') }}
                                </button>
                            </div>

                            <div class="col-12">
                                <small class="form-text text-muted">
                                    {{ __('payout.method.bank_account.form.amount_to_redeem.info', ['minimum_amount' => number_format($operator['minAmount'], 2), 'maximum_amount' => number_format($operator['maxAmount'], 2)]) }}
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($operator['denominationType'] === 'RANGE')
                @php
                    $fieldName = 'local_amount';
                    $value = old($fieldName) ?? $operator['maxLocalAmount'];
                @endphp
                <div class="form-group row">
                    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                        {{ __('payout.method.bank_account.form.currency_amount.label', ['currency' => $operator['fx']['currencyCode']]) }}
                    </label>

                    <div class="col-sm-7 col-lg-8">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="{{ $fieldName }}" readonly
                                           name="{{ $fieldName }}" value="{{ $value }}">

                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ $operator['fx']['currencyCode'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1 mt-lg-0">
                                <button type="button" class="btn btn-outline-success btn-block"
                                        onclick="calculateLocalAmount({{ $operator['fx']['rate'] }})">
                                    {{ __('payout.method.bank_account.calculate_local_amount_cta', ['local_currency' => $operator['fx']['currencyCode']]) }}
                                </button>
                            </div>

                            <div class="col-12">
                                <small class="form-text text-muted">
                                    {{ __('payout.method.bank_account.form.local_amount_to_redeem.info') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <a href="{{ route('profile.payout-v2.options') }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <a href="{{ url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.get-operator', now()->addMinutes(30)) }}"
                       class="btn btn-outline-primary btn-block">
                        {{ __('payout.method.general.previous_step_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-block" {{ empty($operator) ? 'disabled' : null }}>
                        {{ __('payout.method.general.request_payout_cta') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function setMax(elementId, amount) {
            document.getElementById(elementId).value = amount;
        }

        function calculateLocalAmount(fxRate) {
            var inputField = document.getElementById('usd_amount');
            var localAmountField = document.getElementById('local_amount');
            localAmountField.value = inputField.value * fxRate;
        }
    </script>
@endsection
