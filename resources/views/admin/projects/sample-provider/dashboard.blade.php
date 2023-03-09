@extends('admin.projects.sample-provider.layout')

@section('inner-content')

    @foreach($statsByProject as $projectCode => $projectStats)
        <div class="row mb-3">
            <div class="col">
                <h3>{{ $projectCode }}</h3>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Status</th>
                        <th>Counts</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($projectStats as $statusCounts)
                        <tr>
                            <td>{{ $statusCounts->status }}</td>
                            <td>{{ $statusCounts->count }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endsection
