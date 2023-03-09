@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@php
    $requestPayoutCta = __('payout.method.general.request_payout_cta')
@endphp

@section('content')
    <h1 class="h4 py-3">{{ __('payout.method.bank_account.short_name') }}</h1>
    <p class="small">{{ __('payout.method.bank_account.intro') }}</p>

    @if( ! $minimumThresholdReached)
        <div class="alert alert-warning" role="alert">
            <p class="small">{{ __('payout.method.general.minimum_not_reached', ['amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}</p>
        </div>
    @endif

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.method.bank_account.local_calculator.title', ['base_currency' => 'USD', 'local_currency' => $payoutOption->getLocalCurrency()]) }}</h2>
        <p class="small">{{ __('payout.method.bank_account.local_calculator.intro', ['base_currency' => 'USD', 'local_currency' => $payoutOption->getLocalCurrency()]) }}</p>

        <form method="post"
              action="{{ url()->temporarySignedRoute('profile.payout-v2.bank-account.local-amount', now()->addMinutes(30)) }}">
            @csrf

            @php
                $fieldName = 'usd_amount';
                $value = old($fieldName) ?? $allowedMaxBaseTransferAmount;
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}-1" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.bank_account.form.currency_amount.label', ['currency' => 'USD']) }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <div class="row{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                        <div class="col-6">
                            <div class="input-group">
                                <input type="number" step="0.01"
                                       class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                       id="{{ $fieldName }}-1" name="{{ $fieldName }}" value="{{ $value }}" required
                                       min="{{ $payoutOption->getMinTransferLimit() }}"
                                       max="{{ $payoutOption->getMaxTransferLimit() }}"
                                    {{ $minimumThresholdReached ? null : ' readonly' }}>

                                <div class="input-group-append">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-success btn-block"
                                    data-max="{{ number_format($payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount), 2) }}"
                                    onclick="setMax('{{ $fieldName }}-1', {{ number_format($payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount), 2) }})">
                                {{ __('payout.method.bank_account.set_max_cta') }}
                            </button>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        @if($minimumThresholdReached)
                            {{ __('payout.method.bank_account.form.amount_to_redeem.info', ['minimum_amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'maximum_amount' => number_format($payoutOption->checkAllowedAvailableTransferAmount($balanceAmount), 2)]) }}
                        @else
                            {{ __('payout.method.bank_account.form.amount_to_redeem.not_reached_info', ['amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}
                        @endif
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'local_amount';
                $value = old($fieldName) ?? number_format($localAmountAfterFeeCompensation, 2, ',', '.');
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.bank_account.form.currency_amount.label', ['currency' => $payoutOption->getLocalCurrency()]) }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="{{ $fieldName }}" readonly
                                       name="{{ $fieldName }}" value="{{ $value }}">

                                <div class="input-group-append">
                                    <span class="input-group-text">{{ $payoutOption->getLocalCurrency() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        {{ __('payout.method.bank_account.form.local_amount_to_redeem.info') }}
                    </small>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-outline-info btn-block"{{ $minimumThresholdReached ? null : ' disabled' }}>
                    {{ __('payout.method.bank_account.calculate_local_amount_cta', ['local_currency' => $payoutOption->getLocalCurrency()]) }}
                </button>
            </div>
        </form>
    </div>

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.method.bank_account.request_payout.title') }}</h2>

        <form method="post"
              action="{{ url()->temporarySignedRoute('profile.payout-v2.bank-account.request', now()->addMinutes(30)) }}">
            @csrf

            @php
                $fieldName = 'bank_account';
                $value = old($fieldName) ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.bank_account.form.your_bank_account_field.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    @if($bankAccounts->isEmpty())
                        <a href="{{ route('profile.bank_account') }}" class="btn btn-info btn-block">
                            {{ __('payout.method.bank_account.add_bank_account') }}
                        </a>
                    @else
                        <div class="row">
                            <div class="col-12 col-xl-6 mb-3 mb-xl-0">
                                <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                        class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                                    <option value="">
                                        {{ __('payout.method.bank_account.form.your_bank_account_field.placeholder') }}
                                    </option>
                                    @foreach($bankAccounts as $bankAccount)
                                        <option value="{{ encrypt($bankAccount['id']) }}"
                                            {{ ($value == $bankAccount['id']) ? 'selected' : '' }}>
                                            {{ $bankAccount['account_number'] }} ({{ $bankAccount['name'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-xl-6">
                                <a href="{{ route('profile.bank_account') }}" class="btn btn-outline-info btn-block">
                                    {{ __('payout.method.bank_account.manage_bank_account') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <small class="form-text text-muted">
                        {{ __('payout.method.bank_account.form.your_bank_account_field.info') }}
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'usd_amount';
                $value = old($fieldName) ?? $allowedMaxBaseTransferAmount;
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}-2" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.bank_account.form.amount_to_redeem.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <div class="row{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                        <div class="col-6">
                            <div class="input-group">
                                <input type="number" step="0.01"
                                       class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                       id="{{ $fieldName }}-2" name="{{ $fieldName }}" value="{{ $value }}" required
                                       min="{{ $payoutOption->getMinTransferLimit() }}"
                                       max="{{ $payoutOption->getMaxTransferLimit() }}"
                                    {{ $minimumThresholdReached ? null : ' readonly' }}>

                                <div class="input-group-append">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-success btn-block"
                                    data-max="{{ number_format($payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount), 2) }}"
                                    onclick="setMax('{{ $fieldName }}-2', {{ number_format($payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount), 2) }})">
                                {{ __('payout.method.bank_account.set_max_cta') }}
                            </button>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        @if($minimumThresholdReached)
                            {{ __('payout.method.bank_account.form.amount_to_redeem.info', ['minimum_amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'maximum_amount' => number_format($payoutOption->checkAllowedAvailableTransferAmount($balanceAmount), 2)]) }}
                        @else
                            {{ __('payout.method.bank_account.form.amount_to_redeem.not_reached_info', ['amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}
                        @endif
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <p class="small">
                    @if($minimumThresholdReached)
                        {{ __('payout.method.bank_account.form.footnote', ['cta_label' => $requestPayoutCta]) }}
                    @else
                        {{ __('payout.method.bank_account.form.amount_to_redeem.not_reached_info', ['amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}
                    @endif
                </p>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <a href="{{ route('profile.payout-v2.options') }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <button type="submit" class="btn btn-primary btn-block"{{ $minimumThresholdReached ? null : ' disabled' }}>
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
    </script>
@endsection
