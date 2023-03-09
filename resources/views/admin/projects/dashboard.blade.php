@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-12 col-md-5">
                    <h1>Projects dashboard</h1>
                </div>

                <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="{{ route('admin.sample-provider.index') }}"
                               class="nav-link{{ (Route::currentRouteName() == 'admin.sample-provider.index') ? ' active' : '' }}">
                                Sample Suppliers
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <section class="col-12">
            @alert

            <h2 class="h4">Clients</h2>
            <div class="row mt-3">
                @foreach (array_chunk($partners, ceil(count($partners) / 3), true) as $chunk)
                    <div class="col-12 col-md-6 col-lg-4">
                        <ul class="list-group">
                            @foreach($chunk as $partner)
                                <a href="#{{ $partner['prefix'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span href="#{{ $partner['prefix'] }}" class="text-muted">{{ $partner['name'] }}</span>
                                    <span class="badge badge-primary badge-pill">{{ count($partner['projects']) }}</span>
                                </a>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @foreach ($partners as $partner)
                <div class="card my-5 bg-light border-info" id="{{ $partner['prefix'] }}">
                    <div class="card-header">{{ $partner['name'] }}</div>
                    <div class="card-body">
                        <table class="table table-hover table-borderless table-striped">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Channel status</th>
                                <th></th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($partner['projects'] as $projectCode => $project)
                                <tr>
                                    <td>{{ $projectCode }}</td>
                                    <td>{{ $project['description'] }}</td>
                                    <td>
                                <span class="badge badge-{{ $project['live'] ? 'success' : 'warning' }}">
                                    Email
                                </span>

                                        <span class="badge badge-{{ $project['live'] ? 'success' : 'warning' }}">
                                    SMS
                                </span>

                                        <span
                                            class="badge badge-{{ ($project['live'] && $project['enabled_via_web_app']) ? 'success' : 'warning' }}">
                                    Web App
                                </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.projects.target_track.index', ['projectCode' => $projectCode]) }}"
                                           class="btn btn-sm btn-info m-1">
                                            View project targets & progress
                                        </a>

                                        <a href="{{ route('admin.projects.respondents', ['projectCode' => $projectCode]) }}"
                                           class="btn btn-sm btn-info m-1">
                                            View project respondents
                                        </a>

                                        <a href="{{ route('admin.projects.incentive-packages', ['projectCode' => $projectCode]) }}"
                                           class="btn btn-sm btn-info m-1">
                                            Incentive Packages
                                        </a>

                                        <a href="{{ route('admin.projects.audience_selection', ['projectCode' => $projectCode]) }}"
                                           class="btn btn-sm btn-info m-1">
                                            Engage project audience
                                        </a>

                                        <a href="{{ route('admin.projects.switch_status', ['projectCode' => $projectCode]) }}"
                                           class="btn btn-sm btn-info m-1">
                                            Switch status
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </section>
    </div>
@endsection
