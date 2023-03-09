<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Target
 *
 * @package App
 * @property string $project_code
 * @property string $criteria
 * @property string $value
 * @property string $status
 * @property Collection $targetTracks
 */
class Target extends Model {

    protected $fillable = [
        'project_code',
        'criteria',
        'value',
        'status',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return BelongsToMany
     */
    public function targetTracks()
    {
        return $this->belongsToMany(TargetTrack::class, 'target_target_track');
    }
}
