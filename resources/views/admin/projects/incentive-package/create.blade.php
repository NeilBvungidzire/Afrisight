@extends('admin.projects.layout')

@section('inner-content')
    <div class="text-right">
        <a href="{{ route('admin.projects.incentive-packages', ['project_code' => $projectCode]) }}"
           class="btn btn-outline-info btn-sm">
            All Packages
        </a>
    </div>

    <hr>

    <form action="{{ route('admin.projects.incentive-packages.store', ['project_code' => $projectCode]) }}"
          method="post" class="form">
        @csrf

        @php
            $fieldName = "reference_id";
            $value = old($fieldName) ?? $nextPackageId ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                ID
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" step="1" min="1">

                <small class="form-text text-muted">
                    Only change if you're sure!
                </small>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = "loi";
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                LOI (minutes)
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" step="1" min="1">

                <small class="form-text text-muted">
                    This is the LOI as communicated to the panellist, so not per se the actual.
                </small>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = "usd_amount";
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                USD Amount
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" step="0.01" min="0.01">

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = "local_currency";
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Local Currency
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}">

                <small class="form-text text-muted">
                    <b>Choose one of the following</b>: {{ implode(",", $currencies) }}
                </small>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = "local_amount";
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                Local Amount
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" step="0.01" min="0.01">

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.projects.incentive-packages', ['project_code' => $projectCode]) }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </div>
        </div>
    </form>
@endsection
