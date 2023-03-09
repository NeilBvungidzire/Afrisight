@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'New screening question' . ' - ' . config('app.name'))

@section('content')
    <h1>New screening question</h1>

    <form action="{{ route('admin.screening.store-question') }}" method="post">
        @csrf

        {{-- Question --}}
        @php($attribute = 'question')
        @php($key = $attribute)
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">Question (as question text itself or
                translation key)</label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                       id="{{ $key }}" name="{{ $attribute }}" required
                       value="{{ $value }}">

                @if ($errors->has($key))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($key) }}</strong>
                </span>
                @endif
            </div>
        </div>

        {{-- Question type --}}
        @php($attribute = 'question_type')
        @php($key = $attribute)
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">Question type</label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $key }}" name="{{ $attribute }}" required
                        class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}">
                    @foreach($questionTypes as $questionType)
                        <option value="{{ $questionType }}" {{ ($value == $questionType) ? 'selected' : '' }}>
                            {{ $questionType }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($key))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($key) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Infotext --}}
        @php($attribute = 'params[info]')
        @php($key = 'params.info')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">
                Question helper text (as text itself or translation key)
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                       id="{{ $key }}" name="{{ $attribute }}" value="{{ $value }}">

                @if ($errors->has($key))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($key) }}</strong>
                </span>
                @endif
            </div>
        </div>

        {{-- Country specific --}}
        @php($attribute = 'params[country_code]')
        @php($key = 'params.country_code')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">Only specific country (optional)</label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $key }}" name="{{ $attribute }}"
                        class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}">
                    <option value="">Not specific</option>
                    @foreach($countries as $country)
                        <option value="{{ $country['iso_alpha_2'] }}"
                            {{ ($value == $country['iso_alpha_2']) ? 'selected' : '' }}>
                            {{ $country['name'] }} (country code: {{ strtolower($country['iso_alpha_2']) }})
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($key))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($key) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 offset-sm-4 col-lg-3 offset-lg-6">
                <a href="{{ route('admin.screening.index') }}" class="btn btn-outline-secondary btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Create</button>
            </div>
        </div>
    </form>
@endsection
