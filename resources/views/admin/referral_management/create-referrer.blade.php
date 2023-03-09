@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>@isset($referrer)
            Update
        @else
            Create
        @endisset
        Non-member Referrer
    </h1>

    @alert

    <form action="{{ $action }}" method="post" class="form">
        @csrf
        @isset($referrer)
            @method('put')
        @endisset

        @php
            $fieldName = 'name';
            $value = old($fieldName) ?? $referrer->name ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Name
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'contacts[email]';
            $key = 'contacts.email';
            $value = old($fieldName) ?? $referrer->contacts['email'] ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Email
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}">

                @if ($errors->has($key))
                    <div class="invalid-feedback">{{ $errors->first($key) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'contacts[phone]';
            $key = 'contacts.phone';
            $value = old($fieldName) ?? $referrer->contacts['phone'] ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Phone Number
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}">

                @if ($errors->has($key))
                    <div class="invalid-feedback">{{ $errors->first($key) }}</div>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.referral_management.overview_referrer') }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">
                    @isset($referrer)
                        Update
                    @else
                        Create
                    @endisset
                </button>
            </div>
        </div>
    </form>
@endsection
