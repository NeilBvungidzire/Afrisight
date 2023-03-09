@extends('layouts.profile')

@section('title', __('profile.sub_pages.rewards.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">{{ __('profile.sub_pages.rewards.heading') }}</h1>

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('profile.sub_pages.rewards.list.title') }}</h2>

        <table class="table table-sm mt-4">
            <thead>
            <tr>
                <th>{{ __('profile.sub_pages.rewards.list.date') }}</th>
                <th>{{ __('profile.sub_pages.rewards.list.type') }}</th>
                <th>{{ __('profile.sub_pages.rewards.list.amount') }}</th>
                <th>{{ __('profile.sub_pages.rewards.list.status') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($rewards as $reward)
                <tr>
                    <td>{{ $reward['date'] }}</td>
                    <td>{{ $reward['type'] }}</td>
                    <td>{{ number_format($reward['amount'], 2) }}</td>
                    <td>{{ $reward['status'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('profile.sub_pages.rewards.notes.title') }}</h2>

        <p class="small">
            <span class="font-weight-bold">{{ __('profile.sub_pages.rewards.notes.statuses.title') }}</span><br>
            <span>{{ __('profile.sub_pages.rewards.notes.statuses.approved') }}</span><br>
            <span>{{ __('profile.sub_pages.rewards.notes.statuses.pending') }}</span><br>
            <span>{{ __('profile.sub_pages.rewards.notes.statuses.denied') }}</span><br>
        </p>
    </div>
@endsection
