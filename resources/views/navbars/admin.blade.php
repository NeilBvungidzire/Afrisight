<nav class="navbar navbar-expand-xl navbar-light fixed-top" id="navbar">
    <div class="container-fluid">
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ asset('img/logo.svg') }}" alt="{{ config('app.name') }}"/>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" aria-label="Toggle navigation"
                id="navbar-menu-button">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="{{ route('admin.projects.index') }}" class="nav-link">
                        Project dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('translation.index') }}" class="nav-link">
                        Translations
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('profiling.index') }}" class="nav-link">
                        Profiling
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.referral_management.overview') }}" class="nav-link">
                        Referral management
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.reward_management.dashboard') }}" class="nav-link">
                        Reward management
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.account-quality.index') }}" class="nav-link">
                        Account quality
                    </a>
                </li>

                @if (Route::has('horizon.index'))
                    <li class="nav-item">
                        <a href="{{ route('horizon.index') }}" class="nav-link">Horizon</a>
                    </li>
                @endif

                @if (Route::has('telescope'))
                    <li class="nav-item">
                        <a href="{{ route('telescope') }}" class="nav-link">Telescope</a>
                    </li>
                @endif

                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link">Website</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
