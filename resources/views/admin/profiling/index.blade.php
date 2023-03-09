@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Profiling Q&A overview</h1>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>Question</th>
            <th>Notes</th>
            <th>Datapoint</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($questions as $question)
            <tr>
                <td>{{ __($question->title) }}</td>
                <td>
                    @if (isset($question->conditions['country_id']))
                        {{ $countries[$question->conditions['country_id']] }}
                    @endif
                </td>
                <td>{{ __($question->datapoint_identifier) }}</td>
                <td style="width: 200px;">
                    <a href="{{ route('profiling.edit', ['profiling' => $question]) }}"
                       class="btn btn-sm btn-outline-secondary">Edit</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
