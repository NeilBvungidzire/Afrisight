@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Create Referral</h1>

    @alert

    <form action="{{ route('admin.referral_management.store_referral') }}" method="post" class="form">
        @csrf

        @php
            $fieldName = 'referrer_type';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Referrer Type
            </label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                        class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                    @foreach($referrerTypes as $label => $referrerType)
                        <option value="{{ $referrerType }}" {{ ($value == $referrerType) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'referrer_id';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Referrer ID
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        <hr>

        @php
            $fieldName = 'amount_per_successful_referral';
            $value = old($fieldName) ?? 0;
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Amount (USD)
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}" min="0"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required step="0.01">

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
                @if(isset($type))
                    <input type="hidden" name="{{ $fieldName }}" value="{{ $type }}" />
                    <p class="form-control-plaintext" id="{{ $fieldName }}">{{ $type }}</p>
                @else
                    <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                            class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ ($value == $type) ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @if(isset($type) && $type === \App\Constants\ReferralType::RESPONDENT_RECRUITMENT)
            @php
                $fieldName = 'data[project_code]';
                $key = 'data.project_code';
                $value = old($fieldName) ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    Project Code
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    @if ($errors->has($key))
                        <div class="invalid-feedback">{{ $errors->first($key) }}</div>
                    @endif
                </div>
            </div>
        @endif

        <hr>

        @php
            $fieldName = 'code';
            $value = old($fieldName) ?? $code;
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Referral Code
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                <small id="{{ $fieldName }}" class="form-text text-muted">Only change if necessary!</small>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'static-url';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Referral URL
            </label>

            <div class="col-sm-7 col-lg-8">
                <p class="form-control-plaintext" id="{{ $fieldName }}">{{ $url }}</p>
            </div>
        </div>

        @php
            $fieldName = 'data[public_reference]';
            $key = 'data.public_reference';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Public description/reference
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                @if ($errors->has($key))
                    <div class="invalid-feedback">{{ $errors->first($key) }}</div>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.referral_management.overview') }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Generate</button>
            </div>
        </div>
    </form>
@endsection
