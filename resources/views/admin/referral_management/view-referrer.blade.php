@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>View Referrer</h1>

    <div class="row">
        <div class="col-12">
            <dl class="row">
                <dt class="col-3">ID</dt>
                <dd class="col-9">{{ $referrer->id }}</dd>

                <dt class="col-3">Email</dt>
                <dd class="col-9">{{ $referrer->contacts['email'] ?? null }}</dd>

                <dt class="col-3">Phone Number</dt>
                <dd class="col-9">{{ $referrer->contacts['phone'] ?? null }}</dd>
            </dl>
        </div>

        <div class="col-12">
            <h2>Referral instances</h2>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Referral Code / Link</th>
                    <th>Type</th>
                    <th>Amount / conversion</th>
                    <th>Total referrals</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @isset($referrer->referrals)
                    @foreach ($referrer->referrals as $referral)
                        <tr>
                            <td>
                                <a href="{{ route('inflow', ['projectId' => $referral->code]) }}">{{ $referral->code }}</a>
                            </td>
                            <td>{{ $referral->type ?? null }}</td>
                            <td>USD {{ number_format($referral->amount_per_successful_referral, 2) }}</td>
                            <td>{{ $referral->total_referrals ?? 0 }}</td>
                            <td>
                                <a href="{{ route('admin.referral_management.view_referral', ['id' => $referral->id]) }}"
                                   class="btn btn-sm btn-outline-info">
                                    View
                                </a>
                                <a href="{{ route('admin.referral_management.edit_referral', ['id' => $referral->id]) }}"
                                   class="btn btn-sm btn-outline-info">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endisset
                </tbody>
            </table>
        </div>

        <div class="col-12">
            <hr>

            <a href="{{ route('admin.referral_management.overview_referrer') }}" class="btn btn-outline-info btn-block">Overview</a>
        </div>
    </div>
@endsection
