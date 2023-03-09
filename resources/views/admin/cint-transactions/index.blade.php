@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Cint transactions'])

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.cint.transactions.filter') }}" method="post">
                @csrf

                <div class="form-row align-items-center">
                    <div class="col-auto my-1">
                        <p class="form-control-plaintext mr-sm-2 text-muted">Found: {{ number_format($transactions->total()) }}</p>
                    </div>

                    @foreach ($filters as $key => $filter)
                        <div class="col-auto my-1">
                            <label class="sr-only" for="{{ $key }}">{{ $key }}</label>
                            @if ($filter['type'] === 'select')
                                <select class="custom-select mr-sm-2" id="{{ $key }}" name="{{ $key }}">
                                    <option value="">{{ $filter['label'] }}</option>
                                    @foreach ($filter['options'] as $value => $label)
                                        <option
                                            value="{{ $value }}" {{ $filter['current_value'] === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                        <a href="{{ route('admin.cint.transactions') }}" class="btn btn-outline-primary px-5">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h2 class="h5">Found transactions</h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Person ID</th>
                    <th>Date</th>
                    <th>Amount (USD)</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Balance adjusted</th>
                    <th>Meta data</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->person_id }}</td>
                        <td>{{ $transaction->updated_at->format('d-m-Y') }}</td>
                        <td>{{ number_format($transaction->amount, 2) }}</td>
                        <td>{{ $transaction->type }}</td>
                        <td>{{ $transaction->status }}</td>
                        <td>{{ $transaction->balance_adjusted ? 'Yes' : 'No' }}</td>
                        <td>
                            @foreach ((array)$transaction->meta_data as $label => $value)
                                <span>{{ $label }}: {{ json_encode($value) }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('admin.cint.transactions.edit', ['id' => $transaction->id]) }}"
                               class="btn btn-outline-info btn-sm">Edit</a>
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
                <a href="{{ route('admin.cint.transactions', array_merge(request()->query->all(), ['limit' => $limit])) }}"
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
            {{ $transactions->appends($queryStrings)->links() }}
        </div>
    </div>
@endsection
