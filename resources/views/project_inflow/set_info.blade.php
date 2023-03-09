@extends('layouts.website')

@section('title', ('Project inflow' . ' - ' . 'AfriSight'))

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-4 mb-3">{{ __('inflow.title') }}</h1>
                    <p>{{ __('inflow.subtitle') }}</p>
                    <hr>

                    <p>@lang('inflow.intro.line_1', ['loi' => $data['loi'], 'local_currency' => $data['local_currency'], 'local_amount' => number_format($data['local_amount'], 2), 'usd_amount' => number_format($data['usd_amount'], 2)])</p>
                    <p class="small">@lang('inflow.intro.line_2')</p>
                    <hr/>

                    <form method="post" action="{{ route('inflow.start', ['projectId' => $projectId]) }}">
                        @csrf
                        <input type="hidden" name="code" value="{{ $projectCodeEncrypted }}">

                        @php
                            $fieldName = 'email';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}"
                                   type="email" value="{{ $fieldValue }}" required
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}" placeholder="myemail@somewhere.com">

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'gender_code';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <select id="{{ $fieldName }}"
                                    class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                    name="{{ $fieldName }}" required>
                                <option value="">{{ __('model/person.gender.placeholder') }}</option>
                                @foreach($genders as $key => $value)
                                    <option value="{{ $key }}" {{ ($fieldValue === $key) ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'date_of_birth';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $fieldValue }}"
                                   placeholder="{{ __('model/person.date_of_birth.label') }}. {{ __('model/person.date_of_birth.placeholder') }}">

                            <small class="form-text text-muted">
                                {{ __('model/person.date_of_birth.info') }}
                            </small>

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'country_id';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <select id="{{ $fieldName }}"
                                    class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                    name="{{ $fieldName }}" required>
                                <option value="">{{ __('model/person.country.placeholder') }}</option>
                                @foreach($countries as $country)
                                    <option
                                        value="{{ $country['id'] }}" {{ ($fieldValue == $country['id']) ? 'selected' : '' }}>
                                        {{ $country['name'] }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                @lang('inflow.start_cta')
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-outline-info btn-block">
                                @lang('inflow.cancel_cta')
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
