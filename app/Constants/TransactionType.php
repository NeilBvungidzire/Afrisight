<?php

namespace App\Constants;

use ReflectionClass;

class TransactionType {

    // Rewarding
    public const ACTIVITY_REWARDING = 'ACTIVITY_REWARDING';
    public const SURVEY_REWARDING = 'SURVEY_REWARDING';
    public const REFERRAL_REWARDING = 'REFERRAL_REWARDING';
    // Payout
    public const REWARD_PAYOUT = 'REWARD_PAYOUT';

    /**
     * @return array
     */
    public static function getConstants(): array
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }

    public static function getPayoutTypeConstants(): array
    {
        return [
            self::REWARD_PAYOUT,
        ];
    }

    public static function getRewardingTypeConstants(): array
    {
        return [
            self::ACTIVITY_REWARDING,
            self::SURVEY_REWARDING,
            self::REFERRAL_REWARDING,
        ];
    }
}
