<?php

namespace App\Http\Controllers\PayoutV2;

use App\Alert\Facades\Alert;
use App\Constants\BlacklistInitiator;
use App\Country;
use App\Libraries\Reloadly\Reloadly;
use App\Services\AccountControlService\AccountControlService;
use App\Services\AccountService\Constants\Balances;
use App\Services\AccountService\Constants\PayoutMethod;
use App\Services\AccountService\Constants\TransactionInitiator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;

class MobileTopUpPayoutController extends PayoutOptionUtilsController {

    public function start()
    {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::MOBILE_TOP_UP)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $mobileNumber = $this->person->mobile_number;

        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::AFRISIGHT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $minimumThresholdReached = $payoutOption->minimumThresholdReached($balanceAmount);

        switch ($payoutOption->getProvider()) {
            case 'RELOADLY':
                return view('profile.payout-v2.reloadly-mobile-top-up.start', compact('payoutOption',
                    'minimumThresholdReached', 'mobileNumber'));
        }
    }

    public function handlePhoneNumber()
    {
        $this->initialize();

        if ( ! $this->checkAvailability(PayoutMethod::MOBILE_TOP_UP)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        $data = request()->all('phone_number');
        Validator::make($data, [
            'phone_number' => ['required', 'string'],
        ])->validate();

        try {
            $mobileNumber = PhoneNumber::make($data['phone_number'])->formatE164();
        } catch (\Exception $exception) {
            Alert::makeWarning(__('payout.method.mobile_top_up.phone_number_not_found'));

            return redirect(url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30)));
        }

        if (AccountControlService::byMobileNumber()->isBanned($mobileNumber)) {
            AccountControlService::byMobileNumber()->ban(BlacklistInitiator::AUTOMATED, $mobileNumber, $this->person->id);

            return redirect()->route('home');
        }

        $url = url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.get-operator', now()->addMinutes(30));
        return redirect($url)->with([
            'phone_number' => $mobileNumber,
        ]);
    }

    public function getOperator()
    {
        $this->initialize();

        if ( ! $this->checkAvailability(PayoutMethod::MOBILE_TOP_UP)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        request()->session()->reflash();

        $phoneNumber = session()->get('phone_number');

        if (empty($phoneNumber)) {
            Alert::makeWarning(__('payout.method.mobile_top_up.phone_number_not_found'));

            return redirect(url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30)));
        }

        $countryCode = Country::getCountryIso2Code($this->person->country_id);

        $reloadly = new Reloadly();
        $foundOperator = $reloadly->getOperatorByMobileNumber($phoneNumber, $countryCode);

        request()->session()->flash('operator', $foundOperator);

        return view('profile.payout-v2.reloadly-mobile-top-up.operator', compact('phoneNumber',
            'foundOperator'));
    }

    public function handleOperator()
    {
        $this->initialize();

        if ( ! $this->checkAvailability(PayoutMethod::MOBILE_TOP_UP)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        request()->session()->reflash();

        $url = url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.get-plan', now()->addMinutes(30));
        return redirect($url);
    }

    public function getPlan()
    {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::MOBILE_TOP_UP)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        request()->session()->reflash();

        $phoneNumber = session()->get('phone_number');
        $operator = session()->get('operator');

        if (empty($phoneNumber) || empty($operator)) {
            Alert::makeWarning(__('payout.method.mobile_top_up.mobile_operator_not_found'));

            return redirect(url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30)));
        }

        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::AFRISIGHT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $allowedMaxBaseTransferAmount = $payoutOption->checkAllowedAvailableTransferAmount($balanceAmount, $balanceAmount);
        $minBaseTransferAmount = $payoutOption->getMinTransferLimit();

        $operator = $this->prepareOperator($operator, $minBaseTransferAmount, $allowedMaxBaseTransferAmount);
        if (empty($operator)) {
            Alert::makeWarning(__('payout.method.mobile_top_up.mobile_operator_not_found'));

            return redirect(url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30)));
        }

        if ( ! $operator['operatorThresholdAchieved']) {
            Alert::makeWarning(__('payout.method.mobile_top_up.mobile_operator_threshold_not_achieved', [
                'operator_threshold' => $operator['operatorThreshold'] . ' USD',
                'account_threshold'  => $allowedMaxBaseTransferAmount . ' USD',
            ]));

            return redirect()->route('profile.payout-v2.options');
        }

        return view('profile.payout-v2.reloadly-mobile-top-up.plan', compact('operator',
            'phoneNumber', 'allowedMaxBaseTransferAmount'));
    }

    public function request()
    {
        $this->initialize();

        if ( ! $payoutOption = $this->checkAvailability(PayoutMethod::MOBILE_TOP_UP)) {
            Alert::makeWarning(__('payout.country_not_set'));

            return redirect()->route('profile.basic-info.edit');
        }

        request()->session()->reflash();

        $phoneNumber = session()->get('phone_number');
        $operator = session()->get('operator');

        if (empty($phoneNumber) || empty($operator)) {
            Alert::makeWarning(__('payout.method.mobile_top_up.mobile_operator_not_found'));

            return redirect(url()->temporarySignedRoute('profile.payout-v2.mobile-top-up.start', now()->addMinutes(30)));
        }

        $data = request()->all('usd_amount');
        Validator::make($data, [
            'usd_amount' => ['required', 'numeric'],
        ])->validate();

        $balanceToUse = $this->accountService->separateCintBalance() ? [Balances::AFRISIGHT] : [];
        $balanceAmount = $this->accountService->getBalance(true, ...$balanceToUse);
        $allowedMaxBaseTransferAmount = $payoutOption->checkAllowedAvailableTransferAmount($data['usd_amount'], $balanceAmount);
        $minBaseTransferAmount = $payoutOption->getMinTransferLimit();

        $operator = $this->prepareOperator($operator, $minBaseTransferAmount, $allowedMaxBaseTransferAmount);
        Validator::make($data, [
            'usd_amount' => function ($attribute, $value, $fail) use ($operator) {
                if (round($value, 2) < round($operator['minAmount'], 2)) {
                    return $fail('Must be higher than the minimum amount.');
                }

                if (round($value, 2) > round($operator['maxAmount'], 2)) {
                    return $fail('Must be lower than the maximum amount.');
                }
            },
        ])->validate();

        $payoutSucceed = $payoutOption->requestPayout(
            $this->person->id,
            TransactionInitiator::ACCOUNT_HOLDER,
            $data['usd_amount'],
            $operator['operatorId'],
            $phoneNumber,
            null,
            [
                'local_amount'  => $operator['fx']['rate'] * $data['usd_amount'],
                'operator_id'   => $operator['operatorId'],
                'operator_name' => $operator['name'],
            ]
        );

        if ($payoutSucceed) {
            Alert::makeSuccess(__('payout.method.general.successful_request'));
        } else {
            Alert::makeWarning(__('payout.request_fail_try_later'));
        }

        return redirect()->route('profile.payout-v2.options');
    }

    /**
     * @param array $operator
     * @param float $minBaseTransferAmount
     * @param float $allowedMaxBaseTransferAmount
     * @return array|null
     */
    private function prepareOperator(array $operator, float $minBaseTransferAmount, float $allowedMaxBaseTransferAmount): ?array
    {
        $validator = Validator::make($operator, [
            'operatorId'              => ['required', 'numeric'],
            'name'                    => ['required', 'string'],
            'denominationType'        => ['required', Rule::in(['RANGE', 'FIXED'])],
            'destinationCurrencyCode' => ['required', 'string'],
            'fx'                      => ['required', 'array'],
            'fx.rate'                 => ['required', 'numeric'],
            'fx.currencyCode'         => ['required', 'string'],
        ]);

        // For RANGE type
        $validator->sometimes(['minAmount', 'maxAmount'], ['required', 'numeric'], function ($input) {
            return $input->denominationType === 'RANGE';
        });

        // For FIXED type
        $validator->sometimes(['fixedAmounts'], ['required', 'array'], function ($input) {
            return $input->denominationType === 'FIXED';
        });

        if ($validator->fails()) {
            Log::channel('reloadly')->error('Operator data not as expected.', array_merge($validator->errors()->toArray(), [
                'operator' => $operator,
            ]));

            return null;
        }

        // Handle local amounts
        $operatorThreshold = 9999;
        $operatorThresholdAchieved = false;
        $minAmount = $minBaseTransferAmount;
        $maxAmount = $allowedMaxBaseTransferAmount;
        $fixedAmounts = [];
        if ($operator['denominationType'] === 'RANGE') {
            $operatorThreshold = $operator['minAmount'];
            $operatorThresholdAchieved = $allowedMaxBaseTransferAmount > $operator['minAmount'];
            $minAmount = $minBaseTransferAmount > $operator['minAmount'] ? $minBaseTransferAmount : $operator['minAmount'];
            $maxAmount = $operator['maxAmount'] > $allowedMaxBaseTransferAmount ? $allowedMaxBaseTransferAmount : $operator['maxAmount'];
        } elseif ($operator['denominationType'] === 'FIXED') {
            foreach ($operator['fixedAmounts'] as $fixedAmount) {
                if ($fixedAmount < $operatorThreshold) {
                    $operatorThreshold = $fixedAmount;
                }

                if ($fixedAmount < $minAmount || $fixedAmount > $maxAmount) {
                    continue;
                }

                $fixedAmounts[] = [
                    'base_amount'  => $fixedAmount,
                    'local_amount' => $operator['fx']['rate'] * $fixedAmount,
                ];
            }

            $operatorThresholdAchieved = $allowedMaxBaseTransferAmount > $operatorThreshold;
        }

        return [
            'operatorId'                => $operator['operatorId'],
            'name'                      => $operator['name'],
            'denominationType'          => $operator['denominationType'],
            'minAmount'                 => $minAmount,
            'maxAmount'                 => $maxAmount,
            'fixedAmounts'              => $fixedAmounts,
            'fx'                        => $operator['fx'],
            'minLocalAmount'            => $operator['fx']['rate'] * $minAmount,
            'maxLocalAmount'            => $operator['fx']['rate'] * $maxAmount,
            'operatorThreshold'         => $operatorThreshold,
            'operatorThresholdAchieved' => $operatorThresholdAchieved,
        ];
    }
}
