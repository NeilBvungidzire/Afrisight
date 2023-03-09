@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('profile.sub_pages.payout.heading') }}</h1>
    <p class="small">{{ __('payout.intro') }}</p>

    @foreach($payoutOptions as $payoutOption)
        @component('profile.payout-v2.components.payout-option-box', ['payoutOption' => $payoutOption])@endcomponent
    @endforeach

    {{-- Payout transactions overview --}}
    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('payout.payout_requests.title') }}</h2>
        <p class="small">{{ __('payout.payout_requests.intro') }}</p>

        @empty($payoutTransactions)
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
                @foreach ($payoutTransactions as $transaction)
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
