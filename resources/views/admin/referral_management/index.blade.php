@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Referral management')

@section('content')
    <div class="row">
        <div class="col-6 d-flex align-items-center">
            <h1>Referral management</h1>
        </div>
        <div class="col-2 d-flex align-items-center">
            <a href="{{ route('admin.referral_management.overview_referrer') }}"
               class="btn btn-block btn-outline-primary">
                Overview External Referrers
            </a>
        </div>
        <div class="col-2 d-flex align-items-center">
            <a href="{{ route('admin.referral_management.create_referral', ['type' => \App\Constants\ReferralType::RESPONDENT_RECRUITMENT]) }}"
               class="btn btn-block btn-primary">
                Create Inflow Referral
            </a>
        </div>
        <div class="col-2 d-flex align-items-center">
            <a href="{{ route('admin.referral_management.recount_all_referral') }}"
               class="btn btn-block btn-primary">
                Recount all referrals!
            </a>
        </div>
    </div>

    @alert

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.referral_management.overview') }}" method="post">
                @csrf

                <div class="form-row align-items-center">
                    <div class="col-auto my-1">
                        <p class="form-control-plaintext mr-sm-2 text-muted">
                            Found: {{ number_format($referrals->total()) }}</p>
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
                        <a href="{{ route('admin.referral_management.overview') }}"
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
                    <th>Referral Code / Link</th>
                    <th>Type (project code)</th>
                    <th>Referrer</th>
                    <th>Amount / conversion</th>
                    <th>Total referred / total converted</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($referrals as $referral)
                    <tr>
                        <td>
                            <a href="{{ route('inflow', ['projectId' => $referral->code]) }}">{{ $referral->code }}</a>
                        </td>
                        <td>{{ $referral->type ?? null }} ({{ $referral->data['project_code'] ?? null }})</td>
                        <td>{{ $referral->referrerable_type ? $referrerTypes[$referral->referrerable_type] ? $referrerTypes[$referral->referrerable_type] . ' (ID: ' . $referral->referrerable_id . ')' : 'Not set' : 'Not set' }}</td>
                        <td>USD {{ number_format($referral->amount_per_successful_referral, 2) }}</td>
                        <td>{{ $referral->total_referrals ?? 0 }} / {{ $referral->total_successful_referrals ?? 0 }}</td>
                        <td>
                            <a href="{{ route('admin.referral_management.recount_referral', ['id' => $referral->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                Recount
                            </a>
                            <a href="{{ route('admin.referral_management.view_referral', ['id' => $referral->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                View
                            </a>
                            <a href="{{ route('admin.referral_management.edit_referral', ['id' => $referral->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                Edit
                            </a>
                            <a href="{{ route('admin.referral_management.handle_referral_transactions', ['id' => $referral->id]) }}"
                               class="btn btn-sm btn-outline-info">
                                Transactions
                            </a>
                            <span>|</span>
                            <a href="{{ route('admin.referral_management.engagement.draft', ['id' => $referral->id, 'channel' => 'sms']) }}"
                               class="btn btn-sm btn-outline-info">
                                SMS ({{ $referral->data['SMS'] ?? 0 }})
                            </a>
                            @if($referral->referrerable_type && $referral->referrerable_id)
                                <span>|</span>
                                <a href="{{ URL::signedRoute('referral_management.referrer.overview', ['locale' => app()->getLocale(), 'id' => encrypt(['locale' => app()->getLocale(), 'type' => $referral->referrerable_type, 'id' => $referral->referrerable_id])]) }}"
                                   class="btn btn-sm btn-outline-info">
                                    Referrer Dashboard
                                </a>
                            @endif
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
            @php($limitRanges = [10, 25, 50, 100, 250])
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
            {{ $referrals->appends($queryStrings)->links() }}
        </div>
    </div>
@endsection
