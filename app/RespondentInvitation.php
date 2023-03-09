<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RespondentInvitation extends Model {

    protected $fillable = [
        'uuid',
        'respondent_id',
        'type',
        'status',
        'publish_date',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (RespondentInvitation $respondentInvitation) {
            if ( ! isset($respondentInvitation->uuid)) {
                $respondentInvitation->uuid = Str::uuid();
            }
        });
    }

    // ------------------------------------------------------------------------
    // Relations
    //

    /**
     * @return BelongsTo
     */
    public function respondent()
    {
        return $this->belongsTo(Respondent::class);
    }
}
