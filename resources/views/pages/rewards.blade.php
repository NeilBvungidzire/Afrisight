@extends('layouts.website')

@section('title', __('pages.rewards.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--rewards'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('pages.rewards.heading') }}</h1>
                <p class="lead">{{ __('pages.rewards.subheading') }}</p>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row">
                @php
                    $rewardList = [
                        [
                            'title' => __('pages.rewards.cash.title'),
                            'icon' => 'svg.dollar-sign',
                            'body' => __('pages.rewards.cash.body'),
                        ],
                        [
                            'title' => __('pages.rewards.paypal.title'),
                            'icon' => 'svg.paypal',
                            'body' => __('pages.rewards.paypal.body'),
                        ],
                        [
                            'title' => __('pages.rewards.airtime.title'),
                            'icon' => 'svg.mobile-alt',
                            'body' => __('pages.rewards.airtime.body'),
                        ],
                        [
                            'title' => __('pages.rewards.shopping-voucher.title'),
                            'icon' => 'svg.money-check-alt',
                            'body' => __('pages.rewards.shopping-voucher.body'),
                        ],
                        [
                            'title' => __('pages.rewards.prize-draws.title'),
                            'icon' => 'svg.dice',
                            'body' => __('pages.rewards.prize-draws.body'),
                        ],
                    ];
                @endphp
                @foreach($rewardList as $reward)
                    <div class="col-12 col-lg-6 py-5">
                        <div class="bullet">
                            <div class="bullet__media">
                                @include($reward['icon'])
                            </div>
                            <div class="bullet__content">
                                <div class="bullet__title">{{ $reward['title'] }}</div>
                                <div class="bullet__body">{{ $reward['body'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @guest
                    <div class="col-12 col-lg-6 offset-lg-3 pt-5">
                        <a href="{{ route('register') }}"
                           class="btn btn-primary btn-block btn-lg">{{ __('pages.rewards.cta_text') }}</a>
                    </div>
                @endguest
            </div>
        </div>
    </section>
@endsection()
