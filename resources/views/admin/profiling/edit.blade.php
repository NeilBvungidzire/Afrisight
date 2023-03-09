@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Update profiling Q&A</h1>

    <form action="{{ route('profiling.update', ['profiling' => $profiling]) }}" method="post">
        @csrf
        @method('put')

        @php
            $fieldName = 'title';
            $value = old($fieldName) ?? $profiling->title;
        @endphp
        <div class="form-group">
            <label for="key">Question translation key reference</label>
            <input id="{{ $fieldName }}" type="text"
                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                   name="{{ $fieldName }}" value="{{ $value }}" required placeholder="my.key">
            @include('admin.profiling.translation_list', ['value' => $value])

            @if ($errors->has($fieldName))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
            @endif
        </div>

        {{-- Link profiling question to datapoint attribute --}}
        @php
            $fieldName = 'datapoint_identifier';
            $value = old($fieldName) ?? $profiling->datapoint_identifier ?? '';
        @endphp
        <div class="form-group">
            <label for="key">Datapoint attribute</label>
            <input id="{{ $fieldName }}" type="text"
                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                   name="{{ $fieldName }}" value="{{ $value }}">

            @if ($errors->has($fieldName))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
            @endif
        </div>

        @foreach ($profiling->answer_params as $index => $answerParam)
            <hr class="mt-5">
            <h4>Answer option {{ $index + 1 }}</h4>
            @php
                $fieldName = 'answer_params[' . $index . '][uuid]';
                $value = old($fieldName) ?? $answerParam['uuid'];
            @endphp
            <div class="form-group{{ Auth::user()->role === 'SUPER_ADMIN' ? '' : ' d-none' }}">
                <label for="key">UUID</label>
                <input id="{{ $fieldName }}" type="text"
                       class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       name="{{ $fieldName }}" value="{{ $value }}" required>

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
                @endif
            </div>

            @php
                $fieldName = 'answer_params[' . $index . '][label]';
                $value = old($fieldName) ?? $answerParam['label'];
            @endphp
            <div class="form-group">
                <label for="key">Answer translation key reference</label>
                <input id="{{ $fieldName }}" type="text"
                       class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       name="{{ $fieldName }}" value="{{ $value }}" required>
                @include('admin.profiling.translation_list', ['value' => $value])

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
                @endif
            </div>

            {{-- Link profiling question option to datapoint value --}}
            @php
                $fieldName = 'answer_params[' . $index . '][datapoint_value]';
                $value = old($fieldName) ?? $answerParam['datapoint_value'] ?? '';
            @endphp
            <div class="form-group">
                <label for="key">Datapoint value</label>
                <input id="{{ $fieldName }}" type="text"
                       class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       name="{{ $fieldName }}" value="{{ $value }}">

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
                @endif
            </div>
        @endforeach

        <div class="form-group text-right">
            <a href="{{ route('profiling.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <a href="{{ route('profiling.index') }}" class="btn btn-outline-secondary">Overview</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
@endsection
