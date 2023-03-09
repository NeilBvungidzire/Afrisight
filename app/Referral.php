<?php

namespace App;

use App\Constants\ReferralType;
use App\Constants\RespondentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Referral extends Model {

    protected $fillable = [
        'referrerable_type',
        'referrerable_id',
        'code',
        'type',
        'amount_per_successful_referral',
        'total_referrals',
        'total_successful_referrals',
        'notes',
        'data',
    ];

    protected $casts = [
        'amount_per_successful_referral' => 'float',
        'total_referrals'                => 'integer',
        'total_successful_referrals'     => 'integer',
        'notes'                          => 'array',
        'data'                           => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    public function referrerable(): MorphTo
    {
        return $this->morphTo();
    }

    // ------------------------------------------------------------------------
    // Mutators
    //

    /**
     * @return float
     */
    public function getTotalConversionAmountAttribute(): float
    {
        return $this->total_successful_referrals * $this->amount_per_successful_referral;
    }

    public function getConversionRateAttribute(): float
    {
        return $this->total_successful_referrals
            ? number_format((int)$this->total_successful_referrals / (int)$this->total_referrals, 2)
            : 0;
    }

    public function getUrlAttribute(): ?string
    {
        return self::generateUrl($this->type, $this->code);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @return string
     */
    public static function generateCode(): string
    {
        return strtolower(Str::random(12));
    }

    /**
     * @param string $type
     * @param string $code
     * @return string|null
     */
    public static function generateUrl(string $type, string $code): ?string
    {
        if ($type === ReferralType::RESPONDENT_RECRUITMENT) {
            return route('inflow', ['projectId' => $code]);
        }

        return null;
    }

    /**
     * @param string $referralCode
     * @param int    $byNumber
     * @return bool
     */
    public static function adjustTotalReferrals(string $referralCode, int $byNumber): bool
    {
        return self::query()
            ->where('code', $referralCode)
            ->increment('total_referrals', $byNumber);
    }

    /**
     * @param string $referralCode
     * @param int    $byNumber
     * @return bool
     */
    public static function adjustTotalSuccessfulReferrals(string $referralCode, int $byNumber): bool
    {
        return self::query()
            ->where('code', $referralCode)
            ->increment('total_successful_referrals', $byNumber);
    }

    public function recountReferrals(): Referral
    {
        $countsPerStatus = DB::table('respondents')
            ->selectRaw(DB::raw('current_status AS status, COUNT(*) AS count'))
            ->where('project_code', $this->data['project_code'])
            ->where('meta_data->referral_id', $this->code)
            ->groupBy(['status'])
            ->get();

        $totalSuccessfulReferrals = 0;
        $totalReferrals = 0;
        foreach ($countsPerStatus as $record) {
            if ($record->status === RespondentStatus::COMPLETED) {
                $totalSuccessfulReferrals += $record->count;
            }

            $totalReferrals += $record->count;
        }

        $this->total_referrals = $totalReferrals;
        $this->total_successful_referrals = $totalSuccessfulReferrals;

        return $this;
    }
}
