<?php

namespace App;

use App\Constants\TransactionType;
use App\Libraries\Payout\Constants\TransactionInitiator;
use App\Libraries\Payout\Constants\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model {

    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'uuid',

        'type',
        'initiator',
        'amount',
        // Used to set the local currency, local amount, fx rate, type of transaction, external transaction ID, method, etc.
        'meta_data',
        'balance_adjusted',

        // Mutator
        'new_status',
    ];

    protected $casts = [
        'amount'           => 'float',
        'meta_data'        => 'array',
        'status_history'   => 'array',
        'balance_adjusted' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Set default status in case not set during creation.
        static::creating(function (self $transaction) {
            if ( ! isset($transaction->status)) {
                $transaction->status = TransactionStatus::REQUESTED;
            }

            if ( ! isset($transaction->balance_adjusted)) {
                $transaction->balance_adjusted = false;
            }
        });
    }

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    // ------------------------------------------------------------------------
    // Mutators
    //

    /**
     * Set current and status history with new_status mutator field.
     *
     * @param string $value
     */
    public function setNewStatusAttribute(string $value)
    {
        $this->status = $value;
        $this->status_history = array_merge((array)$this->status_history, [
            date('Y-m-d H:i:s') => $value,
        ]);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * Add transaction for person who successfully participated in a survey, but not yet fully approved reward.
     *
     * @param int    $personId
     * @param int    $respondentId
     * @param string $projectCode
     * @param float  $amount Is mandatory if you want to attempt creating a transaction.
     *
     * @return Transaction|null
     */
    public static function firstOrCreateRespondentRewarding(
        int $personId,
        int $respondentId,
        string $projectCode,
        float $amount = 0
    ): ?Transaction
    {
        $type = TransactionType::SURVEY_REWARDING;

        /** @var self|null $existingTransaction */
        $existingTransaction = self::query()
            ->where('person_id', $personId)
            ->where('type', $type)
            ->whereJsonContains('meta_data->project_code', $projectCode)
            ->whereJsonContains('meta_data->respondent_id', $respondentId)
            ->first();

        if ($existingTransaction) {
            return $existingTransaction;
        }

        return self::create([
            'person_id'  => $personId,
            'type'       => $type,
            'initiator'  => TransactionInitiator::AUTOMATED,
            'amount'     => $amount,
            'new_status' => TransactionStatus::REQUESTED,
            'meta_data'  => [
                'respondent_id' => $respondentId,
                'project_code'  => $projectCode,
            ],
        ]);
    }

    /**
     * @param string $referralCode
     * @param int    $respondentId
     * @param string $projectCode
     * @param float  $amount
     * @return Transaction|null
     */
    public static function firstOrCreateRespondentReferralRewarding(
        string $referralCode,
        int $respondentId,
        string $projectCode,
        $initiator = TransactionInitiator::AUTOMATED
    ): ?Transaction
    {
        // Make sure this referrer is a person instance.
        $referral = Referral::query()
            ->where('referrerable_type', Person::class)
            ->where('code', $referralCode)
            ->first(['referrerable_id', 'amount_per_successful_referral']);
        if ( ! $referral) {
            return null;
        }

        $type = TransactionType::REFERRAL_REWARDING;

        /** @var self|null $existingTransaction */
        $existingTransaction = self::query()
            ->where('person_id', $referral->referrerable_id)
            ->where('type', $type)
            ->whereJsonContains('meta_data->project_code', $projectCode)
            ->whereJsonContains('meta_data->respondent_id', $respondentId)
            ->whereJsonContains('meta_data->referral_code', $referralCode)
            ->first();

        if ($existingTransaction) {
            return $existingTransaction;
        }

        return self::create([
            'person_id'  => $referral->referrerable_id,
            'type'       => $type,
            'initiator'  => $initiator,
            'amount'     => $referral->amount_per_successful_referral,
            'new_status' => TransactionStatus::REQUESTED,
            'meta_data'  => [
                'respondent_id' => $respondentId,
                'project_code'  => $projectCode,
                'referral_code' => $referralCode,
            ],
        ]);
    }
}
