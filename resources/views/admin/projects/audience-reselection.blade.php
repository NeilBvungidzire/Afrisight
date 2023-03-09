@extends('admin.projects.layout')

@section('inner-content')

    <form action="/">
        @csrf

        <input type="hidden" name="engage" value="0" class="attribute">

        @php($attribute = 'country_codes')
        <div class="form-group">
            <label for="{{ $attribute }}">Country Code</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" required
                   value="{{ request()->query($attribute) ?? $countryCode }}">

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'languages')
        <div class="form-group">
            <label for="{{ $attribute }}">Languages</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" value="{{ request()->query($attribute) ?? implode(',', $languageRestrictions) }}">
            <small id="{{ $attribute }}" class="form-text text-muted">Options: EN,FR,PT</small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'age_ranges')
        <div class="form-group">
            <label for="{{ $attribute }}">Age Ranges</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" value="{{ request()->query($attribute) }}">
            <small id="{{ $attribute }}" class="form-text text-muted">For example: 16-18,19-24</small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'genders')
        <div class="form-group">
            <label for="{{ $attribute }}">Genders</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" value="{{ request()->query($attribute) }}">
            <small id="{{ $attribute }}" class="form-text text-muted">Options: m,w</small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'include_with_status')
        <div class="form-group">
            <label for="{{ $attribute }}">Include engaged persons (respondents) with specific status</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" value="{{ request()->query($attribute) }}">
            <small id="{{ $attribute }}" class="form-text text-muted">Options: {{ implode(', ', $statuses) }}</small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'channel')
        <div class="form-group">
            <label for="{{ $attribute }}">Channel</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" required
                   value="{{ request()->query($attribute) ?? 'email' }}">
            <small id="{{ $attribute }}" class="form-text text-muted">Options: sms, email</small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'size')
        <div class="form-group">
            <label for="{{ $attribute }}">Size</label>
            <input type="number" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" required
                   value="{{ request()->query($attribute) ?? 100 }}">
            <small id="{{ $attribute }}" class="form-text text-muted">Numeric value, for example 100</small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'incentive_package_id')
        <div class="form-group">
            <label for="{{ $attribute }}">Incentive Package ID</label>
            <input type="number" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" required
                   value="{{ request()->query($attribute) }}">
            @foreach ($incentivePackages ?? [] as $packageId => $package)
                <p class="badge badge-info">
                    ID: {{ $packageId }} -
                    @foreach ($package as $paramKey => $paramValue)
                        <span class="">{{ $paramKey }}</span>: <span class="pr-2">{{ $paramValue }}</span>
                    @endforeach
                </p>
            @endforeach

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        @php($attribute = 'uuids')
        <div class="form-group">
            <label for="{{ $attribute }}">
                Respondent IDs (UUID) (separated by newline, no whitespace)
            </label>

            <textarea class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                      id="{{ $attribute }}" name="{{ $attribute }}" rows="10"></textarea>

            @if ($errors->has($attribute))
                <div class="invalid-feedback">{{ $errors->first($attribute) }}</div>
            @endif
        </div>

        @php($attribute = 'person_ids')
        <div class="form-group">
            <label for="{{ $attribute }}">Persons ID</label>
            <input type="text" class="form-control attribute{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                   id="{{ $attribute }}" name="{{ $attribute }}" value="{{ request()->query($attribute) }}">
            <small id="{{ $attribute }}" class="form-text text-muted">
                Numeric value, for example 1,2,3,etc. Your ID is: {{ $userPersonId }}.
            </small>

            @if ($errors->has($attribute))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first($attribute) }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group">
            Feasible: {{ number_format($feasible) }}
        </div>
        <a href="{{ Request::url() }}" class="btn btn-primary" id="search-url">Check feasibility</a>
        <a href="{{ Request::url() }}" class="btn btn-danger{{ $isLive ? null : ' disabled' }}" id="engage-url">Engage panelists</a>
    </form>

    <script>
        let url = new URL('{{ Request::url() }}');
        const urlElement = document.getElementById('search-url');

        Array.from(document.getElementsByClassName('attribute')).forEach(element => {
            element.addEventListener('change', handleQueryParams);

            const attribute = element.getAttribute('name');
            const value = element.value;
            if (value) {
                setQueryParam(attribute, value);
            }
        });

        document.getElementById('engage-url').addEventListener('click', event => {
            event.preventDefault();

            setQueryParam('engage', 1);
            window.location.href = url.href;
        });

        setUrl(url.href);

        function handleQueryParams(event) {
            const attribute = event.target.getAttribute('name');
            const value = event.target.value;
            setQueryParam(attribute, value);
            setUrl(url.href);
        }

        function setQueryParam(attribute, value) {
            if (url.searchParams.has(attribute)) {
                url.searchParams.set(attribute, value);
            } else {
                url.searchParams.append(attribute, value);
            }
        }

        function setUrl(href) {
            urlElement.href = href;
        }
    </script>
@endsection
