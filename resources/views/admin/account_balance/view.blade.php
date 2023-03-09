@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.reward-management.header', ['header' => 'Person balance'])

    <div class="row">
        <div class="col-12">
            <dl class="row">
                <dt class="col-3">ID</dt>
                <dd class="col-9">{{ $person->id }}</dd>

                <dt class="col-3">Name</dt>
                <dd class="col-9">{{ $person->first_name }} {{ $person->last_name }}</dd>

                <dt class="col-3">Email</dt>
                <dd class="col-9">{{ $person->email }}
                    ({{ ($person->user && $person->user->email_verified_at) ? 'verified' : 'not verified' }})
                </dd>

                <dt class="col-3">Mobile number</dt>
                <dd class="col-9">{{ $person->mobile_number }}</dd>

                <dt class="col-3">Gender</dt>
                <dd class="col-9">{{ $person->gender_code }}</dd>

                <dt class="col-3">Date of Birth</dt>
                <dd class="col-9">{{ $person->date_of_birth }}</dd>

                <dt class="col-3">Language</dt>
                <dd class="col-9">{{ $person->language_code }}</dd>

                <dt class="col-3">Country</dt>
                <dd class="col-9">{{ isset($countries[$person->country_id]) ? $countries[$person->country_id] : '' }}</dd>
            </dl>
        </div>
        <div class="col-12">
            <h2 class="mt-5 mb-3">Overview transactions and balance</h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Transaction Type</th>
                    <th>Status</th>
                    <th>Details</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($person->transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->id }}</td>
                        <td>{{ $transaction->updated_at }}</td>
                        <td>{{ $transaction->type }}</td>
                        <td>{{ $transaction->status }}</td>
                        <td>
                            @if($transaction->project_code ?? null)
                                <span class="badge badge-info">Project Code: {{ $transaction->project_code }}</span>
                            @endif
                            @if($transaction->meta_data['payout_method'] ?? null)
                                <span class="badge badge-info">Payment Methode: {{ $transaction->meta_data['payout_method'] }}</span>
                            @endif
                            @if($transaction->meta_data['provider'] ?? null)
                                <span class="badge badge-info">Payment Provider: {{ $transaction->meta_data['provider'] }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($transaction->amount, 2) }} USD</td>
                    </tr>
                @endforeach
                <tr class="font-italic">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Cint balance</td>
                    <td>{{ number_format($person->cintBalance, 2) }} USD</td>
                </tr>
                <tr class="font-weight-bold">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Total balance</td>
                    <td>{{ number_format($person->calculatedBalance, 2) }} USD</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="col-12">
            <h2 class="mt-5 mb-3">Engaged for surveys</h2>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Project Code</th>
                    <th>End result</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($person->respondent as $respondent)
                    <tr>
                        <td>{{ $respondent->updated_at }}</td>
                        <td>{{ $respondent->project_code }}</td>
                        <td>{{ $respondent->current_status }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
