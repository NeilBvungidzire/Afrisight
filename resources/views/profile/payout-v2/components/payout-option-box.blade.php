<div class="bg-light my-3 py-3 px-4">
    <h2 class="h5">{{ $payoutOption->title }}</h2>
    <p class="small">{{ $payoutOption->intro }}</p>
    <p class="small">{{ __('payout.method.general.usage_requirement', ['min_amount' => number_format($payoutOption->getMinTransferLimit(), 2), 'currency' => 'USD']) }}</p>

    <div class="row">
        <div class="col-12">
            @if( ! $payoutOption->isActive())
                <p class="small font-italic">
                    {{ __('payout.method.general.inactive_reason') }}
                </p>
            @endif
        </div>

        <div class="col-12">
            @if($payoutOption->isActive())
                <a href="{{ $payoutOption->link }}" class="btn btn-primary btn-block">
                    {{ __('payout.method.general.start_cta') }}
                </a>
            @else
                <p class="btn btn-primary btn-block disabled">
                    {{ __('payout.method.general.unavailable_button') }}
                </p>
            @endif
        </div>
    </div>
</div>
