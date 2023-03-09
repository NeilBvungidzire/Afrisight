<?php

namespace App\Http\Controllers\PayoutV2;

use App\Alert\Facades\Alert;
use App\BankAccount;
use App\Constants\BlacklistInitiator;
use App\Country;
use App\Libraries\Flutterwave\Flutterwave;
use App\Services\AccountControlService\AccountControlService;
use App\Services\AccountService\Constants\PayoutMethod;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends PayoutOptionUtilsController {

    public function index()
    {
        $this->initialize();

        $payoutMethod = PayoutMethod::BANK_ACCOUNT;

        if ( ! $this->checkAvailability($payoutMethod)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $countryCode = Country::getCountryIso2Code($this->person->country_id);
        $flutterwave = new Flutterwave();

        // Make sure one or more banks are available for this country to continue.
        $banksAvailable = $flutterwave->banks()->getAllBanks($countryCode, $payoutMethod);
        if (empty($banksAvailable)) {
            return redirect()->route('profile.payout-v2.options');
        }
        $banksAvailable = collect($banksAvailable)->keyBy('code');

        $bankAccounts = BankAccount::getAvailablePersonBankAccounts($this->person->id, $countryCode, $payoutMethod);

        // Handle single person bank account request.
        $bankAccountId = request()->query('account');
        $bankAccountToEdit = [];
        if ($bankAccountId) {
            try {
                $bankAccountId = decrypt($bankAccountId);
                $bankAccountToEdit = BankAccount::getAvailablePersonBankAccounts(
                    $this->person->id,
                    $countryCode,
                    $payoutMethod,
                    $bankAccountId
                )->first();
            } catch (Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());

                return redirect()->back();
            }
        }

        // Remove all bank accounts which requires a bank branch, but is not set.
        if ($flutterwave->banks()->bankBranchRequired($countryCode)) {
            foreach ($bankAccounts as $key => $bankAccount) {
                if ( ! isset($bankAccount->meta_data['bank_branch_code'])) {
                    unset($bankAccounts[$key]);
                    BankAccount::destroy($bankAccount->id);
                }
            }
        }

        $person = $this->person;
        return view('profile.payout-v2.bank-account.index', compact('person', 'banksAvailable',
            'bankAccounts', 'bankAccountToEdit', 'bankAccountId'));
    }

    public function save(): RedirectResponse
    {
        $this->initialize();

        $payoutMethod = PayoutMethod::BANK_ACCOUNT;

        if ( ! $this->checkAvailability($payoutMethod)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $countryCode = Country::getCountryIso2Code($this->person->country_id);

        $data = request()->all([
            'first_name',
            'last_name',
            'bank_id',
            'account_number',
            'email',
            'mobile_number',
            'recipient_address',
        ]);

        Validator::make($data, [
            'first_name'        => ['required'],
            'last_name'         => ['required'],
            'bank_id'           => ['required'],
            'account_number'    => ['required'],
            'email'             => ['required', 'email'],
            'mobile_number'     => ['required'],
            'recipient_address' => ['required'],
        ])->validate();

        try {
            $data['bank_id'] = decrypt($data['bank_id']);

            if ($bankAccountId = request()->query('account')) {
                $bankAccountId = decrypt($bankAccountId);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        if ( ! $bankCode = $this->getBankCodeById($data['bank_id'], $countryCode, $payoutMethod)) {
            return redirect()->route('profile.bank_account');
        }

        $bankAccount = $this->savePersonBankAccount($this->person->id, $countryCode, $payoutMethod, $bankCode, $data['account_number'], Arr::only($data, [
            'first_name',
            'last_name',
            'bank_id',
            'email',
            'mobile_number',
            'recipient_address',
        ]), $bankAccountId);

        if ($bankAccount->type === PayoutMethod::BANK_ACCOUNT && (new Flutterwave())->banks()->bankBranchRequired($countryCode)) {
            return redirect()->route('profile.bank_account.branch', ['bankAccountId' => encrypt($bankAccount->id)]);
        }

        // Check if this bank account is marked as blacklisted.
        $accountControlService = AccountControlService::byBankAccount();
        $isBlacklisted = $accountControlService->isBanned($this->person->id);

        if ($isBlacklisted) {
            $accountControlService->ban(
                BlacklistInitiator::AUTOMATED,
                $countryCode,
                $bankAccount->bank_code,
                $bankAccount->account_number,
                [$this->person->id]
            );
            auth()->logout();

            try {
                cache()->forget('PERSON_BY_USER_ID_' . auth()->user()->id);
            } catch (Exception $e) {}

            return redirect()->home();
        }

        return redirect()->route('profile.bank_account');
    }

    public function delete(): RedirectResponse
    {
        $this->initialize();

        $payoutMethod = PayoutMethod::BANK_ACCOUNT;

        if ( ! $this->checkAvailability($payoutMethod)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        if ( ! $bankAccountId = request()->query('account')) {
            return redirect()->route('profile.bank_account');
        }

        try {
            $bankAccountId = decrypt($bankAccountId);
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        $bankAccount = BankAccount::query()
            ->where('id', $bankAccountId)
            ->where('person_id', $this->person->id)
            ->first();

        if ($bankAccount) {
            try {
                $bankAccount->delete();
            } catch (Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());
            }
        }

        return redirect()->route('profile.bank_account');
    }

    public function selectBranch(string $bankAccountId)
    {
        $this->initialize();

        $payoutMethod = PayoutMethod::BANK_ACCOUNT;

        if ( ! $this->checkAvailability($payoutMethod)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        try {
            $bankAccountId = decrypt($bankAccountId);
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        // Could not find the bank account.
        if ( ! $bankAccount = BankAccount::find($bankAccountId)) {
            return redirect()->route('profile.bank_account');
        }

        $countryCode = Country::getCountryIso2Code($this->person->country_id);
        $flutterwave = new Flutterwave();

        if ($bankAccount->type === $payoutMethod && ! $flutterwave->banks()->bankBranchRequired($countryCode)) {
            return redirect()->route('profile.bank_account');
        }

        if ( ! isset($bankAccount->meta_data['bank_id'])) {
            $bankAccount->delete();

            return redirect()->route('profile.bank_account');
        }

        $bankBranches = $flutterwave->banks()->getBranches($bankAccount->meta_data['bank_id']);
        if (empty($bankBranches)) {
            $bankAccount->delete();

            return redirect()->route('profile.bank_account');
        }

        return view('profile.payout-v2.bank-account.bank-branch', compact('bankAccountId', 'bankAccount',
            'bankBranches'));
    }

    public function saveBranch(string $bankAccountId): RedirectResponse
    {
        $this->initialize();

        $payoutMethod = PayoutMethod::BANK_ACCOUNT;

        if ( ! $this->checkAvailability($payoutMethod)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        try {
            $bankAccountId = decrypt($bankAccountId);
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return redirect()->route('profile.bank_account');
        }

        // Could not find the bank account.
        if ( ! $bankAccount = BankAccount::find($bankAccountId)) {
            return redirect()->route('profile.bank_account');
        }

        $countryCode = Country::getCountryIso2Code($this->person->country_id);
        $flutterwave = new Flutterwave();

        if ($bankAccount->type === $payoutMethod && ! $flutterwave->banks()->bankBranchRequired($countryCode)) {
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

    /**
     * @param int      $personId
     * @param string   $countryCode
     * @param string   $payoutMethod
     * @param string   $bankCode
     * @param string   $accountNumber
     * @param array    $metaData
     * @param int|null $id
     * @return BankAccount
     */
    private function savePersonBankAccount(int $personId, string $countryCode, string $payoutMethod, string $bankCode, string $accountNumber, array $metaData, int $id = null): BankAccount
    {
        $existingData = [
            'person_id'    => $personId,
            'country_code' => $countryCode,
            'type'         => $payoutMethod,
        ];
        if ($id) {
            $existingData['id'] = $id;
        } else {
            $existingData['bank_code'] = $bankCode;
            $existingData['account_number'] = $accountNumber;
        }

        return BankAccount::updateOrCreate($existingData, [
            'bank_code'      => $bankCode,
            'account_number' => $accountNumber,
            'meta_data'      => $metaData,
        ]);
    }

    /**
     * @param int    $id
     * @param string $countryCode
     * @param string $payoutMethod
     * @return string|null
     */
    private function getBankCodeById(int $id, string $countryCode, string $payoutMethod): ?string
    {
        $flutterwave = new Flutterwave();
        $banksAvailable = $flutterwave->banks()->getAllBanks($countryCode, $payoutMethod);
        if (empty($banksAvailable)) {
            return null;
        }

        foreach ($banksAvailable as $availableBank) {
            if ($availableBank['id'] == $id) {
                return $availableBank['code'];
            }
        }

        return null;
    }
}
