<?php

namespace App\Http\Controllers\Payout;

use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Libraries\Payout\Payout;
use App\Person;

class PayoutController extends Controller {

    public function show()
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            abort(403);
        }
        $person = $user->person;

        // Make sure the country is set.
        if ( ! $person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the Cint amount is synced.
        $person->syncCintData();

        $combineRewardAccounts = $person->can_combine_with_cint_reward_balance;
        $rewardAccountType = $combineRewardAccounts ? 'all' : 'own';

        $rewardAccount = $person->rewardAccount();
        $rewardBalance = $rewardAccount->getCalculatedRewardBalance($rewardAccountType);

        // Make sure the cached reward balance sum is cleared to make sure the amount is correct.
        $rewardAccount->forceFresh();

        $countryCode = $person->country->getCountryCode($person->country_id);

        // Get payment methods handled by Cint.
        $cintPanelPaymentMethods = $this->getActiveCintPanelPaymentMethods($countryCode);

        // Get payment methods handled via us.
        $payout = new Payout();

        $payout->setCurrentBalanceAmount($rewardBalance);

        $availablePaymentMethods = [];
        if ($countryCode) {
            $availablePaymentMethods = $payout->getAvailablePayoutMethods($countryCode);
        }

        $hasNoBankAccounts = $payout->bankAccount($countryCode)->getPersonBankAccounts($person->id)->isEmpty();

        $transactions = $this->formatTransactionList($person);

        return view('profile.payout.index', compact('cintPanelPaymentMethods', 'rewardAccountType',
            'availablePaymentMethods', 'hasNoBankAccounts', 'transactions', 'rewardAccount', 'combineRewardAccounts'));
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    private function formatTransactionList(Person $person)
    {
        $transactions = $person->transactions()
            ->whereIn('status', [
                TransactionStatus::REQUESTED,
                TransactionStatus::PENDING,
                TransactionStatus::APPROVED,
                TransactionStatus::DENIED,
            ])
            ->where('type', TransactionType::REWARD_PAYOUT)
            ->get();

        $method = [
            PayoutMethod::BANK_ACCOUNT  => __('payout.method.bank_account.short_name'),
            PayoutMethod::MOBILE_MONEY  => __('payout.method.mobile_money.short_name'),
            PayoutMethod::MOBILE_TOP_UP => __('payout.method.mobile_top_up.short_name'),
        ];

        $status = [
            TransactionStatus::PENDING  => __('payout.payout_requests.list.status.value.pending'),
            TransactionStatus::DENIED   => __('payout.payout_requests.list.status.value.rejected'),
            TransactionStatus::APPROVED => __('payout.payout_requests.list.status.value.approved'),
        ];

        $results = [];
        foreach ($transactions as $transaction) {
            $results[] = [
                'date'   => $transaction->updated_at->format('d-m-Y'),
                'method' => (isset($transaction->meta_data['payout_method']) && isset($method[$transaction->meta_data['payout_method']]))
                    ? $method[$transaction->meta_data['payout_method']]
                    : __('payout.payout_requests.list.method.value.other'),
                'amount' => number_format($transaction->amount, 2),
                'status' => isset($status[$transaction->status])
                    ? $status[$transaction->status]
                    : __('payout.payout_requests.list.status.value.other'),
            ];
        }

        return $results;
    }

    /**
     * Get active payment methods for country panel.
     *
     * @param string $isoAlpha2
     *
     * @return array
     */
    protected function getActiveCintPanelPaymentMethods(string $isoAlpha2)
    {
        // Get payment methods available.
        $cintPanelPaymentMethods = [];
        foreach (config('cint.panels') as $panelConfig) {
            if ($panelConfig['country']['iso_alpha_2'] === $isoAlpha2) {
                $cintPanelPaymentMethods = $panelConfig['payment_methods'] ?? [];
            }
        }

        foreach ($cintPanelPaymentMethods as $panelPaymentMethodKey => $panelPaymentMethod) {
            if ( ! $panelPaymentMethod['active']) {
                unset($cintPanelPaymentMethods[$panelPaymentMethodKey]);
            }
        }

        return $cintPanelPaymentMethods;
    }
}
