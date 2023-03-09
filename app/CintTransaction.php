<?php

namespace App;

use App\Libraries\Payout\Constants\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CintTransaction extends Model {

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
                $transaction->status = TransactionStatus::CREATED;
            }

            if ( ! isset($transaction->balance_adjusted)) {
                $transaction->balance_adjusted = false;
            }
        });
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
}
