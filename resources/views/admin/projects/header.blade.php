<div class="row">
    <div class="col-12 col-md-5">
        <h1>{{ $title }} <span class="badge badge-info">{{ $projectCode }}</span></h1>
    </div>

    <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('admin.projects.index') }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.projects.index') ? ' active' : '' }}">
                    Projects overview
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.projects.target_track.index', ['projectCode' => $projectCode]) }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.projects.target_track.index') ? ' active' : '' }}">
                    Targets & progress
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.projects.respondents', ['projectCode' => $projectCode]) }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.projects.respondents') ? ' active' : '' }}">
                    Engaged respondents
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.projects.incentive-packages', ['projectCode' => $projectCode]) }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.projects.incentive-packages') ? ' active' : '' }}">
                    Incentive Packages
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.projects.audience_selection', ['projectCode' => $projectCode]) }}"
                   class="nav-link{{ (Route::currentRouteName() == 'admin.projects.audience_selection') ? ' active' : '' }}">
                    Engagement cockpit
                </a>
            </li>

            @can('admin-projects')
                <li class="nav-item">
                    <a href="{{ route('admin.projects.manage_participants.filter', ['projectCode' => $projectCode]) }}"
                       class="nav-link{{ (Route::currentRouteName() == 'admin.projects.manage_participants.select') ? ' active' : '' }}">
                        Manage participants
                    </a>
                </li>
            @endcan
        </ul>
    </div>
    <div class="col-12">
        <hr>
    </div>
</div>
