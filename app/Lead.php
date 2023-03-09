<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model {

    use SoftDeletes;

    protected $fillable = [
        'person_id',
        'email',
        'name',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Relations
    //

    /**
     * @return MorphOne
     */
    public function respondent()
    {
        return $this->morphOne(Respondent::class, 'respondentable');
    }

    /**
     * @return BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
