@extends('layouts.profiling')

@section('title', __('profile.sub_pages.profiling.heading') . ' - ' . config('app.name'))

@section('content')
    <h1>{{ __('profile.sub_pages.profiling.heading') }}</h1>

    <form class="form-questionnaire" action="{{ route('profiling') }}" method="post">
        @csrf

        @foreach($questionList as $question)
            @component($question->viewTemplate, ['question' => $question])@endcomponent
        @endforeach

        <div class="py-5">
            <button type="submit" class="btn btn-primary">{{ __('profile.sub_pages.profiling.submit_text') }}</button>
        </div>
    </form>
@stop
