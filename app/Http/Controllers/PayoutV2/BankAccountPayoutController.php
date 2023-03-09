<?php

namespace App\Http\Controllers\PayoutV2;

use App\Alert\Facades\Alert;
use App\BankAccount;
use App\Constants\BlacklistInitiator;
use App\Constants\TransactionType;
use App\Country;
use App\Services\AccountControlService\AccountControlService;
use App\Services\AccountService\Constants\Balances;
use App\Services\AccountService\Constants\PayoutMethod;
use App\Services\AccountService\Constants\TransactionInitiator;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Psr\SimpleCache\InvalidArgumentException;

class BankAccountPayoutController extends PayoutOptionUtilsController {

    public function start() {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::BANK_ACCOUNT)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::AFRISIGHT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $requestedBaseAmount = request()->query('amount', $balanceAmount);
        $allowedMaxBaseTransferAmount = $payoutOption->checkAllowedAvailableTransferAmount($requestedBaseAmount,
            $balanceAmount);

        try {
            $rawLocalAmount = $payoutOption->getMethodSpecificProps('getLocalAmount', $allowedMaxBaseTransferAmount);
            $feeCompensation = $payoutOption->getMethodSpecificProps('getFeeCompensation', $rawLocalAmount);
            $localAmountAfterFeeCompensation = $feeCompensation['amount_after_compensation'] ?? 0;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout-v2.options');
        }

        $minimumThresholdReached = $payoutOption->minimumThresholdReached($balanceAmount);

        $bankAccounts = BankAccount::getAvailablePersonBankAccounts(
            $this->person->id,
            Country::getCountryIso2Code($this->person->country_id),
            PayoutMethod::BANK_ACCOUNT
        );

        switch ($payoutOption->getProvider()) {
            case 'FLUTTERWAVE':
                return view('profile.payout-v2.flutterwave-bank-account.start', compact('payoutOption',
                    'allowedMaxBaseTransferAmount', 'balanceAmount', 'localAmountAfterFeeCompensation',
                    'feeCompensation',
                    'bankAccounts', 'minimumThresholdReached'));
        }

        return redirect()->route('profile.payout-v2.options');
    }

    public function request(): RedirectResponse {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::BANK_ACCOUNT)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the payout request is not repeated multiple times, without the balance being adjusted accordingly.
        try {
            $cacheKey = PayoutMethod::BANK_ACCOUNT . "_PAYOUT_REQUEST_REPEATING_BLOCKING_" . request()->get('signature');
            if (cache()->has($cacheKey)) {
                Alert::makeWarning(__('payout.request_fail_try_later'));

                return redirect()->route('profile.payout-v2.options');
            }

            cache()->put($cacheKey, true, now()->addSeconds(60));
        } catch (Exception|InvalidArgumentException $exception) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout-v2.options');
        }

        // Prepare validation data.
        $data = request()->all(['bank_account', 'usd_amount']);
        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::AFRISIGHT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $allowedMaxBaseTransferAmount = $payoutOption->checkAllowedAvailableTransferAmount($data['usd_amount'],
            $balanceAmount);

        // Validate input
        Validator::make($data, [
            'bank_account' => [
                'required',
            ],
            'usd_amount'   => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($payoutOption, $allowedMaxBaseTransferAmount) {
                    if (round($value, 2) < round($payoutOption->getMinTransferLimit(), 2)) {
                        return $fail(__('validation.custom.usd_amount.minimum_threshold_not_reached'));
                    }

                    if (round($value, 2) > round($allowedMaxBaseTransferAmount, 2)) {
                        return $fail(__('validation.custom.usd_amount.less_than_maximum'));
                    }
                },
            ],
        ])->validate();

        // Make sure the bank account exists, belongs to this person and is available for this country.
        $bankAccountId = decrypt($data['bank_account']);
        $countryCode = Country::getCountryIso2Code($this->person->country_id);
        $bankAccounts = BankAccount::getAvailablePersonBankAccounts(
            $this->person->id,
            $countryCode,
            PayoutMethod::BANK_ACCOUNT,
            $bankAccountId
        );
        if ($bankAccounts->isEmpty()) {
            Alert::makeInfo(__('payout.method.bank_account.fail_getting_bank_account'));

            return redirect()->route('profile.bank_account');
        }

        $bankAccount = $bankAccounts->first();

        // Check if this bank account is marked as blacklisted.
        $accountControlService = AccountControlService::byBankAccount();
        $isBlacklisted = $accountControlService->isBanned($this->person->id);

        if ($isBlacklisted) {
            $accountControlService->ban(
                BlacklistInitiator::AUTOMATED,
                $countryCode,
                $bankAccount['bank_code'],
                $bankAccount['account_number'],
                [$this->person->id]
            );
            auth()->logout();

            try {
                cache()->forget('PERSON_BY_USER_ID_' . auth()->user()->id);
            } catch (Exception $e) {}

            return redirect()->home();
        }

        // Make sure the requested amount is valid.
        try {
            $rawLocalAmount = $payoutOption->getMethodSpecificProps('getLocalAmount', $allowedMaxBaseTransferAmount);
            $feeCompensation = $payoutOption->getMethodSpecificProps('getFeeCompensation', $rawLocalAmount);
            $localAmountAfterFeeCompensation = $feeCompensation['amount_after_compensation'] ?? 0;
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout-v2.options');
        }

        // Make sure the local amount is not empty.
        if (empty($localAmountAfterFeeCompensation)) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout-v2.options');
        }

        $narration = __('payout.method.general.payout_transaction_narration');
        $payoutSucceed = $payoutOption->requestPayout(
            $bankAccount,
            $allowedMaxBaseTransferAmount,
            $localAmountAfterFeeCompensation,
            TransactionType::REWARD_PAYOUT,
            TransactionInitiator::ACCOUNT_HOLDER,
            $narration
        );

        if ($payoutSucceed) {
            Alert::makeSuccess(__('payout.method.general.successful_request'));
        } else {
            Alert::makeWarning(__('payout.request_fail_try_later'));
        }

        return redirect()->route('profile.payout-v2.options');
    }

    public function calculateLocalAmount(): RedirectResponse {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::BANK_ACCOUNT)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $usdAmount = request()->get('usd_amount');
        $params = [];
        if ($usdAmount) {
            $params['amount'] = $usdAmount;
        }

        $url = url()->temporarySignedRoute('profile.payout-v2.bank-account.start', now()->addMinutes(30), $params);
        return redirect($url);
    }
}
