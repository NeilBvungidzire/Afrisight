<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Blacklist extends Model {

    use SoftDeletes;

    protected $fillable = [
        'banned_by',
        'related_data',
        'banned_person_ids',
        'initiator',
    ];

    protected $casts = [
        'related_data'      => 'array',
        'banned_person_ids' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * Check if someone is already blacklisted based on different params.
     *
     * @param  string  $by Options: email, bank_account.
     * @param  array  $params By "email", email_address required. By bank_account, country_code, bank_code and account_number required.
     * @return bool
     */
    public static function isBlacklisted(string $by, array $params): bool {
        $query = DB::table('blacklists');

        if ($by === 'email') {
            if (empty($params['email_address'])) {
                return false;
            }

            return $query->where('related_data->email', $params['email_address'])->exists();
        }

        if ($by === 'bank_account') {
            if (empty($params['country_code']) || empty($params['bank_code']) || empty($params['account_number'])) {
                return false;
            }

            return $query
                ->where('related_data->country_code', $params['country_code'])
                ->where('related_data->bank_code', $params['bank_code'])
                ->where('related_data->account_number', $params['account_number'])
                ->exists();
        }

        return false;
    }
}
