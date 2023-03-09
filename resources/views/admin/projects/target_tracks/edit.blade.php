@extends('admin.projects.layout')

@section('inner-content')
    <h2>Edit total complete limit</h2>

    <form action="{{ route('admin.projects.target_track.update_complete_limit', ['project_code' => $projectCode]) }}"
          method="post" class="form">
        @csrf

        {{-- Total completes limit --}}
        @php
            $attribute = 'complete_limit';
            $value = old($attribute) ?? $totalCompletesLimit;
        @endphp
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Total Complete Limit</label>

            <div class="col-sm-7 col-lg-8">
                <input type="number" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                       id="{{ $attribute }}" name="{{ $attribute }}" required
                       value="{{ $value }}">

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-3 col-md-2 offset-sm-6 offset-md-8">
                <a href="{{ route('admin.projects.target_track.index', ['project_code' => $projectCode]) }}"
                   class="btn btn-outline-info btn-block">
                    Cancel
                </a>
            </div>
            <div class="col-sm-3 col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Update</button>
            </div>
        </div>
    </form>

    <hr>

    <h2>Edit quota limit</h2>

    <form action="{{ route('admin.projects.target_track.update_quotas', ['project_code' => $projectCode]) }}"
          method="post" class="form">
        @csrf

        @foreach ($quotas as $quota)
            @php
                $fieldName = "quota.{$quota['id']}";
                $value = old($fieldName) ?? $quota['quota'];
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-md-6 col-form-label">{{ $quota['label'] }}</label>
                <div class="col-md-6">
                    <div class="input-group{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Limit: </span>
                        </div>
                        <input type="number" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                               id="{{ $fieldName }}" name="quota[{{ $quota['id'] }}]" value="{{ $quota['quota'] }}">
                        <div class="input-group-append">
                            <span class="input-group-text">Achieved: {{ $quota['count'] }}</span>
                        </div>
                        <div class="input-group-append">
                            <span class="input-group-text">Needed: {{ $quota['quota'] - $quota['count'] }}</span>
                        </div>
                    </div>
                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">Cannot be empty and must be a numeric value.</div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="form-group row">
            <div class="col-sm-3 col-md-2 offset-sm-6 offset-md-8">
                <a href="{{ route('admin.projects.target_track.index', ['project_code' => $projectCode]) }}"
                   class="btn btn-outline-info btn-block">
                    Cancel
                </a>
            </div>
            <div class="col-sm-3 col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Update</button>
            </div>
        </div>
    </form>
@endsection
