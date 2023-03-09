<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string   $project_code
 * @property int      $engagement_limit
 * @property int      $total_engaged
 * @property int      $batch_size
 * @property int      $minutes_between_batches
 * @property Carbon   $last_batch_time
 * @property string[] $time_windows
 * @property string[] $applicable_criteria
 * @property bool     $is_on
 * @property array    $meta_data
 */
class AudienceEngagement extends Model {

    protected $fillable = [
        'project_code',
        'engagement_limit',
        'total_engaged',
        'batch_size',
        'minutes_between_batches',
        'last_batch_time', // UTC timezone
        'time_windows', // UTC timezone
        'applicable_criteria',
        'is_on',
        'meta_data',
    ];

    protected $casts = [
        'engagement_limit'        => 'integer',
        'total_engaged'           => 'integer',
        'batch_size'              => 'integer',
        'minutes_between_batches' => 'integer',
        'last_batch_time'         => 'datetime',
        'time_windows'            => 'array',
        'applicable_criteria'     => 'array',
        'is_on'                   => 'boolean',
        'meta_data'               => 'array',
    ];

    // ------------------------------------------------------------------------
    // Related models
    //

    public function batches(): HasMany
    {
        return $this->hasMany(AudienceEngagementBatch::class);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * Get the next sample in line for auto engagement.
     *
     * @param string[] $select
     * @return AudienceEngagement|null
     */
    public static function getNext(array $select = []): ?AudienceEngagement
    {
        $todayDate = new Carbon();
        $todayDayOfWeek = strtoupper($todayDate->format('l'));

        $select = array_merge($select, [
            'id',
            'time_windows',
        ]);
        $openRecords = self::query()
            ->where('is_on', true)
            ->whereRaw('total_engaged < engagement_limit')
            ->where(static function (Builder $query) use ($todayDayOfWeek) {
                $query->orWhereNotNull('time_windows->GENERAL');
                $query->orWhereNotNull("time_windows->$todayDayOfWeek");
            })
            ->orderBy('last_batch_time')
            ->get($select);

        foreach ($openRecords as $record) {
            // Try to get today's otherwise most recent window.
            $window = $record->time_windows[$todayDayOfWeek] ?? $record->time_windows['GENERAL'] ?? null;
            if ( ! $window) {
                continue;
            }

            // Check if record falls within time window.
            $windowRanges = explode('-', $window);
            $windowBegin = (new Carbon())->setTimeFromTimeString($windowRanges[0]);
            $windowEnd = (new Carbon())->setTimeFromTimeString($windowRanges[1]);
            if ( ! $todayDate->isBetween($windowBegin, $windowEnd)) {
                continue;
            }

            return $record;
        }

        return null;
    }

    /**
     * Mark as run at this moment.
     *
     * @param int   $totalEngaged
     * @param array $batchData
     * @return bool
     */
    public function batchRun(int $totalEngaged, array $batchData): bool
    {
        $this->last_batch_time = new Carbon();
        $this->total_engaged += $totalEngaged;

        // Maximum number of requested engagement reached.
        if ($this->total_engaged >= $this->engagement_limit) {
            $this->is_on = false;
        }

        $this->batches()->create($batchData);

        return $this->save();
    }
}
