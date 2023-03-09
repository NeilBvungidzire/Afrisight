@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . $projectCode . ' - ' . config('app.name'))

@section('content')

    <div class="row">
        <div class="col-12">
            @include('admin.projects.header', ['title' => $title, 'project_code' => $projectCode])
        </div>

        <aside class="col-2 border-right">
            <p class="text-muted">Projects (live)</p>

            @foreach ($partners as $partner)
                @empty($partner['projects'])
                    @continue
                @endempty

                <p class="p-2 mb-1 bg-light text-dark">{{ $partner['name'] }}</p>
                <ul class="nav nav-pills flex-column">
                    @foreach ($partner['projects'] as $projectCode => $project)
                        @if ( ! $project['enabled_for_admin'] ||  ! $project['live'])
                            @continue
                        @endif

                        <li class="nav-item">
                            <a href="{{ route('admin.projects.target_track.index', ['projectCode' => $projectCode]) }}" class="nav-link">
                                {{ $projectCode }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </aside>
        <section class="col-10">
            @yield('inner-content')
        </section>
    </div>

@endsection
