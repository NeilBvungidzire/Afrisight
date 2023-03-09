<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $uuid
 * @property string $external_id
 * @property string $sample_code
 * @property string $source_id
 * @property array $meta_data
 * @property integer $loi
 * @property string $interviewer_id
 * @property string $new_status
 * @property-read array $status_history
 * @property-read string $status
 */
class OtherRespondent extends Model {

    protected $fillable = [
        'uuid',
        'external_id',
        'sample_code',
        'source_id',
        'meta_data',
        'loi',
        'interviewer_id',

        // Mutator
        'new_status',
    ];

    protected $casts = [
        'status_history' => 'array',
        'meta_data'      => 'array',
        'loi'            => 'integer',
    ];

    protected static function boot(): void {
        parent::boot();

        static::creating(static function (self $otherRespondent) {
            if ( ! isset($otherRespondent->uuid)) {
                $otherRespondent->uuid = Str::uuid();
            }
            if ( ! isset($otherRespondent->external_id)) {
                $otherRespondent->external_id = Str::uuid();
            }
        });
    }

    /**
     * Set current and status history with new_status mutator field.
     *
     * @param  string  $value
     */
    public function setNewStatusAttribute(string $value): void {
        $this->status = $value;
        $this->status_history = array_merge((array) $this->status_history, [
            date('Y-m-d H:i:s') => $value,
        ]);
    }
}
