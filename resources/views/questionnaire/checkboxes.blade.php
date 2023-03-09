@php
    $classnames = cn([
        'py-5',
    ]);
@endphp

<div class="{{ $classnames }}">
    <label class="font-weight-bold">
        {{ __($question->title) }}
    </label>

    @foreach($question->formData['options'] as $option)
        @php
            $classnames = cn([
                'form-check-input',
            ]);
            $id = $question->publicId . '-' . $option['value'];

            $attributes = [
                'checked' => $option['checked'],
            ];
        @endphp

        <div class="form-check">
            <input class="{{ $classnames }}"
                   id="{{ $id }}"
                   type="checkbox"
                   value="{{ $option['value'] }}"
                   name="{{ $option['name'] }}" {{ htmlAttributes($attributes) }}>
            <label class="form-check-label" for="{{ $id }}">
                {{ __($option['label']) }}
            </label>
        </div>
    @endforeach
</div>
