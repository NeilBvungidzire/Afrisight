@extends('admin.projects.layout')

@section('inner-content')
    <div class="text-right">
        <a href="{{ route('admin.projects.incentive-packages.create', ['project_code' => $projectCode]) }}"
           class="btn btn-outline-info btn-sm">
            Create
        </a>
    </div>

    <hr>
    <table class="table table-sm table-bordered table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>LOI</th>
            <th>USD amount</th>
            <th>Local amount</th>
            <th>Allocated</th>
            <th>Allocate</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($packages as $package)
            <tr>
                <td>{{ $package->reference_id }}</td>
                <td>{{ $package->loi }} minutes</td>
                <td>USD {{ number_format($package->usd_amount, 2) }}</td>
                <td>{{ $package->local_currency }} {{ number_format($package->local_amount, 2) }}</td>
                <td>
                    @foreach($channelInfo as $label => $identifier)
                        @if($package->reference_id == $identifier)
                            <span class="badge badge-info">{{ $label }}</span>
                        @endif
                    @endforeach
                </td>
                <td>
                    @foreach($channelMapping as $label => $identifier)
                        <a href="{{ route('admin.projects.incentive-packages.allocate', ['projectCode' => $projectCode, 'channel' => $label, 'id' => $package->reference_id]) }}"
                           class="btn btn-sm btn-warning m-1">
                            {{ $label }}
                        </a>
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
