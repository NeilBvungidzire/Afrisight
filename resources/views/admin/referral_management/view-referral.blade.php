@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>View Referral</h1>

    <div class="row">
        <div class="col-12">
            <dl class="row">
                <dt class="col-3">Referrer</dt>
                <dd class="col-9">{{ $referral->referrerable_type ? $referrerTypes[$referral->referrerable_type] ? $referrerTypes[$referral->referrerable_type] . ' (ID: ' . $referral->referrerable_id . ')' : 'Not set' : 'Not set' }}</dd>

                <dt class="col-3">Code</dt>
                <dd class="col-9">{{ $referral->code }}</dd>

                <dt class="col-3">URL</dt>
                <dd class="col-9">{{ $url }}</dd>

                <dt class="col-3">Amount / Conversion</dt>
                <dd class="col-9">USD {{ number_format($referral->amount_per_successful_referral, 2) }}</dd>

                <dt class="col-3">Total conversions (not per se approved)</dt>
                <dd class="col-9">{{ $referral->total_successful_referrals }}</dd>

                <dt class="col-3">Total referred</dt>
                <dd class="col-9">{{ $referral->total_referrals }}</dd>

                <dt class="col-3">Total earned</dt>
                <dd class="col-9">USD {{ number_format(($referral->total_conversion_amount), 2) }}</dd>

                <dt class="col-3">Conversion rate</dt>
                <dd class="col-9">{{ $referral->conversion_rate }}</dd>

                <dt class="col-3">Project code</dt>
                <dd class="col-9">{{ $referral->data['project_code'] ?? null }}</dd>

                <dt class="col-3">Public description/reference</dt>
                <dd class="col-9">{{ $referral->data['public_reference'] ?? null }}</dd>
            </dl>
        </div>

        <div class="col-12">
            <div class="row justify-content-end">
                <div class="col-md-3">
                    <a href="{{ route('admin.referral_management.overview') }}" class="btn btn-outline-info btn-block">Overview</a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.referral_management.edit_referral', ['id' => $referral->id]) }}" class="btn btn-outline-info btn-block">Edit</a>
                </div>
            </div>
        </div>
    </div>
@endsection
