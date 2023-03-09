@extends('admin.projects.layout')

@push('js-head')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('inner-content')
    <div class="text-right">
        @can('manage-projects')
            <a href="{{ route('admin.projects.target_track.edit_quotas', ['project_code' => $projectCode]) }}"
               class="btn btn-outline-info btn-sm">
                Edit quotas limit
            </a>
        @endcan

        @can('admin-projects')
            <a href="{{ route('admin.projects.target_track.generate', ['project_code' => $projectCode]) }}"
               class="btn btn-outline-danger btn-sm">
                Regenerate targets
            </a>
            <a href="{{ route('admin.projects.target_track.recount_completes', ['project_code' => $projectCode]) }}"
               class="btn btn-outline-danger btn-sm">
                Recount completes
            </a>
        @endcan
    </div>

    <p>
        <span class="badge badge-info">Total completes: {{ $stats['counts_per_status'][\App\Constants\RespondentStatus::COMPLETED] ?? 0 }}</span>

        <span class="">|</span>
        <span class="badge badge-light">IR: {{ $stats['ir'] ? number_format(($stats['ir'] * 100), 0) . '%' : '?' }}</span>
        <span class="badge badge-light">
            Average LOI: {{ $stats['loi']['average'] ? number_format($stats['loi']['average'], 0) : '?' }}
            (Q1: {{ $stats['loi']['min_loi'] ?? '?' }}, Q3: {{ $stats['loi']['max_loi'] ?? '?' }})
        </span>
        <span class="badge badge-light">Potential outliers: {{ $stats['loi']['outliers'] ?? '?' }}</span>
    </p>

    <table class="table table-sm table-bordered table-hover">
        <thead>
        <tr>
            <th>Quota</th>
            <th class="text-center">Total</th>
            <th class="text-center">Completion</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($quotas as $quota)
            <tr class="{{ $quota['count'] >= $quota['quota'] ? 'text-info' : '' }}">
                <td>{{ $quota['label'] }}</td>
                <td class="text-center">{{ $quota['count'] }} ({{ $quota['quota'] }})</td>
                <td class="text-center">
                    @if ($quota['quota'])
                        {{ number_format(($quota['count'] / $quota['quota']) * 100) }}%
                    @else
                        0%
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr>
    <table class="table table-sm table-bordered table-hover">
        <thead>
        <tr>
            <th>Status</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @isset($stats['counts_per_status'])
            @foreach($stats['counts_per_status'] as $status => $count)
                <tr>
                    <td>{{ $status }}</td>
                    <td>{{ number_format($count, 0) }}</td>
                </tr>
            @endforeach
        @endisset
        </tbody>
    </table>

    <div>
        <canvas id="myChart"></canvas>
    </div>

    <script>
        const countsPerStatusPerDate = {!! json_encode($stats['counts_per_status_per_date'] ?? []) !!};
        const countsPerDate = {!! json_encode($stats['counts_per_date'] ?? []) !!};
        const datasets = prepareDatasets(countsPerStatusPerDate, countsPerDate);

        function prepareDatasets(countsPerStatusPerDate, countsPerDate) {
            const defaultShow = ['COMPLETED'];
            const result = [];
            for (const key in countsPerStatusPerDate) {
                const dynamicColors = function() {
                    const r = Math.floor(Math.random() * 255);
                    const g = Math.floor(Math.random() * 255);
                    const b = Math.floor(Math.random() * 255);

                    return "rgb(" + r + "," + g + "," + b + ")";
                };

                let hidden = true;
                if (defaultShow.includes(key)) {
                    hidden = false;
                }

                result.push({
                    label: key,
                    backgroundColor: dynamicColors,
                    borderColor: dynamicColors,
                    data: countsPerStatusPerDate[key],
                    hidden,
                });
            }

            result.push({
                label: 'Engaged',
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: countsPerDate,
            });

            return result;
        }

        const data = {
            labels: {!! json_encode($stats['field_dates'] ?? []) !!},
            datasets: datasets
        };
        const config = {
            type: 'line',
            data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Progress'
                    }
                }
            }
        };
        new Chart(
            document.getElementById('myChart'),
            config
        );
    </script>
@endsection
