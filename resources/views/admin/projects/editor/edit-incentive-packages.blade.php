@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Manage Incentive Packages' . ' - ' . config('app.name'))

@section('content')
    <h1>Incentive Management <span class="badge badge-info">{{ $project->code }}</span></h1>

    <div class="card my-3">
        <div class="card-body">
            <p class="font-weight-bold">Existing incentive packages</p>

            @empty($project->incentive_packages)
                Empty
            @else
                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>LOI</th>
                        <th>USD amount</th>
                        <th>Local currency</th>
                        <th>Local amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($project->incentive_packages as $packageId => $packageParams)
                        <tr>
                            <td>{{ $packageId }}</td>
                            <td>{{ $packageParams['loi'] }}</td>
                            <td>{{ $packageParams['usd_amount'] }}</td>
                            <td>{{ $packageParams['local_currency'] }}</td>
                            <td>{{ $packageParams['local_amount'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endempty
        </div>
    </div>

    <div class="card my-3">
        <div class="card-body">
            <p class="font-weight-bold">Incentive package allocation</p>

            @empty($project->incentive_packages)
                No package exists to allocate. First create minimal one package
            @else
                <form action="{{ route('admin.projects.allocate-incentive-packages', ['id' => $project->id]) }}"
                      method="post">
                    @csrf
                    @method('put')

                    {{-- Package allocation --}}
                    @foreach($incentiveAllocationChannels as $channel)
                        @php($attribute = 'incentive_package_allocation')
                        @php($value = old($attribute . '.' . $channel) ?? $project->incentive_package_allocation[$channel] ?? '')
                        <div class="form-group row">
                            <label class="col-sm-5 col-lg-4 col-form-label"
                                   for="{{ $attribute }}">{{ $channel }}</label>

                            <div class="col-sm-7 col-lg-8">
                                <select id="{{ $attribute }}.{{ $channel }}" name="{{ $attribute }}[{{ $channel }}]" autofocus
                                        class="form-control{{ $errors->has($attribute . '.' . $channel) ? ' is-invalid' : '' }}">
                                    @if($channel !== \App\Services\ProjectManagement\Constants\ProjectIncentiveAllocationOption::DEFAULT)
                                        <option value="">Use default</option>
                                    @endif
                                    @foreach($project->incentive_packages as $packageId => $packageParams)
                                        <option
                                            value="{{ $packageId }}" {{ ($value === $packageId) ? 'selected' : '' }}>
                                            {{ $packageId }} (LOI={{ $packageParams['loi'] }}, USD
                                            amount={{ $packageParams['usd_amount'] }})
                                        </option>
                                    @endforeach
                                </select>

                                @if($channel === \App\Services\ProjectManagement\Constants\ProjectIncentiveAllocationOption::DEFAULT)
                                    <small class="form-text text-muted">Make sure to select an incentive package as
                                        default!</small>
                                @endif

                                @if ($errors->has($attribute . '.' . $channel))
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first($attribute . '.' . $channel) }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group row">
                        <div class="col-sm-4 offset-sm-4 col-lg-3 offset-lg-6">
                            <a href="{{ route('admin.projects.index') }}"
                               class="btn btn-outline-secondary btn-block">Cancel</a>
                        </div>
                        <div class="col-sm-4 col-lg-3">
                            <button type="submit"
                                    class="btn btn-primary btn-block"{{ empty($project->incentive_packages) ? ' disabled' : '' }}>
                                Allocate
                            </button>
                        </div>
                    </div>
                </form>
            @endempty
        </div>
    </div>

    <div class="card my-3">
        <div class="card-body">
            <p class="font-weight-bold">New incentive package</p>

            <form action="{{ route('admin.projects.manage-incentive-packages', ['id' => $project->id]) }}"
                  method="post">
                @csrf
                @method('put')

                {{-- LOI --}}
                @php($attribute = 'loi')
                @php($value = old($attribute) ?? '')
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">LOI</label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="number" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                               id="{{ $attribute }}" name="{{ $attribute }}" required value="{{ $value }}">

                        @if ($errors->has($attribute))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($attribute) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Base amount --}}
                @php($attribute = 'usd_amount')
                @php($value = old($attribute) ?? '')
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">USD amount</label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="number" step=".01"
                               class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                               id="{{ $attribute }}" name="{{ $attribute }}" required value="{{ $value }}">

                        @if ($errors->has($attribute))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($attribute) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Local currency --}}
                @php($attribute = 'local_currency')
                @php($value = old($attribute) ?? '')
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Local currency</label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="text" class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                               id="{{ $attribute }}" name="{{ $attribute }}" required value="{{ $value }}">

                        @if ($errors->has($attribute))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($attribute) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Local amount --}}
                @php($attribute = 'local_amount')
                @php($value = old($attribute) ?? '')
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $attribute }}">Local amount</label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="number" step=".01"
                               class="form-control{{ $errors->has($attribute) ? ' is-invalid' : '' }}"
                               id="{{ $attribute }}" name="{{ $attribute }}" required value="{{ $value }}">

                        @if ($errors->has($attribute))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($attribute) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 offset-sm-4 col-lg-3 offset-lg-6">
                        <a href="{{ route('admin.projects.index') }}"
                           class="btn btn-outline-secondary btn-block">Cancel</a>
                    </div>
                    <div class="col-sm-4 col-lg-3">
                        <button type="submit" class="btn btn-primary btn-block">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
