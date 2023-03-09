<div class="row">
    <div class="col-12 col-md-5">
        <h1>{{ $title }}</h1>
    </div>

    <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('admin.sample-provider.index') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.sample-provider.index') ? ' active' : '' }}">
                    Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.sample-provider.create') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.sample-provider.create') ? ' active' : '' }}">
                    Create
                </a>
            </li>
        </ul>
    </div>
    <div class="col-12">
        <hr>
    </div>
</div>
