<?php

namespace App\Jobs;

use App\AudienceEngagement;
use App\Constants\InvitationType;
use App\Constants\TargetStatus;
use App\Libraries\Project\AudienceQueryBuilder;
use App\Libraries\Project\ProjectUtils;
use App\Libraries\Project\SampleSelector;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EngageSampleBatch implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * @var SampleSelector
     */
    protected $sampleSelector;

    /**
     * @var AudienceEngagement
     */
    protected $audienceEngagement;

    /**
     * @var string
     */
    protected $runKey;

    public function tags(): array
    {
        return [
            'AutoAudienceEngagement',
            'SampleBatch',
        ];
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $runKey, int $audienceEngagementId, SampleSelector $sampleSelector)
    {
        $this->audienceEngagement = AudienceEngagement::query()
            ->where('id', $audienceEngagementId)
            ->where('is_on', true)
            ->first([
                'id',
                'project_code',
                'total_engaged',
                'engagement_limit',
            ]);
        $this->sampleSelector = $sampleSelector;
        $this->runKey = $runKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ( ! $this->audienceEngagement) {
            return;
        }

        $targets = DB::table('targets')
            ->where('status', TargetStatus::OPEN)
            ->whereIn('id', $this->sampleSelector->selectors)
            ->pluck('value', 'criteria')
            ->map(function ($value) {
                return explode('|', $value);
            });

        $queryBuilder = new AudienceQueryBuilder();

        if ( ! empty($values = $targets['country'] ?? null)) {
            try {
                $queryBuilder->setCountries($values);
            } catch (Exception $exception) {
                Log::error($exception->getMessage(), $exception->getTrace());
            }
        }

        if ( ! empty($values = $targets['gender'] ?? null)) {
            $queryBuilder->setGenders($values);
        }

        if ( ! empty($values = $targets['age_range'] ?? null)) {
            $queryBuilder->setAgeRanges($values);
        }

        if ( ! empty($values = $targets['subdivision'] ?? null)) {
            $queryBuilder->setSubdivisionCodes($values);
        }

        $projectCode = $this->audienceEngagement->project_code;
        if ( ! empty($values = ProjectUtils::getLanguageRestrictions($projectCode))) {
            $queryBuilder->setLanguage($values);
        }

        $queryBuilder->excludeRespondents($projectCode);

        // Exclusion by participation in other samples.
        $exclusionByOtherSamples = ProjectUtils::getExclusions($projectCode) ?? [];
        foreach ($exclusionByOtherSamples as $exclusionByOtherSampleProjectCode => $exclusionByOtherSampleStatuses) {
            $queryBuilder->excludeRespondents($exclusionByOtherSampleProjectCode, $exclusionByOtherSampleStatuses);
        }

        // Exclude persons already in queue.
        $queuedPersonsId = [];
        try {
            $queuedPersonsId = (array)cache()->get($this->getCacheKey());
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }

        $queryBuilder->excludedPersons($queuedPersonsId);

        $queryBuilder->orderBy();

        $feasiblePersonsId = $queryBuilder
            ->getQuery()
            ->limit($this->sampleSelector->size)
            ->pluck('id')
            ->toArray();

        $incentivePackage = ProjectUtils::getIncentivePackage($projectCode);

        $channel = InvitationType::EMAIL;
        $invitationTypeHandler = ProjectUtils::getInvitationTypeHandler($projectCode, $channel);

        $batchCode = Str::random(10);
        $additionalMetaData = ['batch_code' => $batchCode];

        $this->addToQueue($feasiblePersonsId);

        if (app()->environment('production')) {
            EngageAudienceForSurvey::dispatch(
                $feasiblePersonsId,
                $projectCode,
                $incentivePackage,
                $channel,
                $additionalMetaData,
                $invitationTypeHandler
            );
        }

        $totalEngaged = count($feasiblePersonsId);
        $this->audienceEngagement->batchRun($totalEngaged, [
            'code'      => $batchCode,
            'size'      => $totalEngaged,
            'meta_data' => $this->getMetaData($this->sampleSelector),
        ]);
    }

    private function getMetaData(SampleSelector $sampleSelector): array
    {
        $result = [
            'run_key'   => $this->runKey,
            'selectors' => $sampleSelector->selectors,
            'size'      => $sampleSelector->size,
        ];

        foreach ($sampleSelector->sample_quotas as $sample_quota) {
            $result['sample_quotas'][] = $sample_quota->id;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        return "ENGAGEMENT_QUEUE_{$this->audienceEngagement->project_code}";
    }

    /**
     * Add (probably) engaged respondents to queue to make sure not to engage before record is changed in DB.
     *
     * @param array $ids
     * @return void
     */
    private function addToQueue(array $ids): void
    {
        try {
            $previousPersonIdsList = (array)cache()->pull($this->getCacheKey());
            cache()->put(
                $this->getCacheKey(),
                array_merge($previousPersonIdsList, $ids),
                now()->addHour()
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }
}
