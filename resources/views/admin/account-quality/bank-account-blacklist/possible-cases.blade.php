@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.account-quality.header', ['header' => 'Bank account blacklist'])

    <div class="row">
        <div class="col-12">
            <h2 class="h5">
                Blacklist
                <a class="btn btn-primary" href="{{ route('admin.account-quality.bank-account-blacklist.search') }}">
                    Search cases
                </a>
                <a class="btn btn-primary"
                   href="{{ route('admin.account-quality.bank-account-blacklist.find-possible-cases') }}">
                    Scan possible cases
                </a>
            </h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Country code</th>
                    <th>Bank code</th>
                    <th>Account number</th>
                    <th>Cases</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($possibleCases as $possibleCase)
                    <tr>
                        <td>{{ $possibleCase->country_code }}</td>
                        <td>{{ $possibleCase->bank_code }}</td>
                        <td>{{ $possibleCase->account_number }}</td>
                        <td>{{ $possibleCase->cases }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.account-quality.bank-account-blacklist.search', ['country_code' => $possibleCase->country_code, 'bank_code' => $possibleCase->bank_code, 'account_number' => $possibleCase->account_number]) }}"
                               class="btn btn-sm btn-outline-danger">
                                Show possible panelist accounts
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
