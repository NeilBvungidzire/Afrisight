@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.instant-labs.header', ['header' => 'Import data'])

    @alert()

    <form action="{{ route('admin.instant_labs.import_data.read') }}" method="post" class="form">
        @csrf

        @php
            $fieldName = 'data';
        @endphp
        <div class="form-group">
            <label for="{{ $fieldName }}" class="col-form-label">
                Copy-paste data from Excel/Google sheet (mandatory: ID, reference_datetime, reference_timezone)
            </label>

            <textarea class="form-control col-12" id="{{ $fieldName }}" name="{{ $fieldName }}" required rows="20"></textarea>

            @if ($errors->has($fieldName))
                <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
            @endif
        </div>

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.instant_labs.dashboard') }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Import</button>
            </div>
        </div>
    </form>
@endsection
