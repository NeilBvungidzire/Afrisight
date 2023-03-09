@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Transactions <span class="badge badge-info">Referral Code: {{ $referral->code }}</span></h1>
    <p>Can only be a AfriSight Member.</p>

    @alert

    <h2>Already existing transactions</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Person ID</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Respondent ID</th>
            <th>Project Code</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($existingTransactions as $existingTransaction)
            <tr>
                <td>{{ $existingTransaction->person_id }}</td>
                <td>{{ number_format($existingTransaction->amount, 2) }} USD</td>
                <td>{{ $existingTransaction->status }}</td>
                <td>{{ $existingTransaction->meta_data['respondent_id'] ?? null }}</td>
                <td>{{ $existingTransaction->meta_data['project_code'] ?? null }}</td>
                <td>
                    <a href="{{ route('admin.reward_management.granting.edit', ['id' => $existingTransaction->id]) }}"
                       class="btn btn-outline-info btn-sm">Edit</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr>

    <h2>Successful referred respondents</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Respondent ID</th>
            <th>Project Code</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($referredRespondents as $referredRespondent)
            <tr>
                <td>{{ $referredRespondent->id }}</td>
                <td>{{ $referredRespondent->project_code }}</td>
                <td>
                    <a href="{{ route('admin.referral_management.create_referral_transactions', ['id' => $referral->id, 'respondent_id' => $referredRespondent->id]) }}"
                       class="btn btn-outline-info btn-sm">Create referral rewarding transaction</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr>

    <div class="row justify-content-end">
        <div class="col-md-3">
            <a href="{{ route('admin.referral_management.overview') }}"
               class="btn btn-outline-info btn-block">Overview</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.referral_management.edit_referral', ['id' => $referral->id]) }}"
               class="btn btn-outline-info btn-block">Edit</a>
        </div>
    </div>
@endsection
