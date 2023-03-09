@extends('admin.projects.layout')

@section('inner-content')
    <p>
        <span class="text-muted">Filter:</span>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'exclude-respondents' => '1'])) }}">
            Exclude engaged persons
        </a>

        <a class="btn btn-sm btn-outline-info ml-2"
           href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'use-exclusion' => '1'])) }}">
            Use exclusion
        </a>

        <a class="btn btn-sm btn-outline-info ml-2"
           href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'sms' => '1'])) }}">
            Mobile number required
        </a>

        {{-- Targets --}}
        @foreach ($targets as $criteria => $target)
            <span class="text-muted ml-2">{{ $criteria }}:</span>

            @foreach ($target as $targetItem)
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, $criteria => $targetItem->value])) }}">
                    {{ $targetItem->value }}
                </a>
            @endforeach
        @endforeach

        {{-- Respondent status --}}
        <span class="text-muted ml-2">Current status:</span>
        @php($statuses = ['SELECTED','RESELECTED','INVITED','STARTED','TARGET_UNSUITABLE','DISQUALIFIED','QUOTA_FULL'])
        @foreach ($statuses as $status)
            <a class="btn btn-sm btn-outline-info"
               href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'status' => $status])) }}">
                {{ $status }}
            </a>
        @endforeach

        {{-- Settings --}}
        <span class="text-muted ml-2">Order by:</span>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'order' => 'ASC'])) }}">
            Oldest
        </a>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'order' => 'DESC'])) }}">
            Newest
        </a>
        <a class="btn btn-sm btn-outline-info"
           href="{{ route('admin.projects.audience_selection', array_merge(request()->query(), ['projectCode' => $projectCode, 'order' => 'mixed'])) }}">
            Mixed
        </a>

        {{-- General --}}
        <a class="btn btn-sm btn-outline-info ml-5"
           href="{{ route('admin.projects.audience_selection', ['projectCode' => $projectCode]) }}">
            Clear
        </a>

        <span class="ml-3 text-muted">Total found:</span> <span>{{ number_format($persons->total()) }}</span>
    </p>

    <form action="{{ route('admin.projects.audience_selection', ['projectCode' => $projectCode]) }}" method="post">
        @csrf

        <table class="table table-hover">
            <thead>
            <tr>
                <th>Select</th>
                <th>Person</th>
                <th>Targets</th>
                <th>Respondent</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $data)
                <tr onclick="toggleRowCheckbox(this)">
                    <td>
                        <div class="form-group form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="person_id[]"
                                   value="{{ $data['id'] }}">
                        </div>
                    </td>

                    <td>
                        <p>ID: {{ $data['id'] }}</p>
                        <p>Email: {{ $data['email'] }}</p>
                        <p>Mobile Number: {{ $data['mobile_number'] }}</p>
                    </td>
                    <td>
                        <p>Age: {{ $data['age'] }}</p>
                        <p>Gender Code: {{ $data['gender_code'] }}</p>
                        @foreach ($data->dataPoints as $dataPoint)
                            <p>{{ $dataPoint->attribute }}: {{ $dataPoint->value }}</p>
                        @endforeach
                    </td>
                    <td>
                        @if ($data['respondent'])
                            <p>{{ $data['respondent']['current_status'] }} ({{ $data['respondent']['updated_at'] }})</p>
                            <p>{{{ json_encode($data['respondent']['meta_data']) }}}</p>

                            @if ($data['respondent']['invitations'])
                                @foreach ($data['respondent']['invitations'] as $invitation)
                                    <p class="badge badge-primary">{{ $invitation['type'] }}
                                        ({{ $invitation['status'] }}) - {{ $invitation['updated_at'] }}</p>
                                @endforeach
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div>
            Packages
            @foreach ($incentivePackages ?? [] as $packageId => $package)
                <p class="badge badge-info">
                    ID: {{ $packageId }} -
                    @foreach ($package as $paramKey => $paramValue)
                        <span class="">{{ $paramKey }}</span>: <span class="pr-2">{{ $paramValue }}</span>
                    @endforeach
                </p>
            @endforeach
        </div>
        <div class="form-group">
            <label for="package_id">Package ID</label>
            <input type="number" class="form-control" id="package_id" placeholder="1" required name="package_id">
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="type" value="email" id="email" required>
                <label class="form-check-label" for="email">
                    Email
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="type" value="sms" id="sms" required>
                <label class="form-check-label" for="sms">
                    SMS
                </label>
            </div>
        </div>

        <div class="form-group">
            <button type="button" class="btn btn-secondary" onclick="toggle()">Select/Unselect all</button>
            <button type="submit" class="btn btn-primary">Send invite</button>
        </div>
    </form>

    <hr>

    @php($queryStringParams = ['exclude-respondents', 'status', 'limit', 'sms', 'use-exclusion'])
    @php($queryStrings = [])
    @foreach ($queryStringParams as $param)
        @php($queryStrings[$param] = request()->query($param))
    @endforeach
    @foreach ($targets as $criteria => $value)
        @php($queryStrings[$criteria] = request()->query($criteria))
    @endforeach
    {{ $persons->appends($queryStrings)->links() }}
@endsection

<script>
    function toggle () {
        checkboxes = document.getElementsByName('person_id[]');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = checkboxes[i].checked !== true;
        }
    }

    function toggleRowCheckbox (row) {
        var checkbox = row.querySelector('input[type="checkbox"]');

        if (checkbox) {
            checkbox.checked = checkbox.checked !== true;
        }
    }
</script>
