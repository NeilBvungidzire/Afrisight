@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.instant-labs.header', ['header' => 'Check imported data'])

    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $record)
                        <tr>
                            @foreach ($record as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-6">
            <a href="{{ route('admin.instant_labs.import_data.set') }}" class="btn btn-danger btn-block">Cancel import</a>
        </div>
        <div class="col-6">
            <a href="{{ route('admin.instant_labs.import_data.import') }}" class="btn btn-success btn-block">Approve import</a>
        </div>
    </div>
@endsection
