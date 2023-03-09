@extends('layouts.website')

@section('title', (__('referral_management.dashboard.heading') . ' - ' . 'AfriSight'))

@section('content')
    <script type="text/javascript" src="{{ mix('js/utils.js') }}"></script>

    <div class="container py-3">
        <h1>{{ __('referral_management.dashboard.heading') }}</h1>

        <div class="row">
            @foreach($referrals as $referral)
                <div class="col-sm-6 col-lg-4">
                    <div class=" {{ cn(['card', 'text-muted' => ( ! $referral->is_available)]) }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $referral->data['public_reference'] ?? __('referral_management.dashboard.public_reference.default') }}</h5>
                        </div>
                        <ul class="list-group border-0">
                            <li class="list-group-item d-flex justify-content-between align-items-center border-left-0 border-right-0 rounded-0">
                                {{ __('referral_management.dashboard.total_referred.label') }}:
                                <span class="badge badge-primary badge-pill">{{ $referral->total_referrals }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-left-0 border-right-0 rounded-0">
                                {{ __('referral_management.dashboard.total_conversions.label') }}:
                                <span class="badge badge-primary badge-pill">{{ $referral->total_successful_referrals }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-left-0 border-right-0 rounded-0">
                                {{ __('referral_management.dashboard.amount_per_conversion.label') }}:
                                <span class="badge badge-primary badge-pill">USD {{ number_format($referral->amount_per_successful_referral, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-left-0 border-right-0 rounded-0">
                                {{ __('referral_management.dashboard.total_earned.label') }}:
                                <span class="badge badge-primary badge-pill">USD {{ number_format(($referral->total_conversion_amount), 2) }}</span>
                            </li>
                        </ul>
                        <div class="card-body">
                            @if($referral->is_available)
                            <p class="small">{{ __('referral_management.dashboard.whatsapp.cta.intro', ['button_text' => __('referral_management.dashboard.whatsapp.cta.button')]) }}</p>
                            <a href="{{ generateWhatsAppLink(__('referral_management.dashboard.referrer.whatsapp.general_message', ['loi' => $referral->incentive_package['loi'], 'usd_amount' => number_format($referral->incentive_package['usd_amount'], 2), 'link' => $referral->url])) }}" target="_blank" class="btn btn-info btn-block">
                                {{ __('referral_management.dashboard.whatsapp.cta.button') }}
                            </a>
                            <p class="small mt-2">{{ __('referral_management.dashboard.copy_link.cta.intro') }}</p>
                            <span class="btn btn-info btn-block copy" data-clipboard-text="{{ $referral->url }}">
                                {{ __('referral_management.dashboard.copy_link.cta.button') }}
                            </span>
                            @else
                                <p>{{ __('referral_management.dashboard.closed_for_referral') }}</p>
                            @endif
                        </div>
                    </div>
                    <hr class="my-4">
                </div>
            @endforeach
        </div>

        <div class="font-italic">
            <p>{{ __('referral_management.dashboard.info.url') }}</p>
            <p>{{ __('referral_management.dashboard.total_referred.label') }} = {{ __('referral_management.dashboard.info.total_referred') }}</p>
            <p>{{ __('referral_management.dashboard.total_conversions.label') }} = {{ __('referral_management.dashboard.info.total_conversions') }}</p>
            <p>{{ __('referral_management.dashboard.amount_per_conversion.label') }} = {{ __('referral_management.dashboard.info.amount_per_conversion') }}</p>
            <p>{{ __('referral_management.dashboard.total_earned.label') }} = {{ __('referral_management.dashboard.info.total_earned') }}</p>
        </div>
    </div>
    <script>
        new ClipboardJS('.copy');
    </script>
@endsection
