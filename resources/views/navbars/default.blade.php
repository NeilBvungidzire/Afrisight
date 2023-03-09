<nav class="navbar navbar-expand-xl navbar-light fixed-top" id="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ asset('img/logo.svg') }}" alt="{{ config('app.name') }}"/>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" aria-label="Toggle navigation"
                id="navbar-menu-button">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item{{ (Route::currentRouteName() == 'about') ? ' active' : '' }}">
                    <a href="{{ route('about') }}" class="nav-link">{{ __('pages.about.heading') }}</a>
                </li>
                <li class="nav-item{{ (Route::currentRouteName() == 'rewards') ? ' active' : '' }}">
                    <a href="{{ route('rewards') }}" class="nav-link">{{ __('pages.rewards.heading') }}</a>
                </li>
                <li class="nav-item{{ (Route::currentRouteName() == 'contacts') ? ' active' : '' }}">
                    <a href="{{ route('contacts') }}" class="nav-link">{{ __('pages.contacts.heading') }}</a>
                </li>

                @guest
                    {{-- Quest user --}}
                    <li class="nav-item{{ (Route::currentRouteName() == 'register') ? ' active' : '' }}">
                        <a href="{{ route('register') }}" class="nav-link">{{ __('pages.join-now.heading') }}</a>
                    </li>
                    <li class="nav-item{{ (Route::currentRouteName() == 'login') ? ' active' : '' }}">
                        <a href="{{ route('login') }}" class="nav-link">{{ __('pages.login.heading') }}</a>
                    </li>
                @else
                    {{-- Logged in user --}}
                    @if(in_array(Auth::user()->role, ['SUPER_ADMIN','ADMIN','TRANSLATOR', 'MEMBERS_SUPPORT'], true))
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin</a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link nav-link-user-avatar" id="navbar-user-button"
                           role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="user-avatar">
                                <span class="text-primary">@include('svg.user')</span>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbar-user-button">
                            <a class="dropdown-item{{ (Route::currentRouteName() == 'profile.basic-info.show') ? ' active' : '' }}"
                               href="{{ route('profile.basic-info.show') }}">{{ __('profile.sub_pages.general_info.heading') }}</a>

                            {{-- Profiling --}}
                            @if (Route::has('profiling'))
                                <a class="dropdown-item{{ (Route::currentRouteName() == 'profiling') ? ' active' : '' }}"
                                   href="{{ route('profiling') }}">{{ __('profile.sub_pages.profiling.heading') }}</a>
                            @endif
                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item{{ (Route::currentRouteName() == 'profile.surveys') ? ' active' : '' }}"
                               href="{{ route('profile.surveys') }}">{{ __('profile.sub_pages.survey_opportunities.heading') }}</a>

                            <a class="dropdown-item{{ (Route::currentRouteName() == 'profile.rewards') ? ' active' : '' }}"
                               href="{{ route('profile.rewards') }}">{{ __('profile.sub_pages.rewards.heading') }}</a>

                            <a class="dropdown-item{{ (Route::currentRouteName() == 'profile.payout-v2.options') ? ' active' : '' }}"
                               href="{{ route('profile.payout-v2.options') }}">{{ __('profile.sub_pages.payout.heading') }}</a>

                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item{{ (Route::currentRouteName() == 'profile.security') ? ' active' : '' }}"
                               href="{{ route('profile.security') }}">{{ __('profile.sub_pages.security.heading') }}</a>

                            {{-- Logout --}}
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('pages.logout.heading') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest

                <!-- Language selector -->
                <li class="nav-item">
                    <span class="separator"></span>
                </li>
                @php($currentLocale = app()->getLocale())
                @foreach(LaravelLocalization::getSupportedLocales() as $key => $specs)
                    @continue($currentLocale === $key)
                    <li class="nav-item">
                        <a href="{{ LaravelLocalization::getLocalizedURL($key) }}"
                           class="nav-link">
                            {{ $specs['native'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
