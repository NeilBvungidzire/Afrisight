@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Referral management')

@section('content')
    <div class="row">
        <div class="col-8 d-flex align-items-center">
            <h1>Referrer Overview</h1>
        </div>
        <div class="col-2 d-flex align-items-center">
            <a href="{{ route('admin.referral_management.overview') }}"
               class="btn btn-block btn-outline-primary">
                Referral Management
            </a>
        </div>
        <div class="col-2 d-flex align-items-center">
            <a href="{{ route('admin.referral_management.create_referrer') }}"
               class="btn btn-block btn-primary">
                Create External Referrer
            </a>
        </div>
    </div>

    @alert

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.referral_management.overview_referrer') }}" method="post">
                @csrf

                <div class="form-row align-items-center">
                    <div class="col-auto my-1">
                        <p class="form-control-plaintext mr-sm-2 text-muted">
                            Found: {{ number_format($referrers->total()) }}</p>
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
                        <a href="{{ route('admin.referral_management.overview_referrer') }}"
                           class="btn btn-outline-primary px-5">Reset</a>
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
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($referrers as $referrer)
                    <tr>
                        <td>{{ $referrer->id }}</td>
                        <td>{{ $referrer->name }}</td>
                        <td>
                            <a href="{{ route('admin.referral_management.view_referrer', ['id' => $referrer->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                View
                            </a>
                            <a href="{{ route('admin.referral_management.edit_referrer', ['id' => $referrer->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                Edit
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
                <a href="{{ route('admin.referral_management.overview', array_merge(request()->query->all(), ['limit' => $limit])) }}"
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
            {{ $referrers->appends($queryStrings)->links() }}
        </div>
    </div>
@endsection
