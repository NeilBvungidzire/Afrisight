@extends('admin.projects.sample-provider.layout')

@section('header-navs')
    @include('admin.projects.sample-provider.respondent-management.header', ['title' => $title])
@endsection

@section('inner-content')

    <table class="table">
        <thead>
        <tr>
            <th>Project Codes</th>
            <th>External Respondent ID</th>
            <th>Status</th>
            <th>Supplier</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($respondents as $respondent)
            <tr>
                <td>{{ $respondent->project_code }}</td>
                <td>{{ $respondent->external_id }}</td>
                <td>{{ $respondent->status }}</td>
                <td>{{ $respondent->source }}</td>
                <td>
                    <pre>Approve Respondent & Reward</pre>
                    <pre>Disqualify Respondent & Deny Reward</pre>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
