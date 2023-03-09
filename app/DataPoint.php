<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataPoint extends Model {

    protected $fillable = [
        'person_id',
        'attribute',
        'value',
        'source_type',
        'source_meta_data',
    ];

    protected $casts = [
        'source_meta_data' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return BelongsTo
     */
    public function person(): BelongsTo {
        return $this->belongsTo(Person::class);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * Create or update datapoint depending on whether the attribute exists for the given person.
     *
     * @param  int  $personId
     * @param  string  $attribute
     * @param  string  $value
     * @param  string  $sourceType
     * @param  array|null  $sourceMetaData
     *
     * @return void
     */
    public static function saveDatapoint(
        int $personId,
        string $attribute,
        string $value,
        string $sourceType,
        array $sourceMetaData = null
    ): void {
        $existingData = [
            'person_id' => $personId,
            'attribute' => $attribute,
        ];
        $newData = [
            'value'       => $value,
            'source_type' => $sourceType,
        ];
        if ( ! is_null($sourceMetaData)) {
            $newData['source_meta_data'] = $sourceMetaData;
        }

        static::updateOrCreate($existingData, $newData);
    }
}
