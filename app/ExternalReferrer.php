<?php

namespace App;

use App\Constants\TransactionType;
use App\Libraries\Payout\Constants\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ExternalReferrer extends Model {

    protected $fillable = [
        'name',
        'contacts',
        'payout_transactions',
    ];

    protected $casts = [
        'contacts'               => 'array',
        'payout_transactions'    => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    public function referrals(): MorphMany
    {
        return $this->morphMany(Referral::class, 'referrerable');
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param string     $initiator
     * @param float      $amount
     * @param array|null $metaData
     * @return bool
     */
    public function saveApprovedPayoutTransaction(string $initiator, float $amount, array $metaData = null): bool
    {
        $transactions = $this->payout_transactions ?? [];
        $transactions[] = [
            'type'      => TransactionType::REWARD_PAYOUT,
            'initiator' => $initiator,
            'amount'    => -abs($amount),
            'status'    => TransactionStatus::APPROVED,
            'meta_data' => $metaData,
        ];
        $this->payout_transactions = $transactions;
        return $this->save();
    }
}
