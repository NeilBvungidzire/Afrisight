@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Create new project' . ' - ' . config('app.name'))

@section('content')
    <h1>New project</h1>

    <form action="{{ route('admin.projects.store') }}" method="post">
        @csrf

        {{-- Client ID --}}
        @php($attribute = 'client_id')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Client</label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $attribute }}" name="{{ $attribute }}" required
                        class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}" autofocus>
                    @foreach($clients as $client)
                        <option value="{{ $client['id'] }}" {{ ($value == $client['id']) ? 'selected' : '' }}>
                            {{ $client['name'] }} (client prefix: {{ $client['code_prefix'] }}, current offset: {{ str_pad($client['code_offset'], 3, '0', STR_PAD_LEFT) }})
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Project country --}}
        @php($attribute = 'country_id')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Country</label>

            <div class="col-sm-7 col-lg-8">
                <select id="{{ $attribute }}" name="{{ $attribute }}" required
                        class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}">
                    @foreach($countries as $country)
                        <option value="{{ $country['id'] }}" {{ ($value == $country['id']) ? 'selected' : '' }}>
                            {{ $country['name'] }} (country code: {{ strtolower($country['iso_alpha_2']) }})
                        </option>
                    @endforeach
                </select>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Project code --}}
        @php($attribute = 'code')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Project Code</label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                       id="{{ $attribute }}" name="{{ $attribute }}" required
                       value="{{ $value }}">

                <small class="form-text text-muted">Make sure this project code is set as following:
                    <b>{client prefix}_{current offset}_{country code}_{additional code}</b>. The "additional code" is
                    required if additional info is needed. For example, afrisight_001, afrisight_011_nl,
                    afrisight_042_test, afrisight_113_nl_test</small>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
                @endif
            </div>
        </div>

        {{-- Project status --}}
        @php($attribute = 'status')
        @php($value = \App\Services\ProjectManagement\Constants\ProjectStatus::DRAFT)
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Initial Project Status</label>

            <div class="col-sm-7 col-lg-8">
                <p class="form-control-plaintext" id="{{ $attribute }}">{{ $value }}</p>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Project description --}}
        @php($attribute = 'description')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Project description</label>

            <div class="col-sm-7 col-lg-8">
                <textarea class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}" id="{{ $attribute }}"
                          name="{{ $attribute }}" rows="3">{{ $value }}</textarea>

                <small class="form-text text-muted">Info you can put here could be: country name, number of completes (n), estimation on IR, LOI, CPI, DIF, Start, and End, DA (yes/no), external platform ID.</small>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Enabled channels --}}
        @php($attribute = 'channels')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label">Enabled respondents channels</label>

            <div class="col-sm-7 col-lg-8">
                @foreach($channels as $channel)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $channel }}" id="{{ $channel }}"
                               name="{{ $attribute }}[]">
                        <label class="form-check-label" for="{{ $channel }}">
                            {{ $channel }}
                        </label>
                    </div>
                @endforeach

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Specific languages support --}}
        @php($attribute = 'language_restrictions')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label">Language restrictions</label>

            <div class="col-sm-7 col-lg-8">
                @foreach($languages as $language)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $language }}" id="{{ $language }}"
                               name="{{ $attribute }}[]">
                        <label class="form-check-label" for="{{ $language }}">
                            {{ $language }}
                        </label>
                    </div>
                @endforeach

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Specific device support --}}
        @php($attribute = 'device_restrictions')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label">Device restrictions</label>

            <div class="col-sm-7 col-lg-8">
                @foreach($devices as $device)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $device }}" id="{{ $device }}"
                               name="{{ $device }}[]">
                        <label class="form-check-label" for="{{ $device }}">
                            {{ $device }}
                        </label>
                    </div>
                @endforeach

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first($attribute) }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{-- Qualification type --}}
        @php($attribute = 'qualification_type')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Qualification Type (pre-survey screening)</label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                       id="{{ $attribute }}" name="{{ $attribute }}" value="{{ $value }}">

                <small class="form-text text-muted">Options: {{ implode(", ", $qualificationTypes) }} or custom type.</small>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
                @endif
            </div>
        </div>

        {{-- Exclusion by project --}}
        @php($attribute = 'exclusions')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Exclusion by project</label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                       id="{{ $attribute }}" name="{{ $attribute }}" value="{{ $value }}">

                <small class="form-text text-muted">If respondents who participated and got an end result in another
                    project and you want to exclude those in this project, you have to set the project code of those
                    projects.</small>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
                @endif
            </div>
        </div>

        {{-- Live link --}}
        @php($attribute = 'live_link')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Survey link (LIVE)</label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                       id="{{ $attribute }}" name="{{ $attribute }}" value="{{ $value }}">

                <small class="form-text text-muted">Make sure the respondent ID (param: RID) is set! For example, "https://survey-link.com/survey-code-123x?some-param=yes&id=<b>{RID}</b>&test=0".</small>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
                @endif
            </div>
        </div>

        {{-- Live link --}}
        @php($attribute = 'test_link')
        @php($value = old($attribute) ?? '')
        <div class="form-group row">
            <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Survey link (TEST)</label>

            <div class="col-sm-7 col-lg-8">
                <input type="text" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                       id="{{ $attribute }}" name="{{ $attribute }}" value="{{ $value }}">

                <small class="form-text text-muted">Make sure the respondent ID (param: RID) is set! For example, "https://survey-link.com/survey-code-123x?some-param=yes&id=<b>{RID}</b>&test=1".</small>

                @if ($errors->has($attribute))
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 offset-sm-4 col-lg-3 offset-lg-6">
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Create</button>
            </div>
        </div>
    </form>
@endsection
