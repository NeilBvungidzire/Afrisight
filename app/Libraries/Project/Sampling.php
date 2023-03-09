<?php

namespace App\Libraries\Project;

use Illuminate\Support\Collection;

class Sampling {

    /**
     * @var Collection
     */
    private $quotas;

    /**
     * @param Collection $quotas
     */
    public function __construct(Collection $quotas)
    {
        $this->quotas = $quotas;
    }

    /**
     * @param int   $totalSampleSize
     * @param array $applicableSamplingCriteria
     * @return Collection
     */
    public function generateSamplingSelectors(int $totalSampleSize, array $applicableSamplingCriteria): Collection
    {
        // Group by quota group.
        $quotasGrouped = $this->quotas->groupBy('group');

        $this->calculateSampleSize($quotasGrouped);

        // Make sure the quotas are ordered by biggest size based on number of quotas breakdowns.
        if ($quotasGrouped->count() > 1) {
            $quotasGrouped = $quotasGrouped->sortBy(static function ($quotas) {
                return $quotas->count();
            });
        }

        $samplingCrossJoin = [[]];
        $index = 0;
        foreach ($quotasGrouped as $quotas) {
            $index++;
            $append = [];

            foreach ($samplingCrossJoin as $product) {
                foreach ($quotas as $quota) {
                    $product[$index] = $quota;

                    $append[] = $product;
                }
            }

            $samplingCrossJoin = $append;
        }

        $selectors = collect();
        foreach ($samplingCrossJoin as $quotas) {
            $selector = new SampleSelector();
            $selector->sample_quotas = $quotas;
            $selector->selectors = [];
            $selector->size = $totalSampleSize;

            foreach ($quotas as $quota) {
                foreach ($quota->selectors as $criteria => $targetId) {
                    if ( ! in_array($criteria, $applicableSamplingCriteria, true)) {
                        continue;
                    }

                    $selector->selectors[$criteria] = $targetId;
                }

                $selector->size = (int)round($quota->weight * $selector->size);
            }

            $selectors->add($selector);
        }

        return $selectors;
    }

    /**
     * Calculate and set the sampling weight based on the numbers needed per quota (target track).
     *
     * @param Collection $quotasGrouped
     * @return void
     */
    private function calculateSampleSize(Collection $quotasGrouped): void
    {
        foreach ($quotasGrouped as $quotasInGroup) {
            $totalNeededOpen = $quotasInGroup->sum('size');

            $quotasInGroup->map(static function (SampleQuota $quota) use ($totalNeededOpen) {
                $quota->weight = $quota->size / $totalNeededOpen;
            });
        }
    }
}
