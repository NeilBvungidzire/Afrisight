<?php

namespace App\Http\Controllers\PayoutV2;

use App\Alert\Facades\Alert;
use App\Services\AccountService\Constants\Balances;
use App\Services\AccountService\Constants\PayoutMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PayPalPayoutController extends PayoutOptionUtilsController {

    public function start()
    {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::PAYPAL)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $panelPayPalId = $payoutOption->getCustomParams()['payment_method_id'] ?? null;
        if (empty($panelPayPalId)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the Cint amount is synced.
        $this->person->syncCintData(true);

        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::CINT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $allowedMaxBaseTransferAmount = $payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount);
        $minimumThresholdReached = $allowedMaxBaseTransferAmount >= $payoutOption->getMinTransferLimit();

        return view('profile.payout-v2.cint-paypal.start', compact('allowedMaxBaseTransferAmount',
            'minimumThresholdReached', 'payoutOption'));
    }

    public function request(): RedirectResponse
    {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::PAYPAL)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $panelPayPalId = (int)$payoutOption->getCustomParams()['payment_method_id'] ?? null;
        if (empty($panelPayPalId)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the Cint amount is synced.
        $this->person->syncCintData(true);

        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::CINT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $allowedMaxBaseTransferAmount = $payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount);

        if ($this->requestPayout($this->person->cintUser, $panelPayPalId, $allowedMaxBaseTransferAmount)) {
            Alert::makeSuccess(__('payout.method.cint_paypal.successful_request'));
        } else {
            Alert::makeWarning(__('payout.method.general.failed_request'));
        }

        return redirect()->route('profile.payout-v2.options');
    }

    /**
     * @param            $cintUser
     * @param int        $panelPayPalId
     * @param float      $allowedMaxBaseTransferAmount
     * @return bool
     */
    private function requestPayout($cintUser, int $panelPayPalId, float $allowedMaxBaseTransferAmount): bool
    {
        $requestSuccessful = $cintUser->requestPayout($panelPayPalId);

        Log::channel('cint_payout_requests')->info(
            $requestSuccessful ? 'REQUEST SUCCESSFUL' : 'REQUEST FAILED',
            [
                'cint_user'        => $cintUser->toArray(),
                'requested_amount' => $allowedMaxBaseTransferAmount,
            ]
        );

        return $requestSuccessful;
    }
}
