<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncentivePackage extends Model {

    use SoftDeletes;

    protected $fillable = [
        'project_code',
        'reference_id',
        'loi',
        'usd_amount',
        'local_currency',
        'local_amount',
    ];

    protected $casts = [
        'reference_id' => 'integer',
        'loi'          => 'integer',
        'usd_amount'   => 'float',
        'local_amount' => 'float',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_code');
    }
}
