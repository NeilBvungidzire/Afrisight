<?php

namespace App\Http\Controllers\Payout;

use App\Alert\Facades\Alert;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Libraries\Payout\Constants\TransactionInitiator;
use App\Libraries\Payout\Payout;
use App\Libraries\RewardAccount\RewardAccountBase;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Psr\SimpleCache\InvalidArgumentException;

class BankAccountPayoutController extends Controller {

    public function start()
    {
        // Make sure the user and person model exist for the authenticated user.
        if ( ! $user = authUser()) {
            abort(403);
        }
        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the cached reward balance sum is cleared to make sure the amount is correct.
        /** @var RewardAccountBase $rewardAccount */
        $rewardAccount = $person->rewardAccount();
        $rewardAccount->forceFresh();

        $combineRewardAccounts = $person->can_combine_with_cint_reward_balance;
        $rewardAccountType = $combineRewardAccounts ? 'all' : 'own';

        $countryCode = $person->country->getCountryCode($person->country_id);
        $rewardBalance = $rewardAccount->getCalculatedRewardBalance($rewardAccountType);

        $payout = new Payout();
        $payout->setCurrentBalanceAmount($rewardBalance);

        // Check if the requested is allowed, this bank is available in this user's country.
        $paymentMethod = $payout->getPayoutMethod($countryCode, PayoutMethod::BANK_ACCOUNT);
        if ( ! $paymentMethod) {
            Log::warning('User tries bank account payout, but is not available based on the params.',
                [
                    'country_code'   => $countryCode,
                    'payment_method' => PayoutMethod::BANK_ACCOUNT,
                    'person_id'      => $person->id,
                ]);

            return redirect()->route('profile.payout');
        }

        $bankAccounts = $payout->bankAccount($countryCode)->getPersonBankAccounts($person->id, null, true);
        if ($bankAccounts->isEmpty()) {
            return redirect()->route('profile.bank_account');
        }

        $threshold = $payout->bankAccount($countryCode)->getThresholdAmount();
        $maximumAmount = $paymentMethod['maximum_payout_amount'];
        $usdAmount = number_format(request()->query('amount') ?? $maximumAmount, 2);
        // Make sure the (potential) request amount is not lower than the threshold.
        $usdAmount = ($usdAmount < $threshold) ? $threshold : $usdAmount;
        // Make sure the (potential) request amount is not higher than the reward balance.
        $usdAmount = ($usdAmount > $maximumAmount) ? $maximumAmount : $usdAmount;
        if ($fxRate = $payout->bankAccount($countryCode)->fxRates($usdAmount)) {
            $localAmount = $fxRate['amount'];
            $localCurrency = $fxRate['to'];
        }
        if (empty($localAmount) || empty($localCurrency)) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout');
        }

        $transferFee = $payout->bankAccount($countryCode)->transferFee((float)$localAmount);
        if ($transferFee === null) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout');
        }

        return view('profile.payout.bank-account.index', compact('bankAccounts', 'usdAmount',
            'localAmount', 'localCurrency', 'threshold', 'transferFee', 'paymentMethod', 'maximumAmount'));
    }

    public function request()
    {
        // Make sure the user and person model exist for the authenticated user.
        if ( ! $user = authUser()) {
            abort(403);
        }

        // Handle request for calculating the local amount.
        if (request()->get('calculate')) {
            return redirect(url()->temporarySignedRoute('profile.payout.bank_account.request', now()->addMinutes(30), [
                'amount' => request()->get('amount'),
            ]));
        }

        // Make sure the payout request is not repeated multiple times, without the balance being adjusted accordingly.
        try {
            $cacheKey = "PAYOUT_REQUEST_REPEATING_BLOCKING_" . request()->get('signature');
            if (cache()->has($cacheKey)) {
                Alert::makeWarning(__('payout.request_fail_try_later'));

                return redirect()->route('profile.payout');
            }

            cache()->put($cacheKey, true, now()->addSeconds(60));
        } catch (Exception | InvalidArgumentException $exception) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout');
        }

        $data = request()->all(['bank_account', 'amount']);

        // Validate input
        Validator::make($data, [
            'bank_account' => ['required'],
            'amount'       => ['required', 'numeric'],
        ])->validate();

        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        $rewardAccount = $person->rewardAccount();
        // Make sure the cached reward balance sum is cleared to make sure the amount is correct.
        $rewardAccount->forceFresh();

        $combineRewardAccounts = $person->can_combine_with_cint_reward_balance;
        $rewardAccountType = $combineRewardAccounts ? 'all' : 'own';

        $countryCode = $person->country->getCountryCode($person->country_id);
        $rewardBalance = $rewardAccount->getCalculatedRewardBalance($rewardAccountType);

        $payout = new Payout();
        $payout->setCurrentBalanceAmount($rewardBalance);

        // Check if the requested is allowed, this bank is available in this user's country.
        $paymentMethod = $payout->getPayoutMethod($countryCode, PayoutMethod::BANK_ACCOUNT);
        if ( ! $paymentMethod) {
            Log::warning('User tries bank account payout, but this method is not available based on the params.',
                [
                    'country_code'   => $countryCode,
                    'payment_method' => PayoutMethod::BANK_ACCOUNT,
                    'person_id'      => $person->id,
                ]);

            return redirect()->route('profile.payout');
        }

        $bankAccountId = decrypt($data['bank_account']);
        $bankAccount = $payout->bankAccount($countryCode)
            ->getPersonBankAccounts($person->id, $bankAccountId, true)
            ->first();
        if ( ! $bankAccount) {
            Alert::makeInfo(__('payout.method.bank_account.fail_getting_bank_account'));

            return redirect()->route('profile.bank_account');
        }

        $threshold = $payout->bankAccount($countryCode)->getThresholdAmount();
        $maximumAmount = $paymentMethod['maximum_payout_amount'];
        $usdAmount = (float)number_format($data['amount'], 2, '.', '');

        // Make sure the requested amount is not lower than the threshold or higher than the reward balance.
        if (($usdAmount < $threshold) || ($usdAmount > $maximumAmount)) {
            Alert::makeWarning(__('payout.method.general.request_amount_crossing_limits'));

            return redirect()->back();
        }

        $localAmount = null;
        $localCurrency = null;
        if ($fxRate = $payout->bankAccount($countryCode)->fxRates($usdAmount)) {
            $localAmount = $fxRate['amount'];
            $localCurrency = $fxRate['to'];
        }
        if (empty($localAmount) || empty($localCurrency)) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout');
        }

        $transferFee = $payout->bankAccount($countryCode)->transferFee((float)$localAmount);
        if ($transferFee === null) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout');
        }

        $localAmount = ($localAmount - $transferFee['our_part']);
        $narration = __('payout.method.general.payout_transaction_narration');
        $transferResult = $payout->bankAccount($countryCode)
            ->transfer($bankAccount->id, $usdAmount, $localAmount, TransactionType::REWARD_PAYOUT,
                TransactionInitiator::ACCOUNT_HOLDER, $narration);

        if ( ! $transferResult) {
            Alert::makeWarning(__('payout.request_fail_try_later'));

            return redirect()->route('profile.payout');
        }

        Alert::makeSuccess(__('payout.method.general.successful_request'));

        return redirect()->route('profile.payout');
    }
}
