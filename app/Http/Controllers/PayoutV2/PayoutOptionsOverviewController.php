<?php

namespace App\Http\Controllers\PayoutV2;

use App\Alert\Facades\Alert;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Person;
use App\Services\AccountService\AccountService;
use App\Services\AccountService\Constants\PayoutMethod;
use App\Services\AccountService\Constants\TransactionStatus;
use App\Services\AccountService\Contracts\PayoutOptionContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PayoutOptionsOverviewController extends Controller {

    /**
     * @return RedirectResponse|View
     */
    public function __invoke()
    {
        $person = authUser()->person;

        $accountService = new AccountService($person);

        if ( ! $accountService->requiredPersonDataAvailable()) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        AccountService::clearCachedBalance($person->id);

        $payoutOptions = $accountService->getPayoutOptions();
        foreach ($payoutOptions as $payoutOption) {
            $this->supplementOption($payoutOption);
        }
        $payoutTransactions = $this->getPayoutTransactions($person);

        return view('profile.payout-v2.index', compact('payoutOptions', 'payoutTransactions'));
    }

    /**
     * @param PayoutOptionContract $option
     * @return PayoutOptionContract
     */
    private function supplementOption(PayoutOptionContract $option): PayoutOptionContract
    {
        switch ($option->getMethodName()) {

            case PayoutMethod::BANK_ACCOUNT:
                $option->title = __('payout.method.bank_account.short_name');
                $option->link = url()->temporarySignedRoute('profile.payout-v2.bank-account.start', now()->addMinutes(30));
                $option->intro = __('payout.method.bank_account.intro');
                break;

            case PayoutMethod::MOBILE_TOP_UP:
                $option->title = __('payout.method.mobile_top_up.short_name');
                $option->link = url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30));
                $option->intro = __('payout.method.mobile_top_up.intro');
                break;

            case PayoutMethod::PAYPAL:
                $option->title = __('payout.method.cint_paypal.short_name');
                $option->link = url()->temporarySignedRoute('profile.payout-v2.paypal.start', now()->addMinutes(30));
                $option->intro = __('payout.method.cint_paypal.intro');
                break;

            default:
                $option->link = route('profile.payout-v2.options');
        }

        return $option;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    private function getPayoutTransactions(Person $person): array
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
}
