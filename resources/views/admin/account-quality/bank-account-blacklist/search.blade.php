@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.account-quality.header', ['header' => 'Bank account blacklist'])

    @alert()

    <div class="row">
        <div class="col-12">
            <p class="text-danger bg-light p-3">When an account is banned, the account will be deleted from our platform
                and added to blacklist, which
                will deny any account being created with the same email address. Please make sure to check before
                clicking on this button!</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.account-quality.bank-account-blacklist.search') }}" method="post">
                @csrf

                <div class="form-row align-items-center">
                    <div class="col-auto my-1">
                        <p class="form-control-plaintext mr-sm-2 text-muted">
                            Found: {{ number_format($persons->total()) }}</p>
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
                                <input type="text" class="form-control mr-sm-2" id="{{ $key }}" name="{{ $key }}"
                                       size="30"
                                       placeholder="{{ $filter['label'] }}" value="{{ $filter['current_value'] }}">
                            @endif
                        </div>
                    @endforeach
                    <div class="col-auto my-1">
                        <button type="submit" class="btn btn-primary px-5">Filter</button>
                    </div>
                    <div class="col-auto my-1">
                        <a href="{{ route('admin.account-quality.bank-account-blacklist.search') }}"
                           class="btn btn-outline-primary px-5">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h2 class="h5">Found persons</h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Person ID</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($persons as $person)
                    <tr>
                        <td>
                            @if ($person->deleted_at)
                                <del>{{ $person->id }}</del>
                            @else
                                {{ $person->id }}
                            @endif
                        </td>
                        <td>{{ $person->email }}</td>
                        <td>{{ $person->first_name }} {{ $person->last_name }}</td>
                        <td>{{ $countries[$person->country_id] ?? 'Could not find (most times, not yet set)' }}</td>
                        @foreach($person->bankAccounts as $account)
                            <td class="text-nowrap">
                                <a href="{{ route('admin.account-quality.bank-account-blacklist.ban', array_merge(request()->query->all(), ['id' => $person->id], [
                                    'country_code' => $account->country_code,
                                    'bank_code' => $account->bank_code,
                                    'account_number' => $account->account_number,
    ]                           )) }}"
                                   class="btn btn-sm btn-outline-danger">
                                    Ban by bank
                                </a>
                            </td>
                        @endforeach
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
                <a href="{{ route('admin.account-quality.bank-account-blacklist.search', array_merge(request()->query->all(), ['limit' => $limit])) }}"
                   class="btn btn-outline-secondary">
                    {{ $limit }}
                </a>
            @endforeach
        </div>
        <div class="col-auto">
            @php($queryStrings = [])

            {{-- Transaction filters --}}
            @php($queryStringParams = ['limit', 'country_code','bank_code','account_number'])
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
