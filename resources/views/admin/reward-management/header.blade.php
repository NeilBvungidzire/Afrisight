<div class="row">
    <div class="col-12 col-md-5">
        <h1>{{ $header ?? 'Reward management' }}</h1>
    </div>
    <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('admin.reward_management.dashboard') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.reward_management.dashboard') ? ' active' : '' }}">Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reward_management.member-account') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.reward_management.member-account') ? ' active' : '' }}">Account</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reward_management.balance') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.reward_management.balance') ? ' active' : '' }}">Balance</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reward_management.granting') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.reward_management.granting') ? ' active' : '' }}">Rewarding</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.reward_management.payout') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.reward_management.payout') ? ' active' : '' }}">Payout</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.cint.transactions') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.cint.transactions') ? ' active' : '' }}">Cint transactions</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.transactions.import') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.transactions.import') ? ' active' : '' }}">Import transactions</a>
            </li>
        </ul>
    </div>
    <div class="col-12">
        <hr>
    </div>
</div>
