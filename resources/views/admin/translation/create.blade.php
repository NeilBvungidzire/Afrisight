@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Create translation</h1>

    <form action="{{ route('translation.store') }}" method="post">
        @csrf

        @php
            $fieldName = 'key';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group">
            <label for="key">Unique key</label>
            <input id="{{ $fieldName }}" type="text"
                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}" autofocus
                   name="{{ $fieldName }}" value="{{ $value }}" required placeholder="my.key">
            <small class="form-text text-muted">Must be unique</small>

            @if ($errors->has($fieldName))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
            @endif
        </div>

        @php
            $fieldName = 'tags';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group">
            <label for="key">Tags</label>
            <input id="{{ $fieldName }}" type="text"
                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                   name="{{ $fieldName }}" value="{{ $value }}" placeholder="myfirsttag,mysecond_tag,my_third_tag">
            <small class="form-text text-muted">Used to group translations</small>

            @if ($errors->has($fieldName))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
            @endif
        </div>

        @foreach(LaravelLocalization::getSupportedLocales() as $key => $specs)
            @php
                $fieldName = 'text.' . $key;
                $value = old($fieldName) ?? '';
            @endphp
            <div class="form-group">
                <label for="{{ $fieldName }}">{{ $specs['name'] }} translation</label>
                <textarea id="{{ $fieldName }}" rows="5" name="text[{{ $key }}]"
                          class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                          placeholder="{{ $specs['name'] }} translation">{{ $value }}</textarea>

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
                @endif
            </div>
        @endforeach

        <div class="form-group text-right">
            <a href="{{ route('translation.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </form>
@endsection
