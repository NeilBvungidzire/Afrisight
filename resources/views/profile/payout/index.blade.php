@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('profile.sub_pages.payout.heading') }}</h1>
    <p class="small">{{ __('payout.intro') }}</p>

    @if (empty($availablePaymentMethods) && empty($cintPanelPaymentMethods))
        <div class="bg-light my-3 py-3 px-4">
            <h2 class="h5">{{ __('payout.method.alternative.short_name') }}</h2>
            <p class="small">{{ __('payout.method.alternative.intro', ['amount' => number_format(5, 2), 'currency' => 'USD']) }}</p>
            <p class="small">{{ __('payout.method.alternative.additional_note.1') }}</p>
        </div>
    @endif

    {{-- AfriSight payout methods --}}
    @foreach($availablePaymentMethods as $method => $availablePaymentMethod)

        {{-- Bank account method --}}
        @if ($method === 'BANK_ACCOUNT')
            <div class="bg-light my-3 py-3 px-4">
                <h2 class="h5">{{ $availablePaymentMethod['name'] }}</h2>
                <p class="small">{{ __('payout.method.bank_account.intro') }}</p>

                @if ($hasNoBankAccounts)
                    <a href="{{ route('profile.bank_account') }}" class="btn btn-info btn-block">
                        {{ __('payout.method.bank_account.add_bank_account') }}
                    </a>
                @elseif ($rewardAccount->getCalculatedRewardBalance($rewardAccountType) < $availablePaymentMethod['minimal_threshold'])
                    <p class="small">{{ __('payout.method.general.minimum_not_reached', ['amount' => number_format($availablePaymentMethod['minimal_threshold'], 2), 'currency' => 'USD']) }}</p>

                    <a href="{{ route('profile.bank_account') }}" class="btn btn-info btn-block">
                        {{ __('payout.method.bank_account.manage_bank_account') }}
                    </a>
                @endif
                <div class="row">
                    @if ( ! $hasNoBankAccounts && $availablePaymentMethod['maximum_payout_amount'] >= $availablePaymentMethod['minimal_threshold'])
                        <div class="col-12">
                            <p class="small">
                                {{ __('payout.method.general.option_available', ['minimum_amount' => number_format($availablePaymentMethod['minimal_threshold'], 2), 'maximum_amount' => number_format($availablePaymentMethod['maximum_payout_amount'], 2), 'currency' => 'USD']) }}
                            </p>
                        </div>

                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <a href="{{ route('profile.bank_account') }}" class="btn btn-outline-info btn-block">
                                {{ __('payout.method.bank_account.manage_bank_account') }}
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ url()->temporarySignedRoute('profile.payout.bank_account.start', now()->addMinutes(30)) }}"
                               class="btn btn-primary btn-block">
                                {{ __('payout.method.general.start_cta') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Mobile money method --}}
        @if ($method === 'MOBILE_MONEY')
            <div class="bg-light my-3 py-3 px-4">
                <h2 class="h5">{{ $availablePaymentMethod['name'] }}</h2>
                <p class="small">{{ __('payout.method.mobile_money.intro') }}</p>

                <div class="row">
                    @if ($availablePaymentMethod['maximum_payout_amount'] >= $availablePaymentMethod['minimal_threshold'])
                        <div class="col-12">
                            <p class="small">
                                {{ __('payout.method.general.option_available', ['minimum_amount' => number_format($availablePaymentMethod['minimal_threshold'], 2), 'maximum_amount' => number_format($availablePaymentMethod['maximum_payout_amount'], 2), 'currency' => 'USD']) }}
                            </p>
                        </div>

                        <div class="col-12">
                            <a href="{{ url()->temporarySignedRoute('profile.payout.mobile_money.start', now()->addMinutes(30)) }}"
                               class="btn btn-primary btn-block">
                                {{ __('payout.method.general.start_cta') }}
                            </a>
                        </div>
                    @else
                        <div class="col-12">
                            <p class="small">
                                {{ __('payout.method.general.minimum_not_reached', ['amount' => number_format($availablePaymentMethod['minimal_threshold'], 2), 'currency' => 'USD']) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endforeach

    {{-- Cint payout methods --}}
    @if ( ! $combineRewardAccounts)
        @foreach($cintPanelPaymentMethods as $panelPaymentMethodKey => $panelPaymentMethod)
            <div class="bg-light my-3 py-3 px-4">
                <h2 class="h5">{{ $panelPaymentMethod['name'] }}</h2>
                <p class="small">{{ __('payout.method.cint_paypal.intro') }}</p>

                <div class="row">
                    @if ($rewardAccount->getCalculatedRewardBalance('cint') >= $panelPaymentMethod['threshold_money'])
                        <div class="col-12">
                            <p class="small">
                                {{ __('payout.method.general.option_available', ['minimum_amount' => number_format($panelPaymentMethod['threshold_money'], 2), 'maximum_amount' => number_format($rewardAccount->getCalculatedRewardBalance('cint'), 2), 'currency' => 'USD']) }}
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <a href="{{ url()->temporarySignedRoute('profile.payout.cint-paypal.start', now()->addMinutes(30)) }}"
                               class="btn btn-primary btn-block">
                                {{ __('payout.method.general.start_cta') }}
                            </a>

                        </div>
                    @else
                        <div class="col-12">
                            <p class="small">
                                {{ __('payout.method.general.minimum_not_reached', ['amount' => number_format($panelPaymentMethod['threshold_money'], 2), 'currency' => 'USD']) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.payout_requests.title') }}</h2>
        <p class="small">{{ __('payout.payout_requests.intro') }}</p>

        @empty($transactions)
            {{ __('payout.payout_requests.empty_list') }}
        @else
            <table class="table table-sm mt-4">
                <thead>
                <tr>
                    <th>{{ __('payout.payout_requests.list.date.label') }}</th>
                    <th>{{ __('payout.payout_requests.list.method.label') }}</th>
                    <th>{{ __('payout.payout_requests.list.amount.label', ['currency' => 'USD']) }}</th>
                    <th>{{ __('payout.payout_requests.list.status.label') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction['date'] }}</td>
                        <td>{{ $transaction['method'] }}</td>
                        <td>{{ $transaction['amount'] }}</td>
                        <td>{{ $transaction['status'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endempty
    </div>
@endsection
