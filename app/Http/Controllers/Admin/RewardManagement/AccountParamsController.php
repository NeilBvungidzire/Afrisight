<?php

namespace App\Http\Controllers\Admin\RewardManagement;

use App\Http\Controllers\Controller;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Person;

class AccountParamsController extends Controller {

    public function edit(int $id)
    {
        $this->authorize('reward-management');

        /** @var Person $person */
        $person = Person::withTrashed()->find($id, [
            'account_params',
        ]);
        if ( ! $person) {
            return redirect()->route('admin.reward_management.member-account');
        }

        $accountParams = $person->account_params;
        $payoutOptions = PayoutMethod::getConstants();
        $payoutParams = $accountParams['payout'] ?? [];

        return view('admin.reward-management.edit-account-params', compact('id', 'payoutOptions', 'payoutParams'));
    }

    public function update(int $id)
    {
        $this->authorize('reward-management');

        $person = Person::withTrashed()->find($id, [
            'id',
            'account_params',
        ]);
        if ( ! $person) {
            return redirect()->route('admin.reward_management.member-account');
        }

        $payoutParams = request()->get('payout');
        $payoutDataToSave = [];
        foreach ($payoutParams as $payoutMethod => $params) {
            if ( ! is_null($value = $params['minimal_threshold'] ?? null)) {
                $payoutDataToSave['payout'][$payoutMethod]['minimal_threshold'] = $value;
            }

            if ( ! is_null($value = $params['maximum_amount'] ?? null)) {
                $payoutDataToSave['payout'][$payoutMethod]['maximum_amount'] = $value;
            }
        }

        $accountParams = $person->account_params;
        $accountParams = array_merge((array)$accountParams, $payoutDataToSave);
        $person->account_params = $accountParams;
        $person->save();

        return redirect()->route('admin.reward_management.member-account');
    }
}
