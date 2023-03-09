<?php

namespace App\Http\Controllers\Admin;

use App\Constants\RespondentStatus;
use App\Constants\TransactionType;
use App\Http\Controllers\Controller;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\Transaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManageProjectParticipantsController extends Controller {

    private $pageTitle = 'Manage participants';

    /**
     * @param string $projectCode
     *
     * @return View
     * @throws AuthorizationException
     */
    public function filter(string $projectCode): View
    {
        $this->authorize('admin-projects');

        $statuses = RespondentStatus::getConstants();

        $title = $this->pageTitle;
        return view('admin.projects.participant_management.filter', compact('projectCode', 'statuses', 'title'));
    }

    public function setFilter(string $projectCode): RedirectResponse
    {
        $this->authorize('admin-projects');

        $statuses = request()->get('statuses');
        $uuids = trim(request()->get('uuids'))
            ? explode("\r\n", trim(request()->get('uuids')))
            : null;

        return redirect()->route('admin.projects.manage_participants.select', ['project_code' => $projectCode])
            ->with('filters', ['statuses' => $statuses, 'uuids' => $uuids]);
    }

    public function select(string $projectCode)
    {
        $this->authorize('admin-projects');

        $filters = request()->session()->get('filters');
        if (empty($filters['statuses']) && empty($filters['uuids'])) {
            return redirect()->route('admin.projects.manage_participants.filter', ['project_code' => $projectCode]);
        }

        request()->session()->keep(['filters']);

        $respondentQuery = Respondent::query()
            ->where('project_code', $projectCode)
            ->whereIn('current_status', $filters['statuses']);

        if ( ! empty($filters['uuids'])) {
            $respondentQuery->whereIn('uuid', $filters['uuids']);
        }

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 50;

        $respondents = $respondentQuery->paginate($limit);
        if ($respondents->count() === 0) {
            return redirect()->route('admin.projects.manage_participants.filter', ['project_code' => $projectCode]);
        }

        // Get transaction for respondents
        $transactionsQuery = Transaction::query();
        foreach ($respondents->pluck('id')->toArray() as $id) {
            $transactionsQuery->orWhereJsonContains('meta_data->respondent_id', (int)$id);
        }
        $transactions = $transactionsQuery->get()->keyBy('meta_data.respondent_id');
        foreach ($respondents as $respondent) {
            if ($transaction = $transactions->get($respondent->id)) {
                $respondent->transaction = $transaction;
            }
        }

        $actions = $this->getActions();

        $title = $this->pageTitle;
        return view('admin.projects.participant_management.select', compact('projectCode', 'respondents', 'actions', 'title'));
    }

    public function chooseAction(string $projectCode): RedirectResponse
    {
        $this->authorize('admin-projects');

        $action = request()->get('action');
        $respondentsId = request()->get('respondent_id');

        if ( ! array_key_exists($action, $this->getActions()) || empty($respondentsId)) {
            return redirect()->route('admin.projects.manage_participants.filter', ['project_code' => $projectCode]);
        }

        request()->session()->keep(['filters']);

        switch ($action) {

            case 'approve_rewards':
                return $this->approveRewards($projectCode, $respondentsId);

            case 'mark_completed':
                return $this->markAsCompleted($projectCode, $respondentsId);

            case 'mark_disqualified':
                return $this->markAsDisqualified($projectCode, $respondentsId);
        }

        return redirect()->route('admin.projects.manage_participants.select', ['project_code' => $projectCode]);
    }

    private function markAsCompleted(string $projectCode, array $respondentsId): RedirectResponse
    {
        Respondent::query()
            ->where('project_code', $projectCode)
            ->whereIn('id', $respondentsId)
            ->whereNotIn('current_status', [RespondentStatus::COMPLETED])
            ->each(function (Respondent $respondent) {
                $status = RespondentStatus::COMPLETED;
                $currentTimeString = date('Y-m-d H:i:s');

                $respondent->update([
                    'current_status' => $status,
                    'status_history' => array_merge($respondent->status_history, [
                        $status => $currentTimeString,
                    ]),
                ]);

                ProjectUtils::incrementHitQuotas($respondent->project_code, $respondent->target_hits, true, true);
            });

        return redirect()->route('admin.projects.manage_participants.select', ['project_code' => $projectCode]);
    }

    private function markAsDisqualified(string $projectCode, array $respondentsId): RedirectResponse
    {
        Respondent::query()
            ->where('project_code', $projectCode)
            ->whereIn('id', $respondentsId)
            ->whereNotIn('current_status', [RespondentStatus::DISQUALIFIED])
            ->each(function (Respondent $respondent) {
                $status = RespondentStatus::POST_DISQUALIFIED;
                $currentTimeString = date('Y-m-d H:i:s');

                $respondent->update([
                    'current_status' => $status,
                    'status_history' => array_merge($respondent->status_history, [
                        $status => $currentTimeString,
                    ]),
                ]);

                ProjectUtils::decrementHitQuotas($respondent->project_code, $respondent->target_hits, true, true);
            });

        return redirect()->route('admin.projects.manage_participants.select', ['project_code' => $projectCode]);
    }

    private function approveRewards(string $projectCode, array $respondentsId): RedirectResponse
    {
        Transaction::query()
            ->whereIn('type', [TransactionType::SURVEY_REWARDING, TransactionType::REFERRAL_REWARDING])
            ->where('status', TransactionStatus::REQUESTED)
            ->where(function (Builder $query) use ($respondentsId, $projectCode) {
                foreach ($respondentsId as $respondentId) {
                    $query->orWhere(static function (Builder $query) use ($projectCode, $respondentId) {
                        $query->whereJsonContains('meta_data->project_code', $projectCode);
                        $query->whereJsonContains('meta_data->respondent_id', (int)$respondentId);
                    });
                }
            })
            ->each(function (Transaction $transaction) {
                $transaction->new_status = TransactionStatus::APPROVED;
                $transaction->save();
            });

        return redirect()->back();
    }

    private function getActions(): array
    {
        return [
            'approve_rewards'   => 'Approve rewards',
            'mark_completed'    => 'Mark as completed after quality check',
            'mark_disqualified' => 'Mark as disqualified after quality check',
        ];
    }
}
