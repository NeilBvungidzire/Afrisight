@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.account-quality.header', ['header' => 'Bank account blacklist'])

    <div class="row">
        <div class="col-12">
            <h2 class="h5">
                Blacklist
                <a class="btn btn-primary" href="{{ route('admin.account-quality.bank-account-blacklist.search') }}">Search cases</a>
                <a class="btn btn-primary" href="{{ route('admin.account-quality.bank-account-blacklist.find-possible-cases') }}">Scan possible cases</a>
            </h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Related data</th>
                    <th>Banned person IDs</th>
                    <th>Initiator</th>
                </tr>
                </thead>
                <tbody>
                @foreach($blackists as $blackist)
                    <tr>
                        <td>
                            @foreach(\Illuminate\Support\Arr::dot($blackist->related_data ?? []) as $key => $value)
                                <span><strong>{{ $key }}:</strong> {{ $value }}</span>
                            @endforeach
                        </td>
                        <td>{{ implode(', ', (array)$blackist->banned_person_ids) }}</td>
                        <td>{{ $blackist->initiator }}</td>
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
                <a href="{{ route('admin.account-quality.bank-account-blacklist.index', array_merge(request()->query->all(), ['limit' => $limit])) }}"
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

            {{-- Paginator --}}
            {{ $blackists->appends($queryStrings)->links() }}
        </div>
    </div>
@endsection
