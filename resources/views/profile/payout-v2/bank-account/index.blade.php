@extends('layouts.profile')

@section('title', 'Bank account' . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">
        {{ __('payout.person_bank_account.title') }}
    </h1>

    <div class="bg-light my-3 py-3 px-4{{ ! empty($bankAccountId) ? ' d-none' : '' }}">
        <h5>{{ __('payout.person_bank_account.available_bank_accounts.title') }}</h5>
        <p class="small">{{ __('payout.person_bank_account.available_bank_accounts.intro') }}</p>

        @if ( ! empty($bankAccounts))
            @foreach ($bankAccounts as $bankAccount)
                <div class="list-group my-3">
                    <div class="list-group-item list-group-item-action">
                        <div class="row">
                            <div class="col-12 col-md-7">
                                <h5 class="my-1">{{ $bankAccount['name'] }}</h5>
                            </div>
                            <div class="col-12 col-md-5">
                                <p class="my-1 text-md-right">{{ $bankAccount['account_number'] }}</p>
                            </div>
                        </div>
                        <div class="small mb-1">
                            @isset($bankAccount['meta_data']['first_name'])
                                <span>{{ $bankAccount['meta_data']['first_name'] }}</span>
                            @endisset
                            @isset($bankAccount['meta_data']['last_name'])
                                <span>{{ $bankAccount['meta_data']['last_name'] }}</span>,
                            @endisset
                            @isset($bankAccount['meta_data']['email'])
                                <span>{{ $bankAccount['meta_data']['email'] }}</span>,
                            @endisset
                            @isset($bankAccount['meta_data']['mobile_number'])
                                <span>{{ $bankAccount['meta_data']['mobile_number'] }}</span>,
                            @endisset
                            @isset($bankAccount['meta_data']['recipient_address'])
                                <span>{{ $bankAccount['meta_data']['recipient_address'] }}</span>
                            @endisset
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 my-1">
                                <form method="post"
                                      action="{{ route('profile.bank_account', ['account' => encrypt($bankAccount['id'])]) }}">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-warning btn-block btn-sm">
                                        {{ __('payout.person_bank_account.delete_cta') }}
                                    </button>
                                </form>
                            </div>
                            <div class="col-12 col-md-6 my-1">
                                <a class="btn btn-outline-info btn-block btn-sm"
                                   href="{{ route('profile.bank_account', ['account' => encrypt($bankAccount['id'])]) }}">
                                    {{ __('payout.person_bank_account.edit_cta') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="bg-light my-3 py-3 px-4">
        <h5>{{ __('payout.person_bank_account.edit_add_bank_account.title', ['type_label' => empty($bankAccountId) ? __('payout.person_bank_account.edit_add_bank_account.type_add_label') : __('payout.person_bank_account.edit_add_bank_account.type_edit_label')]) }}</h5>
        <p class="small">
            {{ __('payout.person_bank_account.edit_add_bank_account.intro') }}
        </p>

        <form method="POST" class="form"
              action="{{ route('profile.bank_account', ['account' => encrypt($bankAccountId)]) }}">
            @csrf

            @php
                $fieldName = 'first_name';
                $value = old($fieldName) ?? $bankAccountToEdit['meta_data'][$fieldName] ?? $person[$fieldName];
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('model/person.first_name.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'last_name';
                $value = old($fieldName) ?? $bankAccountToEdit['meta_data'][$fieldName] ?? $person[$fieldName];
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('model/person.last_name.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'bank_id';
                $oldValue = old($fieldName) ? decrypt(old($fieldName)) : null;
                $value = $oldValue ?? $bankAccountToEdit['meta_data'][$fieldName] ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.person_bank_account.edit_add_bank_account.form.bank.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <select id="{{ $fieldName }}" name="{{ $fieldName }}" required
                            class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}">
                        <option value="">
                            {{ __('payout.person_bank_account.edit_add_bank_account.form.bank.placeholder') }}
                        </option>
                        @foreach($banksAvailable as $bank)
                            <option value="{{ encrypt($bank['id']) }}" {{ ($value == $bank['id']) ? 'selected' : '' }}>
                                {{ $bank['name'] }}
                            </option>
                        @endforeach
                    </select>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'account_number';
                $value = old($fieldName) ?? $bankAccountToEdit[$fieldName] ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.person_bank_account.edit_add_bank_account.form.account_number.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'email';
                $value = old($fieldName) ?? $bankAccountToEdit['meta_data'][$fieldName] ?? $person[$fieldName];
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('model/person.email.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="email" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'mobile_number';
                $value = old($fieldName) ?? $bankAccountToEdit['meta_data'][$fieldName] ?? $person[$fieldName];
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('model/person.mobile_number.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            @php
                $fieldName = 'recipient_address';
                $value = old($fieldName) ?? $bankAccountToEdit['meta_data'][$fieldName] ?? '';
            @endphp
            <div class="form-group row">
                <label for="{{ $fieldName }}" class="col-sm-5 col-lg-4 col-form-label">
                    {{-- @todo translate --}}
                    {{ __('Recipient address') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    <input type="text" class="form-control{{ $errors->has($fieldName) ? ' is-invalid' : '' }}"
                           id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" required>

                    {{-- @todo translate --}}
                    <small id="{{ $fieldName }}" class="form-text text-muted">
                        {{ __('This must be the address of the recipient, so the owner of this bank account.') }}
                    </small>

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                @if ( ! empty($bankAccountId))
                    <div class="col-md-6 offset-lg-4 col-lg-4 mb-3 mb-md-0">
                        <a class="btn btn-outline-warning btn-block" href="{{ route('profile.bank_account') }}">
                            {{ __('payout.person_bank_account.edit_add_bank_account.form.cancel_cta') }}
                        </a>
                    </div>
                @endif
                <div class="col-md-6 col-lg-4{{ empty($bankAccountId) ? ' offset-lg-8' : '' }}">
                    <button type="submit" class="btn btn-primary btn-block">
                        {{ empty($bankAccountId) ? __('payout.person_bank_account.edit_add_bank_account.form.add_cta') : __('payout.person_bank_account.edit_add_bank_account.form.edit_cta') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-light my-3 py-3 px-4">
        <div class="row">
            <div class="col-12 col-md-4">
                <a href="{{ route('profile.payout-v2.options') }}" class="btn btn-outline-info btn-block">
                    {{ __('payout.back_to_payout_cta') }}
                </a>
            </div>
        </div>
    </div>
@endsection
