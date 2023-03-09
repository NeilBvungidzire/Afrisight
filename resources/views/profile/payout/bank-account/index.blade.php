@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    @php
        $calcuateLocalAmountCta = __('payout.method.bank_account.calculate_local_amount_cta', ['local_currency' => $localCurrency])
    @endphp

    <h1 class="h4 py-3">{{ $paymentMethod['label'] }}</h1>

    <div class="bg-light my-3 py-3 px-4">
        <p class="small">{{ __('payout.method.bank_account.page_1_intro', ['local_currency' => $localCurrency, 'cta_label' => $calcuateLocalAmountCta]) }}</p>

        <form method="POST" class="form" id="bank-account-form"
              action="{{ url()->temporarySignedRoute('profile.payout.bank_account.request', now()->addMinutes(30)) }}">
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
                    <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                            class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                        <option value="">
                            {{ __('payout.method.bank_account.form.your_bank_account_field.placeholder') }}
                        </option>
                        @foreach($bankAccounts as $bankAccount)
                            <option
                                value="{{ encrypt($bankAccount['id']) }}" {{ ($value == $bankAccount['id']) ? 'selected' : '' }}>
                                {{ $bankAccount['account_number'] }} ({{ $bankAccount['name'] }})
                            </option>
                        @endforeach
                    </select>

                    <small class="form-text text-muted">
                        {{ __('payout.method.bank_account.form.your_bank_account_field.info') }}
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'amount';
                $value = old($fieldName) ?? $usdAmount;
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.method.bank_account.form.amount_to_redeem.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group">
                                <input type="number" step="0.01"
                                       class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required
                                       min="{{ $threshold }}" max="{{ $maximumAmount }}">

                                <div class="input-group-append">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-success btn-block"
                                    data-max="{{ number_format($maximumAmount, 2) }}"
                                    onclick="setMax('{{ $fieldName }}', {{ number_format($maximumAmount, 2) }})">
                                {{ __('payout.method.bank_account.set_max_cta') }}
                            </button>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        {{ __('payout.method.bank_account.form.amount_to_redeem.info', ['minimum_amount' => number_format($threshold, 2), 'maximum_amount' => number_format($maximumAmount, 2)]) }}
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            {{-- Local amount --}}
            <hr>
            <div class="row">
                <div class="col-sm-7 col-lg-8 offset-sm-5 offset-lg-4">
                    <p class="text-muted">
                        {{ __('payout.method.bank_account.local_amount_header') }}
                    </p>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 col-lg-4 col-form-label" for="fee">
                    {{ __('payout.method.bank_account.form.transfer_fee.label') }}
                </label>
                <div class="col-sm-7 col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control" readonly id="fee"
                               value="{{ number_format($transferFee['respondent_part'], 2, ',', '.') }}">

                        <div class="input-group-append">
                            <span class="input-group-text">{{ $localCurrency }}</span>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        <span>
                            {{ __('payout.method.bank_account.form.transfer_fee.info_1', ['local_amount' => number_format($transferFee['total'], 2, ',', '.'), 'local_currency' => $transferFee['currency']]) }}
                        </span>

                        @if ($transferFee['our_part'] > 0)
                            <br>
                            <span>
                                {{ __('payout.method.bank_account.form.transfer_fee.info_2', ['compensation_amount' => $transferFee['our_part'], 'total_fee_amount' => $transferFee['total'], 'local_currency' => $transferFee['currency']]) }}
                            </span>
                        @endif
                        <br>
                        <span>
                            {{ __('payout.method.bank_account.form.transfer_fee.info_3') }}
                        </span>
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 col-lg-4 col-form-label" for="local-amount">
                    {{ __('payout.method.bank_account.form.local_amount_pay_out.label') }}
                </label>
                <div class="col-sm-7 col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control" readonly id="local-amount"
                               value="{{ number_format(($localAmount - $transferFee['our_part']), 2, ',', '.') }}">

                        <div class="input-group-append">
                            <span class="input-group-text">{{ $localCurrency }}</span>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        {{ __('payout.method.bank_account.form.local_amount_pay_out.info') }}
                    </small>
                </div>
            </div>
            <hr>

            @php
                $requestPayoutCta = __('payout.method.general.request_payout_cta');
            @endphp

            <div class="row">
                <div class="col-12">
                    <p class="small">
                        {{ __('payout.method.bank_account.form.footnote', ['cta_label' => $requestPayoutCta]) }}
                    </p>
                </div>

                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <a href="{{ route('profile.payout') }}" class="btn btn-outline-info btn-block">
                        {{ __('payout.method.general.cancel_request_payout_cta') }}
                    </a>
                </div>

                <div class="col-12 col-md-4 mb-2 mb-md-0">
                    <input type="hidden" name="calculate" value="0" id="calculate">
                    <button type="submit" class="btn btn-info btn-block" id="calculate-handler">
                        {{ $calcuateLocalAmountCta }}
                    </button>
                </div>

                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-block">{{ $requestPayoutCta }}</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('calculate-handler').onclick = function (event) {
            event.preventDefault();

            document.getElementById('calculate').value = 1;
            document.getElementById('bank-account-form').submit();
        };

        function setMax (elementId, amount) {
            console.log(elementId, amount);
            document.getElementById(elementId).value = amount;
        }
    </script>
@endsection
