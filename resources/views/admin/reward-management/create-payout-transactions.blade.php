@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Create transactions'])

    @alert

    <p>Total amount person with ID: {{ $personId }} can withdraw via our payment provider at this moment is <b>{{ number_format($balanceAmount, 2) }} USD</b>.</p>

        <dl class="row">
            <dt class="col-sm-3">Person ID</dt>
            <dd class="col-sm-9">{{ $personId }}</dd>

            <dt class="col-sm-3">Total balance (USD)</dt>
            <dd class="col-sm-9">{{ number_format($balanceAmount, 2) }}</dd>
        </dl>

        <form action="{{ route('admin.reward_management.payout.create') }}" method="post" class="form">
        @csrf

        @php
            $fieldName = 'amount';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Payout amount (USD)
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required step="0.01">

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'new_status';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Status
            </label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                        class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ ($value == $status) ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'type';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Type
            </label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                        class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ ($value == $type) ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.reward_management.payout') }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </div>
        </div>
    </form>
@endsection
