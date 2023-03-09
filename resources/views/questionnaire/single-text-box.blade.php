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
    <input class="{{ $classnames }}"
           type="{{ $question->formData['type'] }}"
           name="{{ $question->formData['name'] }}"
           id="{{ $question->publicId }}"
           value="{{ $question->formData['value'] }}">
</div>
