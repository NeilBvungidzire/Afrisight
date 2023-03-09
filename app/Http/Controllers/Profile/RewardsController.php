<?php

namespace App\Http\Controllers\Profile;

use App\Constants\TransactionType;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Transaction;
use Illuminate\Support\Carbon;

class RewardsController extends BaseController {

    public function __invoke()
    {
        $rewards = $this->getRewardList();

        return view('profile.rewards.index', compact('rewards'));
    }

    private function getRewardList()
    {
        // Make sure the user and person model exist for the authenticated user.
        $user = authUser();
        if ( ! $user) {
            return abort(403);
        }
        $personId = $user->person_id;

        $transactions = Transaction::query()
            ->where('person_id', $personId)
            ->whereIn('type', [TransactionType::ACTIVITY_REWARDING, TransactionType::REFERRAL_REWARDING, TransactionType::SURVEY_REWARDING])
            ->whereIn('status', [TransactionStatus::REQUESTED, TransactionStatus::PENDING, TransactionStatus::APPROVED, TransactionStatus::DENIED])
            ->get();

        $result = [];
        foreach ($transactions as $transaction) {
            $result[] = $this->generateRewardItem($transaction->updated_at, $transaction->status,
                $transaction->type, $transaction->amount);
        }

        return $result;
    }

    /**
     * @param Carbon $date
     * @param string $status
     * @param string $type
     * @param float $amount
     *
     * @return array
     */
    private function generateRewardItem(Carbon $date, string $status, string $type, float $amount)
    {
        $statusText = __('profile.sub_pages.rewards.status.default');
        switch ($status) {
            case TransactionStatus::REQUESTED:
            case TransactionStatus::PENDING:
                $statusText = __('profile.sub_pages.rewards.status.pending');
                break;

            case TransactionStatus::DENIED:
                $statusText = __('profile.sub_pages.rewards.status.denied');
                break;

            case TransactionStatus::APPROVED:
                $statusText = __('profile.sub_pages.rewards.status.approved');
                break;
        }

        $typeText = __('profile.sub_pages.rewards.type.default');
        switch ($type) {
            case TransactionType::REFERRAL_REWARDING:
                $typeText = __('profile.sub_pages.rewards.type.referral');
                break;

            case TransactionType::SURVEY_REWARDING:
                $typeText = __('profile.sub_pages.rewards.type.survey');
                break;
        }

        return [
            'date'   => $date->format('d-m-Y'),
            'status' => $statusText,
            'type'   => $typeText,
            'amount' => $amount,
        ];
    }
}
