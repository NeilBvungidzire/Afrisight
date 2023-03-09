<?php

namespace App\Http\Controllers\Payout;

use App\CintUser;
use App\Person;
use Exception;
use Illuminate\Support\Facades\Log;

class CintPayPalController extends PayoutController {

    /**
     * @var CintUser
     */
    private $cintUser;

    /**
     * @var int
     */
    private $panelPayPalId;

    /**
     * @var Person
     */
    private $person;

    public function start()
    {
        if ( ! $this->isAllowed()) {
            request()->session()->flash('status', __('profile.sub_pages.payout.payout_request_failed'));

            return redirect()->route('profile.payout');
        }

        // Make sure the country is set.
        if ( ! $this->person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        // Make sure the Cint amount is synced.
        $this->person->syncCintData(true);

        $cintRewardBalance = $this->person->rewardAccount()->getCalculatedRewardBalance('cint');

        return view('profile.payout.cint-paypal.index', compact('cintRewardBalance'));
    }

    public function request()
    {
        if ( ! $this->isAllowed()) {
            request()->session()->flash('status', __('payout.method.general.failed_request'));

            return redirect()->route('profile.payout');
        }

        // Make sure the country is set.
        if ( ! $this->person->can_request_payout) {
            return redirect()->route('profile.basic-info.edit');
        }

        if ($this->requestPayPalPayout()) {
            request()->session()->flash('status', __('payout.method.cint_paypal.successful_request'));
        } else {
            request()->session()->flash('status', __('payout.method.general.failed_request'));
        }

        return redirect()->route('profile.payout');
    }

    /**
     * @return bool
     */
    private function isAllowed()
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return false;
        }

        $this->person = $user->person;
        $cintPanelPaymentMethods = $this->getActiveCintPanelPaymentMethods($this->person->country->iso_alpha_2);

        $this->cintUser = $this->person->cintUser;
        if (empty($this->cintUser->can_request_payout)) {
            return false;
        }

        // Try find requested payment method.
        $requestedPanelPaymentMethod = null;
        foreach ($cintPanelPaymentMethods as $panelPaymentMethodKey => $panelPaymentMethod) {
            if ($panelPaymentMethodKey === 'paypal') {
                $requestedPanelPaymentMethod = $panelPaymentMethod;
            }
        }

        // Handle not found requested payment method.
        if (empty($requestedPanelPaymentMethod) || ! isset($requestedPanelPaymentMethod['id'])) {
            return false;
        }

        // Make sure the requested amount is more than the threshold.
        try {
            $calculatedRewardBalance = $this->person->rewardAccount()->getCalculatedRewardBalance('cint');
            if ($calculatedRewardBalance < $requestedPanelPaymentMethod['threshold_money']) {
                return false;
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        $this->panelPayPalId = $requestedPanelPaymentMethod['id'];

        return true;
    }

    /**
     * @return bool
     */
    private function requestPayPalPayout()
    {
        if ( ! $this->cintUser->requestPayout($this->panelPayPalId)) {
            Log::error('Could not request Cint PayPal payout', [
                'cint_user' => $this->cintUser->toArray(),
            ]);

            return false;
        }

        Log::info('User successfully requested Cint PayPal payout.', [
            'cint_user' => $this->cintUser->toArray(),
        ]);

        return true;
    }
}
