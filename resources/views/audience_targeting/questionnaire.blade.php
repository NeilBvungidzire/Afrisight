@extends('layouts.clean')

@section('title', 'Survey enrollment' . ' - ' . config('app.name'))

@section('content')
    <section>
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    @alert

                    <form action="{{ route('enrollment.questionnaire', ['uuid' => $uuid]) }}" method="post">
                        @csrf

                        @php($questionId = 1)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @isset($questions[2])
                            @php($question = $questions[2])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input class="form-check-input{{ $errors->has(2) ? ' is-invalid' : '' }}"
                                               type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                               value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has(2))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @isset($questions[5])
                            @php($question = $questions[5])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input class="form-check-input{{ $errors->has(5) ? ' is-invalid' : '' }}"
                                               type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                               value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has(5))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @isset($questions[6])
                            @php($question = $questions[6])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input class="form-check-input{{ $errors->has(6) ? ' is-invalid' : '' }}"
                                               type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                               value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has(6))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @isset($questions[7])
                            @php($question = $questions[7])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input class="form-check-input{{ $errors->has(7) ? ' is-invalid' : '' }}"
                                               type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                               value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has(7))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 9)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 10)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 11)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 12)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 13)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 14)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @if ( ! empty($question['info']))
                                    <p>
                                        @foreach ($question['info'] as $info)
                                            <span>{{ $info }}</span><br>
                                        @endforeach
                                    </p>
                                @endif
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 15)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @if ( ! empty($question['info']))
                                    <p>
                                        @foreach ($question['info'] as $info)
                                            <span>{{ $info }}</span><br>
                                        @endforeach
                                    </p>
                                @endif
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 16)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @if ( ! empty($question['info']))
                                    <p>
                                        @foreach ($question['info'] as $info)
                                            <span>{{ $info }}</span><br>
                                        @endforeach
                                    </p>
                                @endif
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 17)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @if ( ! empty($question['info']))
                                    <p>
                                        @foreach ($question['info'] as $info)
                                            <span>{{ $info }}</span><br>
                                        @endforeach
                                    </p>
                                @endif
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        @php($questionId = 18)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @if ( ! empty($question['info']))
                                    <p>
                                        @foreach ($question['info'] as $info)
                                            <span>{{ $info }}</span><br>
                                        @endforeach
                                    </p>
                                @endif
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        {{-- Single-choice --}}
                        @php($questionIds = [19, 20, 21, 22, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 40, 43, 44, 45, 46, 47, 48, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 136])
                        @foreach($questionIds as $questionId)
                            @isset($questions[$questionId])
                                @php($question = $questions[$questionId])
                                <div class="form-group">
                                    <label for="{{ $question['code'] }}" class="font-weight-bold">
                                        {{ $question['question'] }}
                                    </label>
                                    @if ( ! empty($question['info']))
                                        <p>
                                            @foreach ($question['info'] as $info)
                                                <span>{{ $info }}</span><br>
                                            @endforeach
                                        </p>
                                    @endif
                                    @php($lastKey = array_key_last($question['options']))
                                    @foreach ($question['options'] as $key => $option)
                                        <div class="form-check">
                                            <input
                                                class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                                type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                                value="{{ $option['code'] }}" required>
                                            <label class="form-check-label" for="{{ $option['code'] }}">
                                                {{ $option['label'] }}
                                            </label>

                                            @if ($key === $lastKey && $errors->has($questionId))
                                                <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <hr class="my-5">
                            @endisset
                        @endforeach

                        {{-- Multiple-choice --}}
                        @php($questionIds = [23, 38, 39, 41, 42, 49, 60, 135])
                        @foreach($questionIds as $questionId)
                            @isset($questions[$questionId])
                                @php($question = $questions[$questionId])
                                <div class="form-group">
                                    <label for="{{ $question['code'] }}" class="font-weight-bold">
                                        {{ $question['question'] }}
                                    </label>
                                    @if ( ! empty($question['info']))
                                        <p>
                                            @foreach ($question['info'] as $info)
                                                <span>{{ $info }}</span><br>
                                            @endforeach
                                        </p>
                                    @endif
                                    @php($lastKey = array_key_last($question['options']))
                                    @foreach ($question['options'] as $key => $option)
                                        <div class="form-check">
                                            <input
                                                class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                                type="checkbox" name="{{ $question['code'] }}[]"
                                                id="{{ $option['code'] }}"
                                                value="{{ $option['code'] }}">
                                            <label class="form-check-label" for="{{ $option['code'] }}">
                                                {{ $option['label'] }}
                                            </label>

                                            @if ($key === $lastKey && $errors->has($questionId))
                                                <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <hr class="my-5">
                            @endisset
                        @endforeach

                        @php($questionId = 24)
                        @isset($questions[$questionId])
                            @php($question = $questions[$questionId])
                            <div class="form-group">
                                <label for="{{ $question['code'] }}" class="font-weight-bold">
                                    {{ $question['question'] }}
                                </label>
                                @if ( ! empty($question['info']))
                                    <p>
                                        @foreach ($question['info'] as $info)
                                            <span>{{ $info }}</span><br>
                                        @endforeach
                                    </p>
                                @endif
                                @php($lastKey = array_key_last($question['options']))
                                @foreach ($question['options'] as $key => $option)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input{{ $errors->has($questionId) ? ' is-invalid' : '' }}"
                                            type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                            value="{{ $option['code'] }}" required>
                                        <label class="form-check-label" for="{{ $option['code'] }}">
                                            {{ $option['label'] }}
                                        </label>

                                        @if ($key === $lastKey && $errors->has($questionId))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>Make sure you select an option.</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <hr class="my-5">
                        @endisset

                        <div class="form-group">
                            <button type="submit" class="btn btn-block btn-primary">{{ __('general.next') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
