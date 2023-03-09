@extends('layouts.clean')

@section('title', ('CAPI project' . ' - ' . 'AfriSight'))

@section('content')
    <div class="container py-3">
        <h1>Import links</h1>

        <form action="{{ route('capi.admin.import') }}"
              method="post" class="form">
            @csrf

            @php
                $fieldName = "sample_code";
                $value = old($fieldName) ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-3 col-lg-2 col-form-label">
                    Sample Code
                </label>

                <div class="col-sm-9 col-lg-10">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}">

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = "entry_links";
                $value = old($fieldName) ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-3 col-lg-2 col-form-label">
                    Entry Links
                </label>

                <div class="col-sm-9 col-lg-10">
                    <textarea type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                              id="{{ $fieldName }}" name="{{ $fieldName }}" rows="15">{{ $value }}</textarea>

                    <small class="form-text text-muted">
                        Each link on a separate line.
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-9 offset-sm-3 col-lg-10 offset-lg-2">
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </div>
            </div>
        </form>
    </div>
@endsection
