@extends('layouts.profiling')

@section('content')
    <h1>Results per survey</h1>
    <table class="table">
        <thead>
        <tr>
            <th></th>
            <th>Complete</th>
            <th>Incomplete</th>
            <th>Disqualified</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data as $surveyKey => $surveys)
            @if( ! in_array($surveyKey, ['salone_consumer','trenk_consumer','salone_customer','trenk_customer']))
                @continue
            @endif

            @php($surveyName = Str::title(str_replace('_', ' ', $surveyKey)))
            <tr>
                <td>{{ $surveyName }}</td>
                <td>{{ $surveys['totals']['complete'] }}</td>
                <td>{{ $surveys['totals']['incomplete'] }}</td>
                <td>{{ $surveys['totals']['disqualified'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @foreach ($data as $surveyKey => $surveys)
        @if( ! in_array($surveyKey, ['salone_consumer','trenk_consumer','salone_customer','trenk_customer']))
            @continue
        @endif

        <h1>{{ Str::title(str_replace('_', ' ', $surveyKey)) }}</h1>

        <p>Total survey results per day</p>
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Complete</th>
                <th>Incomplete</th>
                <th>Disqualified</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($surveys as $date => $surveyData)
                @continue($date === 'totals')

                <tr>
                    <td>{{ $date }}</td>
                    <td>{{ $surveyData['by_status']['complete'] }}</td>
                    <td>{{ $surveyData['by_status']['incomplete'] }}</td>
                    <td>{{ $surveyData['by_status']['disqualified'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach

    <h1>Breakdown per date</h1>

    <h2 class="mt-5">Per country</h2>
    <table class="table">
        <thead>
        <tr>
            <th></th>
            @foreach ($dates as $date)
                <th>{{ $date }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($data['by_country'] as $name => $byDates)
            <tr>
                <td>{{ $name }}</td>
                @foreach ($byDates as $amount)
                    <td>{{ $amount }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

{{--    <h2 class="mt-5">Per city</h2>--}}
{{--    <table class="table">--}}
{{--        <thead>--}}
{{--        <tr>--}}
{{--            <th></th>--}}
{{--            @foreach ($dates as $date)--}}
{{--                <th>{{ $date }}</th>--}}
{{--            @endforeach--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        @foreach ($data['by_city'] as $name => $byDates)--}}
{{--            <tr>--}}
{{--                <td>{{ $name }}</td>--}}
{{--                @foreach ($byDates as $amount)--}}
{{--                    <td>{{ $amount }}</td>--}}
{{--                @endforeach--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--    </table>--}}

    <h2 class="mt-5">Per device</h2>
    <table class="table">
        <thead>
        <tr>
            <th></th>
            @foreach ($dates as $date)
                <th>{{ $date }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($data['by_device'] as $name => $byDates)
            <tr>
                <td>{{ $name }}</td>
                @foreach ($byDates as $amount)
                    <td>{{ $amount }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
