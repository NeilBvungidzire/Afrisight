@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Balance'])

    @alert

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.reward_management.balance.filter') }}" method="post">
                @csrf

                <div class="form-row align-items-center">
                    <div class="col-auto my-1">
                        <p class="form-control-plaintext mr-sm-2 text-muted">Found: {{ number_format($persons->total()) }}</p>
                    </div>

                    @foreach ($filters as $key => $filter)
                        <div class="col-auto my-1">
                            <label class="sr-only" for="{{ $key }}">{{ $key }}</label>
                            @if ($filter['type'] === 'select')
                                <select class="custom-select mr-sm-2" id="{{ $key }}" name="{{ $key }}">
                                    <option value="">{{ $filter['label'] }}</option>
                                    @foreach ($filter['options'] as $value => $label)
                                        <option
                                            value="{{ $value }}" {{ $filter['current_value'] == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif ($filter['type'] === 'text')
                                <input type="text" class="form-control mr-sm-2" id="{{ $key }}" name="{{ $key }}" size="30"
                                       placeholder="{{ $filter['label'] }}" value="{{ $filter['current_value'] }}">
                            @endif
                        </div>
                    @endforeach
                    <div class="col-auto my-1">
                        <button type="submit" class="btn btn-primary px-5">Filter</button>
                    </div>
                    <div class="col-auto my-1">
                        <a href="{{ route('admin.reward_management.balance') }}" class="btn btn-outline-primary px-5">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Person ID</th>
                    <th>Country</th>
                    <th>Email</th>
                    <th>Actual amount (USD)</th>
                    <th>Calculated amount (USD)</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($persons as $person)
                    <tr>
                        <td>
                            @if ($person->deleted_at)
                                <del>
                            @endif
                            {{ $person->id }}
                            @if ($person->deleted_at)
                                </del>
                            @endif
                        </td>
                        <td>{{ isset($countries[$person->country_id]) ? $countries[$person->country_id] : '' }}</td>
                        <td>{{ $person->email }}</td>
                        <td>{{ number_format($person->reward_balance, 2) }}</td>
                        <td>{{ number_format($person->calculatedBalance, 2) }}</td>
                        <td>
                            <a href="{{ route('admin.account_balance.view', ['personId' => $person->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                Person account
                            </a>
                            <a href="{{ route('admin.reward_management.sync_cint_balance', ['personId' => $person->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                Sync Cint balance
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-auto">
            <span>Items per page: </span>
            @php($limitRanges = [25, 50, 100, 250])
            @foreach ($limitRanges as $limit)
                <a href="{{ route('admin.reward_management.balance', array_merge(request()->query->all(), ['limit' => $limit])) }}"
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

            {{-- Transaction filters --}}
            @foreach ($filters as $key => $filter)
                @php($queryStrings[$key] = request()->query($key))
            @endforeach

            {{-- Paginator --}}
            {{ $persons->appends($queryStrings)->links() }}
        </div>
    </div>
@endsection
