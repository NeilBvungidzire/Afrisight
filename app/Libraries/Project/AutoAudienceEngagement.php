<?php

namespace App\Libraries\Project;

use App\AudienceEngagement;
use App\Jobs\EngageSampleBatch;
use App\TargetTrack;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class AutoAudienceEngagement {

    /**
     * @var array
     */
    private $generalCriteria = [
        'country',
    ];

    /**
     * @var AudienceEngagement
     */
    private $audienceEngagement;

    /**
     * @var string[]
     */
    private $allowedCriteria = [
        'country',
        'gender',
        'age_range',
        'state',
    ];

    /**
     * Unique identifier for each time the audience engagement runs.
     *
     * @var string
     */
    private $runKey;

    /**
     * @param AudienceEngagement $audienceEngagement
     */
    public function __construct(AudienceEngagement $audienceEngagement)
    {
        $this->audienceEngagement = $audienceEngagement;
        $this->runKey = Str::random(10);
    }

    /**
     * The list of all required column names needed from the table.
     *
     * @return string[]
     */
    public static function getRequiredColumns(): array
    {
        return [
            'id',
            'project_code',
            'applicable_criteria',
            'batch_size',
        ];
    }

    public static function canRun(): bool
    {
        try {
            return ( ! cache()->has(self::getKey()));
        } catch (InvalidArgumentException|Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        return false;
    }

    private static function getKey(): string
    {
        return 'AUTO_AUDIENCE_ENGAGEMENT_RUNNING';
    }

    /**
     * @return int Delay interval for each selector in seconds.
     */
    private static function delayInterval(): int
    {
        return 15;
    }

    /**
     * @return int Number of times, based on the numbers of selectors, the batch has to run.
     */
    public function engage(): int
    {
        $audienceSelectors = $this->getAudienceSelectors();

        if ($audienceSelectors->isEmpty()) {
            return false;
        }

        $delay = now();
        foreach ($audienceSelectors as $audienceSelector) {
            if ($audienceSelector->size < 1) {
                continue;
            }

            EngageSampleBatch::dispatch($this->runKey, $this->audienceEngagement->id, $audienceSelector)
                ->delay($delay);

            $delay->addSeconds(self::delayInterval());
        }

        $totalBatchesInQueue = $audienceSelectors->count();
        try {
            cache()->set(self::getKey(), 1, now()->addSeconds(($totalBatchesInQueue * self::delayInterval())));
        } catch (Exception|InvalidArgumentException $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        return $totalBatchesInQueue;
    }

    /**
     * @return Collection
     */
    private function getAudienceSelectors(): Collection
    {
        if ( ! $this->isUsable()) {
            return new Collection();
        }

        return $this->generateAudienceSelectors($this->prepareSamplingInput());
    }

    /**
     * @return bool
     */
    private function isUsable(): bool
    {
        if ( ! ProjectUtils::isLive($this->audienceEngagement->project_code)) {
            return false;
        }

        if (empty($this->audienceEngagement->applicable_criteria)) {
            return false;
        }

        if ( ! in_array('country', $this->audienceEngagement->applicable_criteria)) {
            return false;
        }

        if (empty($this->audienceEngagement->batch_size)) {
            return false;
        }

        return true;
    }

    /**
     * @param bool $withoutGeneralCriteria Ignore criteria which always applies.
     * @return string[]
     */
    private function getApplicableSamplingCriteria(bool $withoutGeneralCriteria = false): array
    {
        $applicableCriteria = array_intersect($this->allowedCriteria, $this->audienceEngagement->applicable_criteria);

        if ($withoutGeneralCriteria) {
            return array_diff($applicableCriteria, $this->generalCriteria);
        }

        return $applicableCriteria;
    }

    /**
     * @return EloquentCollection
     */
    private function getApplicableTargetTracks(): EloquentCollection
    {
        $projectCode = $this->audienceEngagement->project_code;
        $applicableSamplingCriteria = $this->getApplicableSamplingCriteria(true);

        return TargetTrack::query()
            ->where('project_code', $projectCode)
            ->whereRaw('count < quota_amount')
            ->where(static function (Builder $queryBuilder) use ($applicableSamplingCriteria) {
                foreach ($applicableSamplingCriteria as $criteria) {
                    $queryBuilder->orWhereNotNull("reference->$criteria");
                }
            })
            ->get([
                'id',
                'reference',
                'count',
                'quota_amount',
                'reference',
            ]);
    }

    /**
     * @param Collection $quotas
     * @return Collection
     */
    private function generateAudienceSelectors(Collection $quotas): Collection
    {
        $applicableCriteria = $this->getApplicableSamplingCriteria();
        return (new Sampling($quotas))->generateSamplingSelectors($this->audienceEngagement->batch_size, $applicableCriteria);
    }

    /**
     * @return Collection
     */
    private function prepareSamplingInput(): Collection
    {
        $quotas = collect();
        $this->getApplicableTargetTracks()->each(function (TargetTrack $targetTrack) use ($quotas) {
            $sampleQuota = new SampleQuota();
            $sampleQuota->id = $targetTrack->id;
            $sampleQuota->group = implode('.', array_keys($targetTrack->reference));
            $sampleQuota->size = $targetTrack->needed_number;
            $sampleQuota->selectors = $targetTrack->reference;

            $quotas->add($sampleQuota);
        });

        return $quotas;
    }
}
