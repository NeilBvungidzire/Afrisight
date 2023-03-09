@extends('layouts.profile')

@section('title', __('profile.sub_pages.payout.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('payout.method.cint_paypal.short_name') }}</h1>

    @if( ! $minimumThresholdReached)
        <div class="alert alert-warning" role="alert">
            <p class="small">{{ __('payout.method.general.minimum_not_reached', ['amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}</p>
        </div>
    @endif

    <div class="bg-light my-3 py-3 px-4">
        <p class="small">{{ __('payout.method.cint_paypal.page_1_intro', ['amount' => number_format($allowedMaxBaseTransferAmount, 2), 'currency' => 'USD']) }}</p>

        <form action="{{ url()->temporarySignedRoute('profile.payout-v2.paypal.request', now()->addMinutes(30)) }}"
              class="form" method="post">
            @csrf

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
@endsection
