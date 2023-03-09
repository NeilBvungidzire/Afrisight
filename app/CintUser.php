<?php

namespace App;

use App\Cint\CintApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CintUser extends Model {

    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'cint_id',
        'reward_balance',
        'meta_data',
        'allowed_sync',
    ];

    protected $casts = [
        'reward_balance' => 'decimal:2',
        'meta_data'      => 'array',
        'allowed_sync'   => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Set default status in case not set during creation.
        static::creating(function (self $cintUser) {
            if (empty($cintUser->reward_balance)) {
                $cintUser->reward_balance = 0;
            }
        });
    }

    // ------------------------------------------------------------------------
    // Relationships
    //

    /**
     * @return BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    // ------------------------------------------------------------------------
    // Attributes
    //

    /**
     * @return bool
     */
    public function getCanRequestPayoutAttribute()
    {
        if (empty($this->person->country_id)) {
            return false;
        }

        $isoAlpha2 = $this->person->country->iso_alpha_2;
        if (empty($isoAlpha2)) {
            return false;
        }

        return self::canRequestPayout($isoAlpha2);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param string $isoAlpha2
     *
     * @return bool
     */
    public static function canRequestPayout(string $isoAlpha2)
    {
        $cintPanelPaymentMethods = [];
        foreach (config('cint.panels') as $panelConfig) {
            if ($panelConfig['country']['iso_alpha_2'] === $isoAlpha2) {
                $cintPanelPaymentMethods = $panelConfig['payment_methods'] ?? [];
            }
        }

        foreach ($cintPanelPaymentMethods as $panelPaymentMethodKey => $panelPaymentMethod) {
            if ( ! $panelPaymentMethod['active']) {
                unset($cintPanelPaymentMethods[$panelPaymentMethodKey]);
            }
        }

        return ! empty($cintPanelPaymentMethods);
    }

    /**
     * @param int $paymentMethodId
     *
     * @return bool
     */
    public function requestPayout(int $paymentMethodId)
    {
        $isoAlpha2 = $this->person->country->iso_alpha_2;

        $client = new CintApi();
        $panelResource = $client->retrievePanel($isoAlpha2);
        if ($panelResource->hasFailed()) {
            return false;
        }

        $panelistResource = $panelResource->retrievePanelistByEmail($this->person->email);
        if ($panelistResource->hasFailed()) {
            // User does not exist on Cint.
            return false;
        }

        return ! $panelistResource->payoutTransaction($paymentMethodId)->hasFailed();
    }
}
