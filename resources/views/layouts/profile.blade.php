@component('layouts.partials.portal')
    <div class="py-3">
        <div class="container">
            <div class="row">

                {{-- Email not verified notification --}}
                @if (auth()->user()->email_verified_at === null)
                    <div class="col-12">
                        @php($verificationUrl = '<a href="' . route('verification.resend') . '">' . ucfirst(__('pages.attributes.click_here')) . '</a>')
                        @component('alerts.default', [
                            'type' => 'warning',
                            'heading' => __('auth.verify-email-notification-title'),
                            'body' => __('auth.verify-email-notification-body', ['url' => $verificationUrl]),
                        ])@endcomponent
                    </div>
                @endif

                {{-- Basic member info (gender, country, etc.) not complete. For instance when logged in via Social Media --}}
                @if ( ! $minimalProfileDataIsAvailable)
                    <div class="col-12">
                        @php($editBasicUrl = '<a href="' . route('profile.basic-info.edit') . '">' . __('pages.attributes.click_here') . '</a>')
                        @component('alerts.default', [
                            'type' => 'warning',
                            'heading' => __('auth.complete-user-info-title'),
                            'body' => __('auth.complete-user-info-body', ['url' => $editBasicUrl])
                        ])@endcomponent
                    </div>
                @endif

                {{-- Request mobile number --}}
                @if ( ! $mobileNumberSet)
                    <div class="col-12">
                        @php($editBasicUrl = '<a href="' . route('profile.basic-info.edit') . '">' . __('pages.attributes.click_here') . '</a>')
                        @component('alerts.default', [
                            'type' => 'warning',
                            'heading' => __('auth.add-mobile-number-title'),
                            'body' => __('auth.add-mobile-number-body', ['url' => $editBasicUrl])
                        ])@endcomponent
                    </div>
                @endif

                @if (session('status'))
                    <div class="col-12">
                        @component('alerts.default', [
                            'type' => 'info',
                            'body' => session('status')
                        ])@endcomponent
                    </div>
                @endif

                @hasSection('profile.notification')
                    <div class="col-md-12">@yield('profile.notification')</div>
                @endif

                {{-- @todo Build check to see if alert are available. Only then render the div wrapper. Now it will always render, so can also render empty wrapper. --}}
                <div class="col-12">@alert</div>

                {{-- Profile header --}}
                <div class="col-md-12">
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="row">
                            <div class="col-md-3">{{ $name }}</div>
                            <div class="offset-md-6 col-md-3 text-md-right">
                                {{ __('profile.money-balance', ['amount' => number_format($totalCalculatedRewardBalance, 2)]) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="nav flex-column">
                        <a class="nav-link{{ (Route::currentRouteName() == 'profile.basic-info.show') ? ' active' : '' }}"
                           href="{{ route('profile.basic-info.show') }}">{{ __('profile.sub_pages.general_info.heading') }}</a>

                        {{-- Profiling --}}
                        @if (Route::has('profiling'))
                            <a class="nav-link{{ (Route::currentRouteName() == 'profiling') ? ' active' : '' }}"
                               href="{{ route('profiling') }}">{{ __('profile.sub_pages.profiling.heading') }}</a>
                        @endif

                        <a class="nav-link{{ (Route::currentRouteName() == 'profile.surveys') ? ' active' : '' }}"
                           href="{{ route('profile.surveys') }}">{{ __('profile.sub_pages.survey_opportunities.heading') }}</a>

                        <a class="nav-link{{ (Route::currentRouteName() == 'profile.rewards') ? ' active' : '' }}"
                           href="{{ route('profile.rewards') }}">{{ __('profile.sub_pages.rewards.heading') }}</a>

                        <a class="nav-link{{ (Route::currentRouteName() == 'profile.payout-v2.options') ? ' active' : '' }}"
                           href="{{ route('profile.payout-v2.options') }}">{{ __('profile.sub_pages.payout.heading') }}</a>

                        <a class="nav-link{{ (Route::currentRouteName() == 'profile.security') ? ' active' : '' }}"
                           href="{{ route('profile.security') }}">{{ __('profile.sub_pages.security.heading') }}</a>
                    </div>
                    <hr class="d-md-none">
                </div>
                <div class="col-md-9">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
@endcomponent
