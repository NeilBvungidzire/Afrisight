@extends('layouts.clean')

@section('title', ('CAPI project' . ' - ' . 'AfriSight'))

@section('content')
    <div class="container py-3">
        <h1>Fieldwork</h1>
        @isset($intId)
            <p>Interviewer ID: {{ $intId }}</p>
{{--            <ul class="list-group">--}}
{{--                <li class="list-group-item">Total completes: x</li>--}}
{{--                <li class="list-group-item">Total interviews: x</li>--}}
{{--            </ul>--}}
        @endisset

        @empty($intId)
            <p>We can't recognize your interviewer ID. Make sure to use the interviewer link communicated to you by
                AfriSight supervisor. In case it's still not working, please contact AfriSight supervisor for further
                instructions.</p>
        @else
            <hr>
            <a href="{{ route('capi.fieldwork.start', ['int_id' => $intId, 'is_test' => !$isLive]) }}"
               class="btn btn-primary btn-block" target="_blank">
                @if($isLive)
                    Start new interview
                @else
                    Start new Test Interview
                @endif
            </a>
        @endempty
    </div>
@endsection
