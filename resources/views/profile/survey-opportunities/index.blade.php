@extends('layouts.profile')

@section('title', __('profile.sub_pages.survey_opportunities.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3 px-4">{{ __('profile.sub_pages.survey_opportunities.heading') }}</h1>

    <div class="bg-light my-3 py-3 px-4">
        @if ( ! $profileComplete)
            <p>{{ __('profile.sub_pages.survey_opportunities.notification.line_1') }}</p>
            <p>{{ __('profile.sub_pages.survey_opportunities.notification.line_2') }}</p>
        @else
            <h2 class="h5 pb-3">{{ __('profile.sub_pages.survey_opportunities.opportunities.heading') }}</h2>

            @if (empty($ownSurveyOpportunities) && empty($cintSurveyOpportunities))
                <p>{{ __('profile.sub_pages.survey_opportunities.opportunities.no_survey') }}</p>
            @else
                <table class="table table-striped table-bordered table-sm">
                    <thead class="thead-dark">
                    <tr>
                        <th>{{ __('profile.sub_pages.survey_opportunities.opportunities.column_loi') }}</th>
                        <th>{{ __('profile.sub_pages.survey_opportunities.opportunities.column_incentive') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    {{-- Own --}}
                    @foreach($ownSurveyOpportunities as $survey)
                        <tr>
                            <td>{{ $survey['loi'] }}</td>
                            <td>{{ number_format($survey['usd_amount'], 2) }} USD ({{ number_format($survey['local_amount'], 2) }} {{ $survey['local_currency'] }})</td>
                            <td>
                                <a href="{{ $survey['url'] }}" class="btn btn-primary btn-sm btn-block">
                                    {{ __('profile.sub_pages.survey_opportunities.opportunities.action_start') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Cint --}}
                    @foreach($cintSurveyOpportunities as $survey)
                        <tr>
                            <td>{{ $survey['length_of_interview'] }}</td>
                            <td>{{ number_format($survey['incentive']['amount'], 2) }} {{ $survey['incentive']['currency'] }}</td>
                            <td>
                                <a href="{{ $survey['survey_link'] }}" target="_blank"
                                   class="btn btn-primary btn-sm btn-block">
                                    {{ __('profile.sub_pages.survey_opportunities.opportunities.action_start') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
@endsection
