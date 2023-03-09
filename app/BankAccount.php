<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model {

    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'country_code',
        'type',
        'bank_code',
        'account_number',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return BelongsTo
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param int                            $personId
     * @param string                         $countryCode
     * @param string                         $method
     * @param null|string|string[]|int|int[] $ids
     * @return Collection
     */
    public static function getAvailablePersonBankAccounts(int $personId, string $countryCode, string $method, $ids = null): Collection
    {
        $bankAccountQuery = BankAccount::query()
            ->where('person_id', $personId)
            ->where('country_code', $countryCode)
            ->where('type', $method);

        if (is_int($ids) || is_string($ids)) {
            $bankAccountQuery->where('id', $ids);
        } elseif (is_array($ids)) {
            $bankAccountQuery->whereIn('id', $ids);
        }

        $bankAccounts = $bankAccountQuery->get();
        if ($bankAccounts->isEmpty()) {
            return $bankAccounts;
        }

        $flutterwave = new Libraries\Flutterwave\Flutterwave();
        $banksAvailable = $flutterwave->banks()->getAllBanks($countryCode, $method);
        $banksAvailable = collect($banksAvailable)->keyBy('code');

        foreach ($bankAccounts as $key => $bankAccount) {
            $bankAccount['available'] = true;

            if ( ! isset($banksAvailable[$bankAccount['bank_code']])) {
                $bankAccount['available'] = false;
            } else {
                $bankAccount['name'] = $banksAvailable[$bankAccount['bank_code']]['name'];
            }
        }

        return $bankAccounts;
    }
}
