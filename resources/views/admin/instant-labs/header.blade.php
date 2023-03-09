<div class="row">
    <div class="col-12 col-md-5">
        <h1>{{ $header ?? 'Reward management' }}</h1>
    </div>
    <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('admin.instant_labs.dashboard') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.instant_labs.dashboard') ? ' active' : '' }}">Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.instant_labs.import_data.set') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.instant_labs.import_data.set') ? ' active' : '' }}">Import data</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.instant_labs.plan.find') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.instant_labs.plan.find') ? ' active' : '' }}">Plan</a>
            </li>
        </ul>
    </div>
    <div class="col-12">
        <hr>
    </div>
</div>
