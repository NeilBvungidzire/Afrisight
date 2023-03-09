<?php

namespace App\Http\Controllers\Payout;

use App\BankAccount;
use App\Http\Controllers\Controller;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Libraries\Payout\Payout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller {

    public function index()
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return abort(403);
        }
        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        $countryCode = $person->country->getCountryCode($person->country_id);
        $paymentMethod = PayoutMethod::BANK_ACCOUNT;

        $payout = new Payout();

        // Check if the requested is allowed, this bank is available in this user's country.
        if ( ! $payout->getPayoutMethod($countryCode, $paymentMethod)) {
            Log::warning('User tries bank account payout, but is not available based on the params.',
                [
                    'country_code'   => $countryCode,
                    'payment_method' => $paymentMethod,
                    'person_id'      => $person->id,
                ]);

            return redirect()->route('profile.payout');
        }

        // Make sure one or more banks are available for this country to continue.
        $banksAvailable = $payout->bankAccount($countryCode)->getAvailableBanks();
        if (empty($banksAvailable)) {
            return redirect()->route('profile.payout');
        }
        $banksAvailable = collect($banksAvailable)->keyBy('code');

        $bankAccounts = $payout->bankAccount($countryCode)->getPersonBankAccounts($person->id);

        $bankAccountId = request()->query('account');
        $bankAccountToEdit = [];
        if ($bankAccountId) {
            try {
                $bankAccountId = decrypt($bankAccountId);
                $bankAccountToEdit = $payout->bankAccount($countryCode)
                    ->getPersonBankAccounts($person->id, $bankAccountId)
                    ->first();

            } catch (\Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());

                return redirect()->back();
            }
        }

        // Remove all bank accounts which requires a bank branch, but is not set.
        if ($payout->bankAccount($countryCode)->bankBranchRequired()) {
            foreach ($bankAccounts as $key => $bankAccount) {
                if ( ! isset($bankAccount->meta_data['bank_branch_code'])) {
                    unset($bankAccounts[$key]);
                    BankAccount::destroy($bankAccount->id);
                }
            }
        }

        return view('profile.bank-account.index', compact('person', 'banksAvailable',
            'bankAccounts', 'bankAccountToEdit', 'bankAccountId'));
    }

    public function save()
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return abort(403);
        }
        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        $countryCode = $person->country->getCountryCode($person->country_id);
        $paymentMethod = PayoutMethod::BANK_ACCOUNT;

        $payout = new Payout();

        // Check if the requested is allowed, this bank is available in this user's country.
        if ( ! $payout->getPayoutMethod($countryCode, $paymentMethod)) {
            Log::warning('User tries bank account payout, but is not available based on the params.',
                [
                    'country_code'   => $countryCode,
                    'payment_method' => $paymentMethod,
                    'person_id'      => $person->id,
                ]);

            return redirect()->route('profile.bank_account');
        }

        $data = request()->all([
            'first_name',
            'last_name',
            'bank_id',
            'account_number',
            'email',
            'mobile_number',
        ]);

        Validator::make($data, [
            'first_name'     => ['required'],
            'last_name'      => ['required'],
            'bank_id'        => ['required'],
            'account_number' => ['required'],
            'email'          => ['required', 'email'],
            'mobile_number'  => ['required'],
        ])->validate();

        try {
            $data['bank_id'] = decrypt($data['bank_id']);

            $bankAccountId = request()->query('account');
            if ($bankAccountId) {
                $bankAccountId = decrypt($bankAccountId);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        $bankAccount = $payout->bankAccount($countryCode)
            ->savePersonBankAccount($person->id, $data['bank_id'], $data['account_number'], Arr::only($data, [
                'first_name',
                'last_name',
                'bank_id',
                'bank_branch_code',
                'email',
                'mobile_number',
            ]), $bankAccountId);

        if ($bankAccount->type === PayoutMethod::BANK_ACCOUNT && $payout->bankAccount($countryCode)->bankBranchRequired()) {
            return redirect()->route('profile.bank_account.branch', ['bankAccountId' => encrypt($bankAccount->id)]);
        }

        return redirect()->route('profile.bank_account');
    }

    public function delete()
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return abort(403);
        }
        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        $bankAccountId = request()->query('account');
        if ( ! $bankAccountId) {
            return redirect()->route('profile.bank_account');
        }

        try {
            $bankAccountId = decrypt($bankAccountId);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        $bankAccount = BankAccount::query()
            ->where('id', $bankAccountId)
            ->where('person_id', $person->id)
            ->first();

        if ($bankAccount) {
            try {
                $bankAccount->delete();
            } catch (\Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());
            }
        }

        return redirect()->route('profile.bank_account');
    }

    public function selectBranch(string $bankAccountId)
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return abort(403);
        }
        $person = $user->person;
        $countryCode = $person->country->getCountryCode($person->country_id);

        try {
            $bankAccountId = decrypt($bankAccountId);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        // Could not find the bank account.
        if ( ! $bankAccount = BankAccount::find($bankAccountId)) {
            return redirect()->route('profile.bank_account');
        }

        $payout = new Payout();
        if ($bankAccount->type !== PayoutMethod::BANK_ACCOUNT || ! $payout->bankAccount($countryCode)->bankBranchRequired()) {
            return redirect()->route('profile.bank_account');
        }

        if ( ! isset($bankAccount->meta_data['bank_id'])) {
            $bankAccount->delete();

            return redirect()->route('profile.bank_account');
        }

        $bankBranches = $payout->bankAccount($countryCode)->getBankBranches($bankAccount->meta_data['bank_id']);
        if (empty($bankBranches)) {
            $bankAccount->delete();

            return redirect()->route('profile.bank_account');
        }

        return view('profile.bank-account.bank-branch', compact('bankAccountId', 'bankAccount',
            'bankBranches'));
    }

    public function saveBranch(string $bankAccountId)
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return abort(403);
        }
        $person = $user->person;
        $countryCode = $person->country->getCountryCode($person->country_id);

        try {
            $bankAccountId = decrypt($bankAccountId);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        // Could not find the bank account.
        if ( ! $bankAccount = BankAccount::find($bankAccountId)) {
            return redirect()->route('profile.bank_account');
        }

        $payout = new Payout();
        if ($bankAccount->type !== PayoutMethod::BANK_ACCOUNT || ! $payout->bankAccount($countryCode)->bankBranchRequired()) {
            return redirect()->route('profile.bank_account');
        }

        if ( ! isset($bankAccount->meta_data['bank_id'])) {
            $bankAccount->delete();

            return redirect()->route('profile.bank_account');
        }

        $data = request()->all('bank_branch_code');
        Validator::make($data, [
            'bank_branch_code' => ['required'],
        ])->validate();

        try {
            $bankBranchCode = decrypt($data['bank_branch_code']);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        $bankAccount->meta_data = array_merge($bankAccount->meta_data, ['bank_branch_code' => $bankBranchCode]);
        $bankAccount->save();

        return redirect()->route('profile.bank_account');
    }
}
