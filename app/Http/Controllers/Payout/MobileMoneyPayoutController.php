<?php

namespace App\Http\Controllers\Payout;

use App\Alert\Facades\Alert;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Libraries\Payout\Constants\TransactionInitiator;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Libraries\Payout\Payout;
use App\Libraries\RewardAccount\RewardAccountBase;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class MobileMoneyPayoutController extends Controller {

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
        $rewardAccount = $person->rewardAccount();
        $rewardAccount->forceFresh();

        $combineRewardAccounts = $person->can_combine_with_cint_reward_balance;
        $rewardAccountType = $combineRewardAccounts ? 'all' : 'own';

        $countryCode = $person->country->getCountryCode($person->country_id);
        $rewardBalance = $rewardAccount->getCalculatedRewardBalance($rewardAccountType);

        $payout = new Payout();
        $payout->setCurrentBalanceAmount($rewardBalance);

        // Check if the requested is allowed, this bank is available in this user's country.
        $paymentMethod = $payout->getPayoutMethod($countryCode, PayoutMethod::MOBILE_MONEY);
        if ( ! $paymentMethod) {
            Log::warning('User tries mobile money payout, but is not available based on the params.',
                [
                    'country_code'   => $countryCode,
                    'payment_method' => PayoutMethod::MOBILE_MONEY,
                    'person_id'      => $person->id,
                ]);

            return redirect()->route('profile.payout');
        }

        $minAmount = (float)number_format($paymentMethod['minimal_threshold'], 2, '.', '');;
        $maxAmount = $paymentMethod['maximum_payout_amount'];
        $usdAmount = number_format(request()->query('amount') ?? $maxAmount, 2);
        // Make sure the (potential) request amount is not lower than the threshold.
        $usdAmount = ($usdAmount < $minAmount) ? $minAmount : $usdAmount;
        // Make sure the (potential) request amount is not higher than the reward balance.
        $usdAmount = ($usdAmount > $maxAmount) ? $maxAmount : $usdAmount;

        $mobileNumber = $person->mobile_number;

        return view('profile.payout.mobile-money.index', compact('usdAmount', 'minAmount',
            'maxAmount', 'mobileNumber'));
    }

    public function request()
    {
        // Make sure the user and person model exist for the authenticated user.
        if ( ! $user = authUser()) {
            abort(403);
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

        $data = request()->all(['mobile_number', 'amount']);

        // Validate input
        Validator::make($data, [
            'amount'        => ['required', 'numeric'],
            'mobile_number' => ['required'],
        ])->validate();

        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the cached reward balance sum is cleared to make sure the amount is correct.
        $rewardAccount = $person->rewardAccount();
        $rewardAccount->forceFresh();

        $combineRewardAccounts = $person->can_combine_with_cint_reward_balance;
        $rewardAccountType = $combineRewardAccounts ? 'all' : 'own';

        $countryCode = $person->country->getCountryCode($person->country_id);
        $rewardBalance = $rewardAccount->getCalculatedRewardBalance($rewardAccountType);

        $payout = new Payout();
        $payout->setCurrentBalanceAmount($rewardBalance);

        // Check if the requested is allowed, this bank is available in this user's country.
        $paymentMethod = $payout->getPayoutMethod($countryCode, PayoutMethod::MOBILE_MONEY);
        if ( ! $paymentMethod) {
            Log::warning('User tries mobile money payout, but is not available based on the params.',
                [
                    'country_code'   => $countryCode,
                    'payment_method' => PayoutMethod::MOBILE_MONEY,
                    'person_id'      => $person->id,
                ]);

            return redirect()->route('profile.payout');
        }

        $minAmount = (float)number_format($paymentMethod['minimal_threshold'], 2, '.', '');;
        $maxAmount = $paymentMethod['maximum_payout_amount'];
        $usdAmount = number_format($data['amount'], 2);

        // Make sure the requested amount is not lower than the threshold or higher than the reward balance.
        if (($usdAmount < $minAmount) || ($usdAmount > $maxAmount)) {
            Alert::makeWarning(__('payout.method.general.request_amount_crossing_limits'));

            return redirect()->back();
        }

        // Make sure the (potential) request amount is not lower than the threshold.
        $usdAmount = ($usdAmount < $minAmount) ? $minAmount : $usdAmount;
        // Make sure the (potential) request amount is not higher than the reward balance.
        $usdAmount = ($usdAmount > $maxAmount) ? $maxAmount : $usdAmount;

        $transaction = $person->transactions()->create([
            'uuid'       => Str::uuid()->toString(),
            'type'       => TransactionType::REWARD_PAYOUT,
            'initiator'  => TransactionInitiator::ACCOUNT_HOLDER,
            'amount'     => -($usdAmount),
            'new_status' => TransactionStatus::REQUESTED,
            'meta_data'  => [
                'debit_currency' => 'USD',
                'payout_method'  => PayoutMethod::MOBILE_MONEY,
                'provider'       => isset($paymentMethod['provider']) ? $paymentMethod['provider'] : null,
                'mobile_number'  => $data['mobile_number'],
            ],
        ]);

        RewardAccountBase::forceRefresh($person->id);

        if ($transaction) {
            Alert::makeSuccess(__('payout.method.general.successful_request'));

            return redirect()->route('profile.payout');
        }

        Alert::makeWarning(__('payout.request_fail_try_later'));

        return redirect()->route('profile.payout');
    }
}
