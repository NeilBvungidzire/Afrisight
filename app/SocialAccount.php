<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model {

    protected $fillable = [
        'user_id',
        'provider_id',
        'provider',
        'scopes_permission',
        'other_data',
    ];

    protected $casts = [
        'other_data' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
