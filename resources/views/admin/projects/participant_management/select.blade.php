@extends('admin.projects.layout')

@section('inner-content')
    <h2>Found participants <span class="badge badge-info">Found: {{ $respondents->total() }}</span></h2>

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.projects.manage_participants.select', ['project_code' => $projectCode]) }}"
                  method="post" class="form">
                @csrf

                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Select</th>
                        <th>Respondent ID / Person Id</th>
                        <th>UUID</th>
                        <th>Status</th>
                        <th>Additional data</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($respondents as $respondent)
                        <tr onclick="toggleRowCheckbox(this)">
                            <td>
                                <div class="form-group form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="respondent_id[]"
                                           value="{{ $respondent->id }}">
                                </div>
                            </td>
                            <td>{{ $respondent->id }} / {{ $respondent->person_id }}</td>
                            <td>{{ $respondent->uuid }}</td>
                            <td>{{ $respondent->current_status }}</td>
                            <td>
                                @foreach ((array)$respondent->meta_data as $label => $value)
                                    <span class="badge badge-light">{{ $label }}: {{ json_encode($value) }}</span>
                                @endforeach
                                @if (isset($respondent->transaction))
                                    <hr>
                                    <span class="badge badge-light">Amount: {{ $respondent->transaction->amount }}</span>
                                    <span class="badge badge-light">Initiator: {{ $respondent->transaction->initiator }}</span>
                                    <span class="badge badge-light">Status: {{ $respondent->transaction->status }}</span>
                                    <span class="badge badge-light">
                                Balance adjusted: {{ $respondent->transaction->balance_adjusted ? 'Yes' : 'No' }}
                            </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="form-group row">
                    <div class="col-12">
                        <hr>
                    </div>
                </div>

                @php
                    $fieldName = 'action';
                    $value = old($fieldName) ?? '';
                @endphp
                <div class="form-group row">
                    <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                        Action
                    </label>

                    <div class="col-sm-7 col-lg-8">
                        <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                                class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                            <option value="">Choose action</option>
                            @foreach($actions as $action => $label)
                                <option value="{{ $action }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-sm-3 col-md-2 offset-sm-6 offset-md-8">
                        <button type="button" class="btn btn-secondary btn-block" onclick="toggle()">Switch selection</button>
                    </div>
                    <div class="col-sm-3 col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Handle action</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-auto">
            <span>Items per page: </span>
            @php($limitRanges = [25, 50, 100, 250])
            @foreach ($limitRanges as $limit)
                <a href="{{ route('admin.projects.manage_participants.select', array_merge(['project_code' => $projectCode], request()->query->all(), ['limit' => $limit])) }}"
                   class="btn btn-outline-secondary">
                    {{ $limit }}
                </a>
            @endforeach
        </div>
        <div class="col-auto">
            @php($queryStrings = [])

            {{-- Transaction filters --}}
            @php($queryStringParams = ['limit'])
            @foreach ($queryStringParams as $param)
                @php($queryStrings[$param] = request()->query($param))
            @endforeach

            {{-- Paginator --}}
            {{ $respondents->appends($queryStrings)->links() }}
        </div>
    </div>

    <script>
        function toggle () {
            checkboxes = document.getElementsByName('respondent_id[]');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = checkboxes[i].checked !== true;
            }
        }

        function toggleRowCheckbox (row) {
            var checkbox = row.querySelector('td input[type="checkbox"]');

            if (checkbox) {
                checkbox.checked = checkbox.checked !== true;
            }
        }
    </script>
@endsection
