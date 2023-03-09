@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Import transactions'])

    @alert()

    <form action="{{ route('admin.transactions.import') }}" method="post" class="form">
        @csrf

        @php
            $fieldName = 'new_status';
            $value = old($fieldName) ?? $defaultStatus;
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-md-3 col-form-label">
                Status for all transaction
            </label>

            <div class="col-md-9">
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
            $fieldName = 'data';
        @endphp
        <div class="form-group">
            <label for="{{ $fieldName }}" class="col-form-label">
                Copy-paste transaction data from Excel sheet (must contain the following columns on first row: email, amount_paid OR amount_open)
            </label>

            <textarea class="form-control col-12" id="{{ $fieldName }}" name="{{ $fieldName }}" required rows="20"></textarea>

            @if ($errors->has($fieldName))
                <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
            @endif
        </div>

        <div class="form-group row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">Import</button>
            </div>
        </div>
    </form>

    @if ( ! empty($unhandledEmails))
        <hr class="my-5">

        <h2>Data</h2>

        <table class="table">
            <thead>
            <tr>
                <th>Email</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($unhandledEmails as $email)
                <tr>
                    <td>{{ $email }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
