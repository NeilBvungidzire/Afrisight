@php
    $classnames = cn([
        'py-5',
    ]);
@endphp

<div class="{{ $classnames }}">
    <label class="font-weight-bold" for="{{ $question->publicId }}">
        {{ __($question->title) }}
    </label>

    @php
        $classnames = cn([
            'form-control',
        ]);
    @endphp

    <select class="{{ $classnames }}" id="{{ $question->publicId }}" name="{{ $question->formData['name'] }}">
        <option value="">{{ __('questionnaire.choose-option') }}</option>

        @foreach($question->formData['options'] as $option)
            @php
                $attributes = [
                    'selected' => $option['selected'],
                ];
            @endphp
            <option value="{{ $option['value'] }}" {{ htmlAttributes($attributes) }}>
                {{ __($option['label']) }}
            </option>
        @endforeach
    </select>
</div>
