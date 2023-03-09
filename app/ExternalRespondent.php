<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExternalRespondent extends Model {

    protected $fillable = [
        'uuid',
        'person_id',
        'external_id',

        'project_id',
        'project_code',
        'source',
        'meta_data',

        // Mutator
        'new_status',
    ];

    protected $casts = [
        'status_history' => 'array',
        'meta_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $externalRespondent) {
            if ( ! isset($externalRespondent->uuid)) {
                $externalRespondent->uuid = Str::uuid();
            }
        });
    }

    // ------------------------------------------------------------------------
    // Related models
    //

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

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
