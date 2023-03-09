@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.account-quality.header', ['header' => 'Account Quality'])

    <div class="row">
        <div class="col-12">
            <h2 class="h5">Found persons</h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Person ID</th>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection
