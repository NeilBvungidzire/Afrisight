<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\RespondentStatus;
use App\Constants\TransactionType;
use App\Libraries\Payout\Constants\PayoutMethod;
use App\Libraries\Payout\Constants\TransactionInitiator;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Libraries\Project\ProjectParticipants;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\Target;
use App\Transaction;
use Illuminate\Http\RedirectResponse;

class ProjectController extends BaseController {

    private $pageTitle = 'Project';

    public function showRespondents(string $projectCode) {
        $this->authorize('manage-projects');

        $this->pageTitle = 'Engaged respondents';

        $respondentsQuery = Respondent::query()
            ->with('invitations')
            ->where('project_code', $projectCode)
            ->orderBy('updated_at', 'desc');

        if ($endResults = request()->query('result')) {
            $respondentsQuery->whereIn('current_status', explode(',', $endResults));
        }

        // Limit the numbers of items retrieved.
        $limit = request()->query('limit') ?? 30;

        $respondents = $respondentsQuery->paginate($limit);

        foreach ($respondents->items() as &$respondent) {
            if ($respondent->target_hits === null) {
                $respondent->target_hits = [];

                continue;
            }

            $respondent['client_end_status'] = isset($respondent->status_history[RespondentStatus::STARTED]);

            $respondent['client_denial'] = false;
            if (isset($respondent->status_history[RespondentStatus::TARGET_SUITABLE])) {
                $respondent['client_denial'] = (isset($respondent->status_history[RespondentStatus::STARTED])
                    && (isset($respondent->status_history[RespondentStatus::QUOTA_FULL])
                        || isset($respondent->status_history[RespondentStatus::DISQUALIFIED])
                    ));
            }
        }

        $targetCriteria = Target::query()
            ->where('project_code', $projectCode)
            ->get(['id', 'criteria', 'value'])
            ->keyBy('id');

        $statuses = RespondentStatus::getConstants();

        $title = $this->pageTitle;
        return view('admin.projects.respondents',
            compact('projectCode', 'respondents', 'targetCriteria', 'statuses', 'title'));
    }

    public function handleParticipants(string $projectCode): RedirectResponse {
        $this->authorize('admin-projects');

        $this->pageTitle = 'Handle participants';

        $payoutTransaction = [
            'pdi_1'   => 0.20,
            'tsr_001' => 0.20,
            'msi_001' => 0.50,
        ];

        $createPayoutTransaction = isset($payoutTransaction[$projectCode]);
        $payoutAmountReduction = $payoutTransaction[$projectCode] ?? 0;
        $notHandledTransactions = ProjectParticipants::approveProjectParticipantsTransactions($projectCode,
            function (Transaction $transaction, bool $updated) use ($createPayoutTransaction, $payoutAmountReduction) {
                if ( ! $updated) {
                    return;
                }

                if ( ! $createPayoutTransaction) {
                    return;
                }

                Transaction::create([
                    'person_id'  => $transaction->person_id,
                    'type'       => TransactionType::REWARD_PAYOUT,
                    'initiator'  => TransactionInitiator::AUTOMATED,
                    'amount'     => -($transaction->amount) + $payoutAmountReduction,
                    'new_status' => TransactionStatus::APPROVED,
                    'meta_data'  => [
                        'payout_method' => PayoutMethod::ALTERNATIVE,
                    ],
                ]);
            });

        if ( ! empty($notHandledTransactions)) {
            Alert::makeDanger('Could not approve transactions for all participants.');

            return redirect()->route('admin.projects.index');
        }

        Alert::makeSuccess('Transactions for all participants approved.');

        return redirect()->route('admin.projects.index');
    }

    public function switchStatus(string $projectCode): RedirectResponse {
        $this->authorize('manage-projects');

        $this->pageTitle = 'Switch status';

        ProjectUtils::switchIsLiveStatus($projectCode);

        return redirect()->back();
    }
}
