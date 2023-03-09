@extends('layouts.profile')

@section('title', 'Bank account' . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3">
        {{ __('payout.person_bank_account.title') }}
    </h1>

    <div class="bg-light my-3 py-3 px-4">
        <p class="small">{{ __('payout.person_bank_account.bank_branch.intro') }}</p>

        <form method="POST" class="form"
              action="{{ route('profile.bank_account.branch', ['bankAccountId' => encrypt($bankAccountId)]) }}">
            @csrf

            @php
                $fieldName = 'bank_branch_code';
                $oldValue = old($fieldName) ? decrypt(old($fieldName)) : null;
                $value = $oldValue ?? $bankAccount['meta_data'][$fieldName] ?? '';
            @endphp
            <div class="form-group row">
                <label class="col-sm-5 col-lg-4 col-form-label">
                    {{ __('payout.person_bank_account.bank_branch.form.branch.label') }}
                </label>

                <div class="col-sm-7 col-lg-8">
                    @foreach ($bankBranches as $key => $bankBranch)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="{{ $fieldName }}"
                                   value="{{ encrypt($bankBranch['branch_code']) }}" id="{{ $key }}"
                                   required {{ $value ==  $bankBranch['branch_code'] ? 'checked' : ''}}>
                            <label class="form-check-label" for="{{ $key }}">
                                {{ $bankBranch['branch_name'] }}
                            </label>
                        </div>
                    @endforeach

                    @if ($errors->has($fieldName))
                        <div class="invalid-feedback">{{ $errors->first($fieldName) }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        {{ __('payout.person_bank_account.bank_branch.form.submit_cta') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
