@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Import'])

    @alert()

    <form action="{{ route('admin.temp.import') }}" method="post" class="form">
        @csrf

        @php
            $fieldName = 'data';
        @endphp
        <div class="form-group">
            <label for="{{ $fieldName }}" class="col-form-label">
                Data
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
@endsection
