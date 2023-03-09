@extends('layouts.clean')

@section('title', 'Survey enrollment' . ' - ' . config('app.name'))

@section('content')
    <section>
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <form action="{{ route('survey.market_cube_002', ['uuid' => $uuid]) }}" method="post">
                        @csrf

                        @foreach($questions as $question)
                            @if($question['type'] === 'single_choice')
                                <div class="form-group">
                                    <label for="{{ $question['code'] }}" class="font-weight-bold">
                                        {{ $question['question'] }}
                                    </label>

                                    @if ( ! empty($question['info']))
                                        <p>{{ $info }}</p>
                                    @endif

                                    @php($lastKey = array_key_last($question['options']))
                                    @foreach ($question['options'] as $key => $option)
                                        <div class="form-check">
                                            <input
                                                class="form-check-input{{ $errors->has($question['id']) ? ' is-invalid' : '' }}"
                                                type="radio" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                                value="{{ $option['code'] }}" required>

                                            <label class="form-check-label" for="{{ $option['code'] }}">
                                                {{ $option['label'] }}
                                            </label>

                                            @if ($key === $lastKey && $errors->has($question['id']))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>Make sure you select an option.</strong>
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question['type'] === 'open_question')
                                <div class="form-group">
                                    <label for="{{ $question['code'] }}" class="font-weight-bold">
                                        {{ $question['question'] }}
                                    </label>

                                    @if ( ! empty($question['info']))
                                        <p>{{ $info }}</p>
                                    @endif

                                    <input type="text" name="{{ $question['code'] }}" id="{{ $option['code'] }}"
                                           class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}" required>

                                    @if ($key === $lastKey && $errors->has($question['id']))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>Make sure you fill in this field.</strong>
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <hr class="my-5">
                        @endforeach

                        <div class="form-group">
                            <button type="submit" class="btn btn-block btn-primary">{{ __('general.next') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
