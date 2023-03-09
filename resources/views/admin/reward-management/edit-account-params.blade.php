@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Edit Account Params'])

    <form action="{{ route('admin.reward_management.member-account.params', ['id' => $id]) }}" method="post" class="form">
        @csrf

        <h1>Payout Params</h1>
        @foreach($payoutOptions as $payoutOption)

            <h2>{{ $payoutOption }} method</h2>

            @php
                $fieldName = "payout[${payoutOption}][minimal_threshold]";
                $value = old($fieldName) ?? $payoutParams[$payoutOption]['minimal_threshold'] ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    Adjusted Minimal Payout Threshold (USD)
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="number" class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}"
                           value="{{ $value }}" step="0.1">

                    <small class="form-text text-muted">
                        Empty value (no value, so 0 is not empty) will mean undo customized account param in this case.
                    </small>
                </div>
            </div>

            @php
                $fieldName = "payout[${payoutOption}][maximum_amount]";
                $value = old($fieldName) ?? $payoutParams[$payoutOption]['maximum_amount'] ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    Adjusted Maximum Payout Allowed (USD)
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="number" class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}"
                           value="{{ $value }}" step="0.1">

                    <small class="form-text text-muted">
                        Empty value (no value, so 0 is not empty) will mean undo customized account param in this case.
                    </small>
                </div>
            </div>

        @endforeach

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.reward_management.member-account') }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </div>
        </div>
    </form>
@endsection
