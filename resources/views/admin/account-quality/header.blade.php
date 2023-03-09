<div class="row">
    <div class="col-12 col-md-5">
        <h1>{{ $header ?? 'Account Quality' }}</h1>
    </div>
    <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('admin.account-quality.index') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.account-quality.index') ? ' active' : '' }}">
                    Overview
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.account-quality.email-blacklist.index') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.account-quality.email-blacklist.index') ? ' active' : '' }}">
                    Email blacklist
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.account-quality.bank-account-blacklist.index') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.account-quality.bank-account-blacklist.index') ? ' active' : '' }}">
                    Bank account blacklist
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.account-quality.mobile-number-blacklist.index') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.account-quality.mobile-number-blacklist.index') ? ' active' : '' }}">
                    Mobile number blacklist
                </a>
            </li>
        </ul>
    </div>
    <div class="col-12">
        <hr>
    </div>
</div>
