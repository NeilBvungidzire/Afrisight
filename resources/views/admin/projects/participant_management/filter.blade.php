@extends('admin.projects.layout')

@section('inner-content')
    <h2>Filter participants</h2>

    <form action="{{ route('admin.projects.manage_participants.filter', ['project_code' => $projectCode]) }}"
          method="post" class="form">
        @csrf

        @php
            $fieldName = 'statuses';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-md-3 col-form-label">
                Status
            </label>

            <div class="col-md-9">
                @foreach ($statuses as $status)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="{{ $status }}" name="{{ $fieldName }}[]"
                               value="{{ $status }}">
                        <label class="form-check-label" for="{{ $status }}">{{ $status }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        @php
            $fieldName = 'uuids';
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-md-3 col-form-label">
                UUID's (separated by newline)
            </label>

            <div class="col-md-9">
                <textarea class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}" rows="10"></textarea>
            </div>

            @if ($errors->has($fieldName))
                <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
            @endif
        </div>

        <div class="form-group row">
            <div class="col-12">
                <hr>
            </div>
            <div class="col-sm-3 col-md-2 offset-sm-9 offset-md-10">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
        </div>
    </form>
@endsection
