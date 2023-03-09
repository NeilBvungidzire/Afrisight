@extends('layouts.profile')

@section('title', __('profile.sub_pages.edit_general_info.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3 px-4">{{ __('profile.sub_pages.edit_general_info.heading') }}</h1>

    <form class="bg-light py-3 my-3 px-4" action="{{ route('profile.basic-info.update') }}" method="post">
        @csrf
        @method('put')

        @php
            $fieldName = 'first_name';
            $value = old($fieldName) ?? $person[$fieldName];
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.first_name.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}"
                       name="{{ $fieldName }}"
                       value="{{ $value }}">

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'last_name';
            $value = old($fieldName) ?? $person[$fieldName];
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.last_name.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}"
                       name="{{ $fieldName }}"
                       value="{{ $value }}">

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'gender_code';
            $value = old($fieldName) ?? $person[$fieldName];
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.gender.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $fieldName }}"
                        class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                        name="{{ $fieldName }}" required>
                    <option
                        value="">{{ __('model/person.gender.placeholder') }}</option>
                    @foreach($genders as $key => $gender)
                        <option value="{{ $key }}" {{ ($value == $key) ? 'selected' : '' }}>
                            {{ $gender }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($fieldName) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'date_of_birth';
            $value = old($fieldName) ?? $person[$fieldName];
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.date_of_birth.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}"
                       name="{{ $fieldName }}"
                       value="{{ $value }}"
                       placeholder="{{ __('model/person.date_of_birth.placeholder') }}"
                       required>

                <small class="form-text text-muted">
                    {{ __('model/person.date_of_birth.info') }}
                </small>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'country_id';
            $value = old($fieldName) ?? $person[$fieldName];
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.country.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                @if ($countryName)
                    <input type="text" readonly class="form-control-plaintext" id="{{ $fieldName }}"
                           value="{{ $countryName }}">
                @else
                    <select id="{{ $fieldName }}"
                            class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                            name="{{ $fieldName }}" required>
                        <option
                            value="">{{ __('model/person.country.placeholder') }}</option>
                        @foreach($countries as $country)
                            <option
                                value="{{ $country['id'] }}" {{ ($value == $country['id']) ? 'selected' : '' }}>
                                {{ $country['name'] }}
                            </option>
                        @endforeach
                    </select>

                    @if ($errors->has($fieldName))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first($fieldName) }}</strong>
                        </span>
                    @endif
                @endif
            </div>
        </div>

        @php
            $fieldName = 'language_code';
            $value = old($fieldName) ?: $person[$fieldName] ?: strtoupper(app()->getLocale());
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.language.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $fieldName }}"
                        class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                        name="{{ $fieldName }}" required>
                    <option
                        value="">{{ __('model/person.language.placeholder') }}</option>
                    @foreach($languages as $languageCode)
                        <option
                            value="{{ $languageCode }}" {{ ($value == $languageCode) ? 'selected' : '' }}>
                            {{ __('language.' . strtolower($languageCode) . '.label') }}
                        </option>
                    @endforeach
                </select>

                <small class="form-text text-muted">
                    {{ __('model/person.language.info') }}
                </small>

                @if ($errors->has($fieldName))
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first($fieldName) }}</strong>
                        </span>
                @endif
            </div>
        </div>

        @php
            $fieldName = 'mobile_number';
            $value = old($fieldName) ?? $person[$fieldName];
        @endphp
        <div class="form-group row">
            <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                {{ __('model/person.mobile_number.label') }}
            </label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                       id="{{ $fieldName }}"
                       name="{{ $fieldName }}"
                       value="{{ $value }}"
                       placeholder="{{ __('model/person.mobile_number.placeholder') }}">

                <small class="form-text text-muted">
                    {{ __('model/person.mobile_number.info') }}
                </small>

                @if ($errors->has($fieldName))
                    <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 offset-lg-4 col-lg-4 mb-3 mb-md-0">
                <a class="btn btn-outline-warning btn-block" href="{{ route('profile.basic-info.show') }}">
                    {{ __('profile.sub_pages.edit_general_info.cancel_text') }}
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('profile.sub_pages.edit_general_info.save_text') }}
                </button>
            </div>
        </div>
    </form>
@endsection
