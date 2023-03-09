<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\ReferralType;
use App\Constants\RespondentStatus;
use App\Constants\TransactionType;
use App\ExternalReferrer;
use App\Http\Controllers\Controller;
use App\Jobs\RecountReferrals;
use App\Libraries\Payout\Constants\TransactionInitiator;
use App\Person;
use App\Referral;
use App\Transaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReferralManagementController extends Controller {

    /**
     * @return View
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('referral-management');

        $boxOptions = [
            'referral_code',
            'project_code',
        ];

        $filters = [
            'type'      => [
                'label'         => 'Type (all)',
                'type'          => 'select',
                'current_value' => request()->query('type'),
                'options'       => ReferralType::getConstants(),
            ],
            'box_type'  => [
                'label'         => 'Specific (none)',
                'type'          => 'select',
                'current_value' => request()->query('box_type'),
                'options'       => array_combine($boxOptions, $boxOptions),
            ],
            'box_value' => [
                'label'         => 'Value',
                'type'          => 'text',
                'current_value' => request()->query('box_value'),
            ],
        ];

        $referralQuery = Referral::query();

        // Filter by type
        if ($filters['type']['current_value'] ?? null) {
            $referralQuery->where('type', $filters['type']['current_value']);
        }

        // Specific box filtering
        if ( ! empty($filters['box_type']['current_value']) && $filters['box_value']['current_value'] !== null) {
            $boxType = $filters['box_type']['current_value'];
            $boxValue = $filters['box_value']['current_value'];

            if ($boxType === 'referral_code') {
                $referralQuery->where('code', $boxValue);
            }
            if ($boxType === 'project_code') {
                $referralQuery->where('data->project_code', $boxValue);
            }
        }

        // Ordering
        $referralQuery->orderByDesc('created_at');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit', 30);

        $referrals = $referralQuery->paginate($limit);
        $referrerTypes = array_flip([
            'Panel member'      => Person::class,
            'External referrer' => ExternalReferrer::class,
        ]);

        return view('admin.referral_management.index', compact('filters', 'referrals',
            'referrerTypes'));
    }

    /**
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function filter(): RedirectResponse
    {
        $this->authorize('reward-management');

        $filters = request()->all(['box_type', 'box_value', 'type']);

        return redirect()->route('admin.referral_management.overview', $filters);
    }

    public function viewReferral(int $id)
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $referrerTypes = array_flip([
            'Panel member'      => Person::class,
            'External referrer' => ExternalReferrer::class,
        ]);
        $url = Referral::generateUrl($referral->type, $referral->code);

        return view('admin.referral_management.view-referral', compact('url', 'referral',
            'referrerTypes'));
    }

    public function recountReferral(int $id): RedirectResponse
    {
        $this->authorize('reward-management');

        /** @var Referral $referral */
        $referral = Referral::find($id);
        if ( ! $referral) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $referral->recountReferrals()->save();

        return redirect()->back();
    }

    public function recountAllReferral(): RedirectResponse
    {
        $this->authorize('reward-management');

        $referrals = DB::table('referrals')->pluck('id');
        foreach ($referrals as $referralId) {
            RecountReferrals::dispatch($referralId);
        }

        Alert::makeInfo("Recounting in the background. It will take some time to process all {$referrals->count()} referrals.");

        return redirect()->back();
    }

    /**
     * @return View
     * @throws AuthorizationException
     */
    public function createReferral(): View
    {
        $this->authorize('reward-management');

        $type = request()->query('type');
        $types = ReferralType::getConstants();
        $code = Referral::generateCode();
        $url = ($code && $type) ? Referral::generateUrl($type, $code) : null;

        // Referrer types
        $referrerTypes = [
            'Panel member'      => Person::class,
            'External referrer' => ExternalReferrer::class,
        ];

        return view('admin.referral_management.create-referral', compact('types', 'code',
            'url', 'type', 'referrerTypes'));
    }

    public function storeReferral(): RedirectResponse
    {
        $this->authorize('reward-management');

        $data = request()->all();

        Validator::make($data, [
            'code'                           => ['required', 'string', 'unique:referrals,code'],
            'type'                           => ['required', 'string', Rule::in(ReferralType::getConstants())],
            'referrer_type'                  => ['required', 'string', Rule::in([Person::class, ExternalReferrer::class])],
            'referrer_id'                    => ['required', 'numeric'],
            'amount_per_successful_referral' => ['required', 'numeric'],
            'data.project_code'              => ['required', 'string'],
            'data.public_reference'          => ['required', 'string'],
        ])->validate();

        $data['referrerable_type'] = $data['referrer_type'];
        $data['referrerable_id'] = $data['referrer_id'];

        if (Referral::create($data)) {
            Alert::makeSuccess('Referral instance created successfully.');
        } else {
            Alert::makeWarning('Could not create referral instance!');
        }

        return redirect()->route('admin.referral_management.overview');
    }

    public function editReferral(int $id)
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $url = Referral::generateUrl($referral->type, $referral->code);

        // Referrer types
        $referrerTypes = [
            'Panel member'      => Person::class,
            'External referrer' => ExternalReferrer::class,
        ];
        $types = ReferralType::getConstants();
        $type = $referral->type;

        return view('admin.referral_management.edit-referral', compact('referral', 'url',
            'referrerTypes', 'types', 'type'));
    }

    public function updateReferral(int $id): RedirectResponse
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $data = request()->all();

        Validator::make($data, [
            'code'                           => ['required', 'string', Rule::unique('referrals')->ignore($referral->id)],
            'type'                           => ['required', 'string', Rule::in(ReferralType::getConstants())],
            'referrer_type'                  => ['required', 'string', Rule::in([Person::class, ExternalReferrer::class])],
            'referrer_id'                    => ['required', 'numeric'],
            'amount_per_successful_referral' => ['required', 'numeric'],
            'data.project_code'              => ['required', 'string'],
            'data.public_reference'          => ['required', 'string'],
        ])->validate();

        $data['referrerable_type'] = $data['referrer_type'];
        $data['referrerable_id'] = $data['referrer_id'];
        $data['data'] = array_merge((array)$referral->data, $data['data']);

        if ($referral->update($data)) {
            Alert::makeSuccess('Referral instance saved successfully.');
        } else {
            Alert::makeWarning('Could not save referral instance!');
        }

        return redirect()->route('admin.referral_management.overview');
    }

    public function indexReferrer()
    {
        $boxOptions = [
            'email',
            'phone',
            'referral_code',
            'project_code',
        ];

        $filters = [
            'box_type'  => [
                'label'         => 'Specific (none)',
                'type'          => 'select',
                'current_value' => request()->query('box_type'),
                'options'       => array_combine($boxOptions, $boxOptions),
            ],
            'box_value' => [
                'label'         => 'Value',
                'type'          => 'text',
                'current_value' => request()->query('box_value'),
            ],
        ];

        $externalReferrerQuery = ExternalReferrer::query();

        // Specific box filtering
        if ( ! empty($filters['box_type']['current_value']) && $filters['box_value']['current_value'] !== null) {
            $boxType = $filters['box_type']['current_value'];
            $boxValue = $filters['box_value']['current_value'];

            if ($boxType === 'email') {
                $externalReferrerQuery->whereJsonContains('contacts->email', $boxValue);
            }

            if ($boxType === 'phone') {
                $externalReferrerQuery->whereJsonContains('contacts->phone', $boxValue);
            }
        }

        // Ordering
        $externalReferrerQuery->orderByDesc('created_at');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit', 30);

        $referrers = $externalReferrerQuery->paginate($limit);

        return view('admin.referral_management.referrer-index', compact('filters', 'referrers'));
    }

    /**
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function filterReferrer(): RedirectResponse
    {
        $this->authorize('reward-management');

        $filters = request()->all(['box_type', 'box_value']);

        return redirect()->route('admin.referral_management.overview_referrer', $filters);
    }

    public function createReferrer()
    {
        $this->authorize('reward-management');

        $action = route('admin.referral_management.store_referrer');
        return view('admin.referral_management.create-referrer', compact('action'));
    }

    public function storeReferrer(): RedirectResponse
    {
        $this->authorize('reward-management');

        $data = request()->all();

        Validator::make($data, [
            'name'           => ['required', 'string'],
            'contacts.email' => ['nullable', 'string', 'email'],
            'contacts.phone' => ['nullable', 'string'],
        ])->validate();

        if (ExternalReferrer::create($data)) {
            Alert::makeSuccess('Referrer created successfully.');
        } else {
            Alert::makeWarning('Could not create referrer!');
        }

        return redirect()->route('admin.referral_management.overview_referrer');
    }

    public function editReferrer(int $id)
    {
        $this->authorize('reward-management');

        if ( ! $referrer = ExternalReferrer::find($id)) {
            Alert::makeWarning('Could not find referrer!');

            return redirect()->route('admin.referral_management.overview_referrer');
        }

        $action = route('admin.referral_management.update_referrer', ['id' => $referrer->id]);
        return view('admin.referral_management.create-referrer', compact('referrer', 'action'));
    }

    public function updateReferrer(int $id): RedirectResponse
    {
        $this->authorize('reward-management');

        if ( ! $referrer = ExternalReferrer::find($id)) {
            Alert::makeWarning('Could not find referrer!');

            return redirect()->route('admin.referral_management.overview_referrer');
        }

        $data = request()->all();

        Validator::make($data, [
            'name'           => ['required', 'string'],
            'contacts.email' => ['nullable', 'string', 'email'],
            'contacts.phone' => ['nullable', 'string'],
        ])->validate();

        if ($referrer->update($data)) {
            Alert::makeSuccess('Referrer update successfully.');
        } else {
            Alert::makeWarning('Could not update referrer!');
        }

        return redirect()->route('admin.referral_management.overview_referrer');
    }

    public function viewReferrer(int $id)
    {
        $this->authorize('reward-management');

        $referrer = ExternalReferrer::with('referrals')->find($id);
        if ( ! $referrer) {
            Alert::makeWarning('Could not find referrer!');

            return redirect()->route('admin.referral_management.overview_referrer');
        }

        return view('admin.referral_management.view-referrer', compact('referrer'));
    }

    public function handleReferralTransactions(int $id)
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $existingTransactions = [];
        $referredRespondents = [];
        if ($referral->referrerable_type === Person::class && ! empty($referral->referrerable_id)) {
            $existingTransactions = Transaction::query()
                ->where('person_id', $referral->referrerable_id)
                ->where('type', TransactionType::REFERRAL_REWARDING)
                ->whereJsonContains('meta_data->referral_code', $referral->code)
                ->get();

            if ($referral->type === ReferralType::RESPONDENT_RECRUITMENT) {
                $referredRespondents = DB::table('respondents')
                    ->where('project_code', $referral->data['project_code'])
                    ->where('current_status', RespondentStatus::COMPLETED)
                    ->where('meta_data->referral_id', $referral->code)
                    ->get(['id', 'project_code']);
            }
        }

        return view('admin.referral_management.handle-referral-transactions', compact('referral',
            'existingTransactions', 'referredRespondents'));
    }

    public function generateReferralRewardTransaction(int $id, int $respondentId): RedirectResponse
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        Transaction::firstOrCreateRespondentReferralRewarding(
            $referral->code,
            $respondentId,
            $referral->data['project_code'],
            TransactionInitiator::ADMINISTRATOR
        );

        return redirect()->back();
    }
}
