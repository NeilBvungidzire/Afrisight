@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Update translation</h1>

    <div class="form">
        @csrf
        @method('put')

        <div class="form-group">
            <p>
                Parameters used:
                @if (count($elements['list']))
                    @foreach($elements['list'] as $param)
                        <span class="badge badge-light">{{ $param }}</span>
                    @endforeach
                @else
                    no parameter used
                @endif
                <small class="form-text text-muted">If parameters is set, please make sure to use each of them in all
                    translations</small>
            </p>
        </div>

        <hr>

        @php
            $fieldName = 'key';
            $value = old($fieldName) ?? $translation->key;
        @endphp
        <div class="form-group">
            <label for="key">Unique key</label>
            <input id="{{ $fieldName }}" type="text"
                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                   name="{{ $fieldName }}"
                   value="{{ $value }}" required
                   placeholder="my.key" disabled>
            <small class="form-text text-muted">Must be unique</small>
        </div>

        @foreach(LaravelLocalization::getSupportedLocales() as $key => $specs)
            @php
                $fieldName = 'text.' . $key;
                $value = old($fieldName) ?? (isset($translation->text[$key])) ? $translation->text[$key] : false;
            @endphp

            <div class="form-group">
                <label for="{{ $fieldName }}">{{ $specs['name'] }} translation</label>
                <textarea id="{{ $fieldName }}" rows="5" name="text[{{ $key }}]" disabled
                          class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                          placeholder="{{ $specs['name'] }} translation">{{ $value }}</textarea>

                @php($unusedElements = array_diff($elements['list'], $elements['by_locale'][$key]))
                @if (count($unusedElements))
                    <p>
                        Make sure to use these parameters:
                        @foreach($unusedElements as $param)
                            <span class="badge badge-danger">{{ $param }}</span>
                        @endforeach
                    </p>
                @endif

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($fieldName) }}</strong>
                </span>
                @endif
            </div>
        @endforeach

        <div class="form-group text-right">
            @if( ! $translation->is_published)
                @php($formId = "delete-translation")
                <a href="#delete" class="btn btn-outline-danger"
                   onclick="event.preventDefault(); document.getElementById('{{ $formId }}').submit();">
                    Delete
                </a>
                <form id="{{ $formId }}" method="post" style="display: none;"
                      action="{{ route('translation.destroy', ['translation' => $translation->id]) }}">
                    @csrf
                    @method('delete')
                </form>
            @endif
            <a href="{{ route('translation.index') }}" class="btn btn-outline-secondary">Overview</a>
            <a href="{{ route('translation.edit', ['translation' => $translation]) }}" class="btn btn-outline-secondary">Edit</a>
        </div>
    </div>
@endsection
