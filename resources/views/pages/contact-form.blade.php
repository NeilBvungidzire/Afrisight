@extends('layouts.website')

@section('title', __('pages.contacts_us.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--about'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('pages.contacts_us.heading') }}</h1>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    @if (session('status'))
                        <div class="alert alert-info" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contacts.submit') }}">
                        @csrf

                        @php
                            $fieldName = 'subject_code';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <select id="{{ $fieldName }}"
                                    class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                    name="{{ $fieldName }}" required>
                                <option value="">{{ __('questionnaire.choose-option') }}</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject['code'] }}" {{ ($fieldValue === $subject['code']) ? 'selected' : '' }}>
                                        {{ $subject['label'] }}
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
                            $fieldName = 'name';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}" type="text"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}" value="{{ $fieldValue }}" required
                                   placeholder="{{ __('pages.contacts_us.fields.name.placeholder') }}">

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'email_address';
                            $fieldValue = old($fieldName) ?? $user['email'];
                        @endphp
                        <div class="form-group">
                            <input id="{{ $fieldName }}" type="email"
                                   class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                   name="{{ $fieldName }}" value="{{ $fieldValue }}" required
                                   placeholder="{{ __('pages.contacts_us.fields.email.placeholder') }}">

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        @php
                            $fieldName = 'message';
                            $fieldValue = old($fieldName) ?? '';
                        @endphp
                        <div class="form-group">
                            <textarea id="{{ $fieldName }}" rows="6"
                                      class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                                      name="{{ $fieldName }}" value="{{ $fieldValue }}" required
                                      placeholder="{{ __('pages.contacts_us.fields.message.placeholder') }}"></textarea>

                            @if ($errors->has($fieldName))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($fieldName) }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">
                                {{ __('pages.contacts_us.submit_text') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-5">
                    <address>
                        <strong>{{ config('app.company.legal_name')  }}</strong><br>
                        {{ config('app.company.address.city') }}, {{ config('app.company.address.country') }}<br/><br/>
                        {{ __('pages.contacts_us.coc') }}: {{ config('app.company.coc') }}<br/>
                        {{ __('pages.contacts_us.vat') }}: {{ config('app.company.vat') }}<br/>
                    </address>
                </div>
            </div>
        </div>
    </section>
@endsection
