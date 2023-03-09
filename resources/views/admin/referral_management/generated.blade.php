@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Generated</h1>

    <p>ID's</p>
    @foreach ($data as $values)
        <pre>{{ $values['number'] }} ---- {{ $values['id'] }}</pre>
    @endforeach

    <p>ID's</p>
    @foreach ($data as $values)
        <pre>{{ $values['id'] }}</pre>
    @endforeach

    <p>ID's</p>
    @foreach ($data as $values)
        <pre>'{{ $values['id'] }}',</pre>
    @endforeach

    <p>URL's</p>
    @foreach ($data as $values)
        <pre>{{ route('inflow', ['project_id' => $values['id']]) }}</pre>
    @endforeach

    <a href="{{ route('admin.referral_management.send') }}" class="btn btn-primary">Send SMS</a>
@endsection
