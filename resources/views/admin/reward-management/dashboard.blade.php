@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Dashboard'])

    <table class="table table-hover">
        <thead>
        <tr>
            <th>Country</th>
            <th>Payout methods</th>
        </tr>
        </thead>
        <tbody>
        @foreach($payoutMethodsByCountry as $countryConfig)
            <tr>
                <td>{{ $countryConfig['name'] }}</td>
                <td>
                    @foreach($countryConfig['methods'] as $method)
                        <div class="card mb-1">
                            <div class="card-header">
                                {{ $method['method'] }}
                            </div>
                            <div class="card-body">
                                <p>Provider: {{ $method['provider'] }}</p>
                                <p>Active: {{ $method['is_active'] ? 'Yes' : 'No' }}</p>
                                <p>Minimal threshold: {{ number_format($method['minimal_threshold'], 2) }} USD</p>
                                <p>Maximum amount: {{ $method['maximum_amount'] ? number_format($method['maximum_amount'], 2) . ' USD' : 'See provider' }}</p>
                            </div>
                        </div>
                    @endforeach

                    @empty($countryConfig['methods'])
                        No method available
                    @endempty
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
