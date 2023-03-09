@extends('admin.projects.layout')

@section('inner-content')
    <p>
        <span class="text-muted">Filter by end result:</span>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.respondents', array_merge(request()->query(), ['projectCode' => $projectCode, 'result' => ''])) }}">
            All
        </a>
        @foreach ($statuses as $status)
            <a class="btn btn-sm btn-outline-info"
               href="{{ route('admin.projects.respondents', array_merge(request()->query(), ['projectCode' => $projectCode, 'result' => $status])) }}">
                {{ $status }}
            </a>
        @endforeach

        <span class="text-muted ml-2">Filter by quota status:</span>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.respondents', array_merge(request()->query(), ['projectCode' => $projectCode, 'status' => ''])) }}">
            All
        </a>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.respondents', array_merge(request()->query(), ['projectCode' => $projectCode, 'status' => 'open'])) }}">
            Open
        </a>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.respondents', array_merge(request()->query(), ['projectCode' => $projectCode, 'status' => 'closed'])) }}">
            Closed
        </a>

        <span class="pl-3 text-muted">Total found:</span> <span>{{ number_format($respondents->total()) }}</span>
    </p>

    <table class="table table-hover">
        <thead>
        <tr>
            <th>Date end result</th>
            <th>Target hits</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($respondents as $respondent)
            <tr>
                <td>
                    <p>{{ $respondent->updated_at }}</p>
                    <p>Person ID: {{ $respondent->person_id }}</p>
                    <p>Public ID: {{ $respondent->uuid }}</p>
                    @foreach ($respondent->invitations as $invitation)
                        <p>
                            <span>UUID: {{ $invitation->uuid }}</span><br>
                            <span>Type: {{ $invitation->type }}</span><br>
                            <span>Status: {{ $invitation->status }}</span><br>
                            <span>Time: {{ $invitation->updated_at }}</span>
                        </p>
                    @endforeach
                    <p>
                        @foreach ($respondent->status_history as $status => $timestamp)
                            <span>{{ $timestamp }} {{ $status }}</span><br>
                        @endforeach
                    </p>
                </td>
                <td>
                    @foreach ($respondent->target_hits as $targetId)
                        @isset($targetCriteria[$targetId])
                            <p class="mb-0">
                                <span class="text-muted">{{ $targetCriteria[$targetId]->criteria }}</span>:
                                <span>{{ $targetCriteria[$targetId]->value }}</span>
                            </p>
                        @endisset
                    @endforeach
                </td>
                <td>
                    <p>{{ $respondent->current_status }}</p>
                    <p>LOI: {{ $respondent->actual_loi ?? 'Not set' }}</p>
                    <p>Client decision: {{ $respondent['client_end_status'] ? 'Yes' : 'No' }}</p>
                    <p>Client denial: {{ $respondent['client_denial'] ? 'Yes' : 'No' }}</p>
                </td>
                <td>Action</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @php($queryStrings = [])
    @foreach (request()->query() as $key => $param)
        @php($queryStrings[$key] = $param)
    @endforeach
    {{ $respondents->appends($queryStrings)->links() }}
@endsection
