<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AudienceEngagementBatch extends Model {

    protected $fillable = [
        'audience_engagement_id',
        'code',
        'size',
        'meta_data',
    ];

    protected $casts = [
        'size'      => 'integer',
        'meta_data' => 'array',
    ];
}
