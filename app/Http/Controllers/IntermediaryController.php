<?php

namespace App\Http\Controllers;

use App\Constants\RespondentStatus;
use App\ExternalRespondent;
use App\Libraries\Project\ProjectUtils;
use App\SampleProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class IntermediaryController extends Controller {

    /**
     * @return RedirectResponse
     */
    public function start(): RedirectResponse
    {
        if ( ! $projectId = request()->query('project-code')) {
            return redirect()->route('home');
        }

        $sampleProvider = SampleProvider::query()->where('project_id', $projectId)->first();
        if ( ! $sampleProvider) {
            return redirect()->route('home');
        }

        if ( ! $respondentId = request()->query('id')) {
            return redirect()->route('home');
        }

        $projectCodes = $sampleProvider->project_codes;
        $projectCode = array_rand(array_flip($projectCodes));

        $externalRespondent = ExternalRespondent::create([
            'external_id'  => $respondentId,
            'project_id'   => $projectId,
            'project_code' => $projectCode,
            'source'       => $sampleProvider->source,
            'new_status'   => RespondentStatus::STARTED,
            'meta_data'    => request()->query(),
        ]);

        $uuid = (string)$externalRespondent->uuid;

        return redirect()->away(ProjectUtils::generateSurveyLink($projectCode, 'live', $uuid));
    }

    /**
     * @param string $uuid
     * @param string $status
     *
     * @return RedirectResponse
     */
    public function finish(string $uuid, string $status): RedirectResponse
    {
        $externalRespondent = ExternalRespondent::query()
            ->where('uuid', $uuid)
            ->where('status', RespondentStatus::STARTED)
            ->first();

        if ( ! $externalRespondent) {
            Log::error('external respondent not found', [
                'uuid'   => $uuid,
                'status' => $status,
            ]);

            return redirect()->route('home');
        }

        $sampleProvider = SampleProvider::query()->where('project_id', $externalRespondent->project_id)->first();
        if ( ! $sampleProvider) {
            return redirect()->route('home');
        }

        $externalRespondent->new_status = $status;
        $externalRespondent->save();

        if ( ! $redirectLink = $sampleProvider->generateEndResultUrl($status, $externalRespondent->external_id)) {
            return redirect()->route('home');
        }

        return redirect()->away($redirectLink);
    }
}
