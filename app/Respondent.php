<?php

namespace App;

use App\Constants\RespondentStatus;
use App\Libraries\Payout\Constants\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Respondent extends Model {

    protected $fillable = [
        'person_id',
        'uuid',

        'project_code',
        'target_hits',
        'current_status',
        'incentive_amount',
        'actual_loi',
        'status_history',
        'is_test',
        'meta_data',
    ];

    protected $casts = [
        'target_hits'      => 'array',
        'status_history'   => 'array',
        'is_test'          => 'boolean',
        'meta_data'        => 'array',
        'incentive_amount' => 'decimal:2',
        'actual_loi'       => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $respondent) {
            if ( ! isset($respondent->uuid)) {
                $respondent->uuid = Str::uuid();
            }

            if ($referralCode = $respondent->meta_data['referral_id'] ?? null) {
                Referral::adjustTotalReferrals($referralCode, 1);
            }
        });

        static::updating(function (self $respondent) {

            // Make sure a reward transaction exists when completed a survey.
            if ($respondent->isDirty('current_status') && $respondent->current_status === RespondentStatus::COMPLETED) {
                Transaction::firstOrCreateRespondentRewarding(
                    $respondent->person_id,
                    $respondent->id,
                    $respondent->project_code,
                    $respondent->incentive_amount
                );

                if ($referralCode = $respondent->meta_data['referral_id'] ?? null) {
                    Transaction::firstOrCreateRespondentReferralRewarding(
                        $referralCode,
                        $respondent->id,
                        $respondent->project_code
                    );

                    Referral::adjustTotalSuccessfulReferrals($referralCode, 1);
                }
            }

            // Make sure the reward transaction is denied, if exists, when complete status is unset.
            if ($respondent->getOriginal('current_status') === RespondentStatus::COMPLETED
                && $respondent->current_status !== RespondentStatus::COMPLETED
            ) {
                if ($referralCode = $respondent->meta_data['referral_id'] ?? null) {
                    $referralTransaction = Transaction::firstOrCreateRespondentReferralRewarding(
                        $referralCode,
                        $respondent->id,
                        $respondent->project_code
                    );
                    if ($referralTransaction) {
                        $referralTransaction->new_status = TransactionStatus::DENIED;
                        $referralTransaction->save();
                    }

                    Referral::adjustTotalSuccessfulReferrals($referralCode, -1);
                }

                $respondentRewarding = Transaction::firstOrCreateRespondentRewarding(
                    $respondent->person_id,
                    $respondent->id,
                    $respondent->project_code,
                    $respondent->incentive_amount
                );
                if ($respondentRewarding) {
                    $respondentRewarding->new_status = TransactionStatus::DENIED;
                    $respondentRewarding->save();
                }
            }
        });
    }

    // ------------------------------------------------------------------------
    // Relations
    //

    /**
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * @return HasMany
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(RespondentInvitation::class);
    }
}
