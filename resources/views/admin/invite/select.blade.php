@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Audience selector</h1>

    @alert

    <form action="{{ route('admin.invite.send_sms', ['projectCode' => $projectCode]) }}" method="post">
        @csrf

        <div class="form-group">
            <label for="person_id">Person ID</label>
            <input type="number" class="form-control" id="person_id" placeholder="12345" required name="person_id">
        </div>

        <div class="form-group">
            <label for="package_id">Package ID</label>
            <input type="number" class="form-control" id="package_id" placeholder="1" required name="package_id">
        </div>

        <button type="submit" class="btn btn-primary">Send SMS</button>
    </form>
@endsection
