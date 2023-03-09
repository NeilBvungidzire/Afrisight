@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Screening questions' . ' - ' . config('app.name'))

@section('content')
    <div class="row">
        <div class="col-12 col-md-5">
            <h1>Screening questions</h1>
        </div>
        <div class="col-12 col-md-7 d-flex align-items-center justify-content-end">
            <ul class="nav nav-pills">
                <li class="nav-item mx-1">
                    <a href="{{ route('admin.screening.create-question') }}"
                       class="btn btn-outline-primary">Add new</a>
                </li>
            </ul>
        </div>
        <div class="col-12">
            <hr>
        </div>
    </div>

    @alert

    <table class="table table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Question</th>
            <th>Type</th>
            <th>Country Specific</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($records as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ __($record->question) }}</td>
                <td>{{ $record->question_type }}</td>
                <td>{{ $record->params['country_code'] ?? '' }}</td>
                <td>
                    <a href="{{ route('admin.screening.edit-question', ['id' => $record->id]) }}"
                       class="btn btn-sm btn-outline-info m-1">
                        Edit question
                    </a>
                    <a href="{{ route('admin.screening.edit-answers', ['id' => $record->id]) }}"
                       class="btn btn-sm btn-outline-info m-1">
                        Edit answers
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
