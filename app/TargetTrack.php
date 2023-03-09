<?php

namespace App;

use App\Libraries\Project\ProjectUtils;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class TargetTrack
 *
 * @package App
 * @property string     $project_code
 * @property int        $quota_amount
 * @property float      $quota_percentage
 * @property int        $count
 * @property string     $relation
 * @property array      $reference
 * @property int        $needed_number
 * @property float|null $sample_weight
 * @property int|null   $sample_size
 * @property Collection $targets
 */
class TargetTrack extends Model {

    protected $fillable = [
        'project_code',
        'quota_amount',
        'quota_percentage',
        'count',
        'relation',
        'reference',
    ];

    protected $casts = [
        'quota_amount'     => 'integer',
        'quota_percentage' => 'decimal:11',
        'count'            => 'integer',
        'reference'        => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    /**
     * @return BelongsToMany
     */
    public function targets()
    {
        return $this->belongsToMany(Target::class, 'target_target_track');
    }

    // ------------------------------------------------------------------------
    // Mutators
    //

    public function getAchievedPercentageAttribute(): float
    {
        $result = 0;
        if ($this->quota_amount != 0) {
            $result = $this->count / $this->quota_amount;
        }

        return (float)$result;
    }

    public function getRemainingPercentageAttribute(): float
    {
        if ($this->quota_amount == 0) {
            return (float)0;
        }

        $achieved = $this->count / $this->quota_amount;
        if ($achieved >= 1) {
            return (float)0;
        }

        return (float)1 - $achieved;
    }

    public function getNeededNumberAttribute(): int
    {
        if ($this->quota_amount == 0) {
            return 0;
        }

        $need = $this->quota_amount - $this->count;
        if ($need <= 0) {
            return 0;
        }

        return $need;
    }

    public function getTargetPathAttribute(): ?string
    {
        $targetPaths = ProjectUtils::getProjectTargetPaths($this->project_code, true);
        $targetTrackCriteria = array_keys($this->reference);
        if (empty($targetPaths) || empty($targetTrackCriteria)) {
            return null;
        }

        foreach ($targetPaths as $targetPath => $targetPathCriteria) {
            if (count(array_intersect($targetPathCriteria, $targetTrackCriteria)) !== count($targetPathCriteria)) {
                continue;
            }

            return (string)$targetPath;
        }

        return null;
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param int   $totalBatchSize
     * @param float $priorityWeight
     *
     * @return int
     */
    public function calculateBatchSize(int $totalBatchSize, float $priorityWeight): int
    {
        return (int)round(($priorityWeight * $totalBatchSize));
    }
}
