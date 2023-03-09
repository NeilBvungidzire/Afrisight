@extends('admin.projects.sample-provider.layout')

@section('inner-content')

    <table class="table">
        <thead>
        <tr>
            <th>Project ID</th>
            <th>Project Codes</th>
            <th>Supplier</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($records as $record)
            <tr>
                <td>{{ $record->project_id }}</td>
                <td>{{ implode(", ", $record->project_codes) }}</td>
                <td>{{ $record->source }}</td>
                <td>
                    <a href="{{ route('admin.sample-provider.dashboard', ['id' => $record->id]) }}"
                       class="btn btn-sm btn-info m-1">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.sample-provider.edit', ['id' => $record->id]) }}"
                       class="btn btn-sm btn-info m-1">
                        Edit
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
