<?php

namespace App\Http\Controllers\CAPI;

use App\Constants\RespondentStatus;
use App\Http\Controllers\Controller;
use App\OtherRespondent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class SurveyRedirectsController extends Controller {

    public function completed(): RedirectResponse {
        $status = RespondentStatus::COMPLETED;

        $this->handleRespondent($status);

        return $this->handleFeedback($status);
    }

    public function terminated(): RedirectResponse {
        $status = RespondentStatus::DISQUALIFIED;

        $this->handleRespondent($status);

        return $this->handleFeedback($status);
    }

    public function quotaReached(): RedirectResponse {
        $status = RespondentStatus::QUOTA_FULL;

        $this->handleRespondent($status);

        return $this->handleFeedback($status);
    }

    public function closed(): RedirectResponse {
        $status = RespondentStatus::CLOSED;

        $this->handleRespondent($status);

        return $this->handleFeedback($status);
    }

    public function screenOut(): RedirectResponse {
        $status = RespondentStatus::SCREEN_OUT;

        $this->handleRespondent($status);

        return $this->handleFeedback($status);
    }

    /**
     * @return RedirectResponse|View
     */
    public function feedback() {
        $status = request()->query('status');

        if ($status === RespondentStatus::COMPLETED) {
            return view('redirects.capi.completed');
        }

        if ($status === RespondentStatus::DISQUALIFIED) {
            return view('redirects.capi.disqualified');
        }

        if ($status === RespondentStatus::QUOTA_FULL) {
            return view('redirects.capi.quota_reached');
        }

        if ($status === RespondentStatus::CLOSED) {
            return view('redirects.capi.closed');
        }

        if ($status === RespondentStatus::SCREEN_OUT) {
            return view('redirects.capi.screen-out');
        }

        return redirect()->route('home');
    }

    /**
     * @param  string  $status
     * @return RedirectResponse
     */
    private function handleFeedback(string $status): RedirectResponse {
        return redirect()->route('survey-redirect.capi.feedback', ['status' => $status]);
    }

    private function handleRespondent(string $status): void {
        $respondentId = null;
        if ($respondentIdParam = request()->query('rid_param')) {
            $respondentId = request()->query($respondentIdParam);
        }
        if ( ! $respondentId) {
            $respondentId = request()->query('id') ?? request()->query('rid');
        }
        if ( ! $respondentId) {
            return;
        }

        /** @var OtherRespondent|null $respondent * */
        $respondent = OtherRespondent::query()
            ->where('uuid', $respondentId)
            ->first();
        if ($respondent) {
            $respondent->update([
                'new_status' => $status,
                'loi' => $this->getLOI($respondent->status_history, date('Y-m-d H:i:s')),
            ]);
        }
    }

    /**
     * @param  array  $statusHistory
     * @param  string  $newTime
     * @return int|null
     */
    private function getLOI(array $statusHistory, string $newTime): ?int {
        $statusHistory = array_flip($statusHistory);
        if ( ! isset($statusHistory[RespondentStatus::STARTED])) {
            return null;
        }

        $startTime = Date::createFromFormat('Y-m-d H:i:s', $statusHistory[RespondentStatus::STARTED]);
        $endTime = Date::createFromFormat('Y-m-d H:i:s', $newTime);

        return $startTime->diffInMinutes($endTime, false);
    }
}
