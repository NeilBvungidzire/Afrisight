@php
    $value = old($key) ?? '';
@endphp
<div class="form-group row">
    <label for="{{ $key }}" class="col-sm-5 col-lg-4 col-form-label">
        {{ $label }}
    </label>

    <div class="col-sm-7 col-lg-8">
        <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
               id="{{ $key }}" name="{{ $name }}" value="{{ $value }}" autocomplete="off" required>

        @if ($errors->has($key))
            <div class="invalid-feedback">{{ $errors->first($key) }}</div>
        @endif
    </div>
</div>
