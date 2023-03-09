@extends('layouts.website')

@section('title', ('CAPI project' . ' - ' . 'AfriSight'))

@section('content')
    <div class="container py-3">
        <h1>CAPI project (current status: {{ $status }})</h1>
        <p>Sample Code: {{ $projectCode }}</p>

        {{-- Live results --}}
        <div class="card">
            <div class="card-header bg-primary">LIVE</div>
            <div class="card-body">
                <h2 class="h4">Total stats</h2>
                <p>Total completes: {{ number_format($generalStatsLive->total_completes) }}</p>
                <p>Average LOI: {{ round($generalStatsLive->average_loi) }} minutes</p>

                <hr class="my-5">

                <h2 class="h4">Enumerators stats</h2>
                @isset($unknownInterviewerLive)
                    <p class="mt-5 lead">Unknown Enumerator</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Average LOI</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($unknownInterviewerLive as $statusStats)
                            <tr>
                                <td>{{ $statusStats->status }}</td>
                                <td>{{ number_format($statusStats->count) }}</td>
                                <td>{{ round($statusStats->average_loi) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endisset

                @foreach($interviewerStatsLive as $interviewerId => $interviewerStat)
                    <p class="mt-4 lead">Enumerator ID: {{ $interviewerId }}</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Average LOI</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($interviewerStat as $statusStats)
                            <tr>
                                <td>{{ $statusStats->status }}</td>
                                <td>{{ number_format($statusStats->count) }}</td>
                                <td>{{ round($statusStats->average_loi) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>

        <hr/>

        {{-- Test results --}}
        <div class="card">
            <div class="card-header bg-warning">TEST</div>
            <div class="card-body">
                <h2 class="h4">Total stats</h2>
                <p>Total completes: {{ number_format($generalStatsTest->total_completes) }}</p>
                <p>Average LOI: {{ round($generalStatsTest->average_loi) }} minutes</p>

                <hr class="my-5">

                <h2 class="h4">Enumerators stats</h2>
                @isset($unknownInterviewerTest)
                    <p class="mt-5 lead">Unknown Enumerator</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Average LOI</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($unknownInterviewerTest as $statusStats)
                            <tr>
                                <td>{{ $statusStats->status }}</td>
                                <td>{{ number_format($statusStats->count) }}</td>
                                <td>{{ round($statusStats->average_loi) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endisset

                @foreach($interviewerStatsTest as $interviewerId => $interviewerStat)
                    <p class="mt-4 lead">Enumerator ID: {{ $interviewerId }}</p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Average LOI</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($interviewerStat as $statusStats)
                            <tr>
                                <td>{{ $statusStats->status }}</td>
                                <td>{{ number_format($statusStats->count) }}</td>
                                <td>{{ round($statusStats->average_loi) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>
    </div>
@endsection
