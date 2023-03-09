<?php

namespace App\Observers;

use App\Constants\DataPointAttribute;
use App\Jobs\SyncWithAudienceProfileService;
use App\Libraries\ProfilingQuestionnaire\ProfilingQuestionnaire;
use App\MemberProfilingAnswer;
use App\Services\DataPointService\DataPointService;
use Illuminate\Support\Carbon;

class MemberProfilingAnswerObserver {

    /**
     * Handle the member profiling answer "created" event.
     *
     * @param  MemberProfilingAnswer  $memberProfilingAnswer
     * @return void
     */
    public function created(MemberProfilingAnswer $memberProfilingAnswer) {
        $this->handleDataPoint($memberProfilingAnswer);

        ProfilingQuestionnaire::handleCache(authUser()->person_id, ProfilingQuestionnaire::ADD, [$memberProfilingAnswer->id]);
    }

    /**
     * Handle the member profiling answer "updated" event.
     *
     * @param  MemberProfilingAnswer  $memberProfilingAnswer
     * @return void
     */
    public function updated(MemberProfilingAnswer $memberProfilingAnswer) {
        //
    }

    /**
     * Handle the member profiling answer "deleted" event.
     *
     * @param  MemberProfilingAnswer  $memberProfilingAnswer
     * @return void
     */
    public function deleted(MemberProfilingAnswer $memberProfilingAnswer) {
        ProfilingQuestionnaire::handleCache(authUser()->person_id, ProfilingQuestionnaire::REMOVE, [$memberProfilingAnswer->id]);
    }

    /**
     * Handle the member profiling answer "restored" event.
     *
     * @param  MemberProfilingAnswer  $memberProfilingAnswer
     * @return void
     */
    public function restored(MemberProfilingAnswer $memberProfilingAnswer) {
        //
    }

    /**
     * Handle the member profiling answer "force deleted" event.
     *
     * @param  MemberProfilingAnswer  $memberProfilingAnswer
     * @return void
     */
    public function forceDeleted(MemberProfilingAnswer $memberProfilingAnswer) {
        //
    }

    private function handleDataPoint(MemberProfilingAnswer $memberProfilingAnswer): void {
        if ( ! $dataPointAttribute = $memberProfilingAnswer->data_point_attribute ?? null) {
            return;
        }

        if ( ! $dataPointValues = $memberProfilingAnswer->data_point_values ?? null) {
            return;
        }

        $dataPointService = new DataPointService();

        switch ($dataPointAttribute) {

            case DataPointAttribute::SUBDIVISION_CODE:
                $dataPointService->place()->setSubdivisionCodeDataPoint(
                    $memberProfilingAnswer->person_id,
                    $dataPointValues[0],
                    'PROFILING_QUESTIONNAIRE'
                );

                SyncWithAudienceProfileService::dispatch(
                    $memberProfilingAnswer->person_id,
                    ['subdivision_code' => $dataPointValues[0], 'last_active' => (new Carbon())->format('Y-m-d')]
                )->delay(now()->addSecond());
                break;
        }
    }
}
