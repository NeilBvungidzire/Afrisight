{{-- Project ID --}}
@php
    $fieldName = 'project_id';
    $value = old($fieldName) ?? $record->project_id ?? $projectId ?? '';
@endphp
<div class="form-group row">
    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
        Project ID
    </label>

    <div class="col-sm-7 col-lg-8">
        <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
               id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required autocomplete="off">

        @if ($errors->has($fieldName))
            <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
        @endif
    </div>
</div>

{{-- Project Code --}}
@php
    $fieldName = 'project_codes';
    $projectCodes = isset($record, $record->project_codes) ? $record->project_codes : null;
    $value = old($fieldName) ?? $projectCodes ?? '';
@endphp
<div class="form-group row">
    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
        Project Codes
    </label>

    <div class="col-sm-7 col-lg-8">
        <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
               id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required autocomplete="off">

        <small id="{{ $fieldName }}" class="form-text text-muted">Separate the project codes by comma (,).</small>

        @if ($errors->has($fieldName))
            <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
        @endif
    </div>
</div>

{{-- Source --}}
@php
    $fieldName = 'source';
    $value = old($fieldName) ?? $record->source ?? '';
@endphp
<div class="form-group row">
    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
        Supplier
    </label>

    <div class="col-sm-7 col-lg-8">
        <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
            <option value="">Select supplier</option>
            @foreach($providers as $providerId => $providerName)
                <option value="{{ $providerId }}" {{ ($value == $providerId) ? 'selected' : '' }}>
                    {{ $providerName }}
                </option>
            @endforeach
        </select>

        @if ($errors->has($fieldName))
            <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
        @endif
    </div>
</div>

<hr>
<p class="font-italic font-weight-bold">Survey link to be used by the sample supplier</p>
<p>
    <span class="small">Parameters that can be set in the survey link by the sample supplier. The required parameters are already in the URL. The format is "parameter=PLACEHOLDER/specified value". In case of specified value, you don't have to change anything. In case of PLACEHOLDER, so capitalized text, you have to replace that with the parameter name defined by the supplier. An example, supplier respondent ID parameter name is RESP_ID. So to replace the respondent ID placeholder you have to replace RID with RESP_ID in the URL.</span>
    <span class="d-block"><span class="badge badge-info">project-code={{ $record->project_id ?? $projectId }}</span> = (required) Sample supplier respondent ID, which will be returned back via end result link.</span>
    <span class="d-block"><span class="badge badge-info">id=RID</span> = (required) Sample supplier respondent ID, which will be returned back via end result link.</span>
    <span class="d-block"><span class="badge badge-info">gender=GENDER</span> = (optional) Respondent gender, which can be passed on to us.</span>
    <span class="d-block"><span class="badge badge-info">age=AGE</span> = (optional) Respondent age, which can be passed on to us.</span>
    <span class="d-block"><span class="badge badge-info">year-of-birth=YOB</span> = (optional) Respondent year of birth, which can be passed on to us.</span>
    <span class="d-block"><span class="badge badge-info">date-of-birth=DOB</span> = (optional) Respondent date of birth, which can be passed on to us.</span>
</p>

@php
    $fieldName = 'survey-link';
@endphp
<div class="form-group row">
    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
        Survey link
    </label>

    <div class="col-sm-7 col-lg-8">
        <input type="text" readonly class="form-control-plaintext" id="{{ $fieldName }}"
               value="{{ $providerSurveyLinkToUse }}">
    </div>
</div>

<hr>
<p class="font-italic font-weight-bold">Sample supplier end result redirects</p>
<p>Possible parameters you can set in the supplier redirect link (only if required):
    <span class="d-block"><span class="badge badge-info">{RID}</span> = (optional) Sample supplier respondent ID. Only if the supplier asks for.</span>
</p>

{{-- End Redirects --}}
<p class="font-italic small">SCREEN_OUT and DISQUALIFIED can be the same link, in case the difference is not supported
    by the supplier.</p>
@foreach($endStatuses as $endStatus)
    @php
        $fieldName = "end_redirects[${endStatus}]";
        $key = "end_redirects.${endStatus}";
        $value = old($key) ?? $record->end_redirects[$endStatus] ?? '';
    @endphp
    <div class="form-group row">
        <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
            {{ $endStatus }}
        </label>

        <div class="col-sm-7 col-lg-8">
            <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                   id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" autocomplete="off" required>

            @if ($errors->has($key))
                <div class="invalid-feedback">{{ $errors->first($key) }}</div>
            @endif
        </div>
    </div>
@endforeach
