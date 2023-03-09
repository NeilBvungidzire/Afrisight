<?php

namespace App\Http\Controllers;

use App\Constants\Gender;
use App\Constants\InvitationType;
use App\Constants\ReferralType;
use App\Constants\RespondentInvitationStatus;
use App\Constants\RespondentStatus;
use App\Country;
use App\Libraries\Project\ProjectUtils;
use App\Mail\ProjectInflowRegistration;
use App\Person;
use App\Referral;
use App\Respondent;
use App\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectInflowController extends Controller {

    /**
     * @param string $projectId
     *
     * @return RedirectResponse|View
     */
    public function land(string $projectId)
    {
        // Check if project ID match a project code.
        if ( ! $projectCode = $this->getProjectCodeByReferralCode($projectId)) {
            return redirect()->route('register');
        }

        // Check if dynamic incentive related params are set for project code.
        if ( ! $data = $this->getIncentivePackage($projectCode)) {
            return redirect()->route('register');
        }

        $projectCodeEncrypted = encrypt($projectCode);
        $countries = cache()->remember('COUNTRIES_ALL', now()->addDays(10), function () {
            return Country::all();
        });
        $genders = Gender::getKeyWithLabel();

        return view('project_inflow.set_info', compact('data', 'projectCodeEncrypted', 'countries',
            'genders', 'projectId'));
    }

    public function handleRespondent(string $projectId): RedirectResponse
    {
        $data = request()->only(['email', 'code', 'country_id', 'date_of_birth', 'gender_code']);

        $validator = Validator::make($data, [
            'code'          => ['required', 'string'],
            'email'         => ['required', 'string', 'email:strict', 'max:255'],

            // Person's data
            'country_id'    => ['required', 'exists:countries,id'],
            'date_of_birth' => ['required', 'string', 'date_format:d-m-Y'],
            'gender_code'   => ['required', 'in:m,w,u'],
        ]);
        $validator->validate();

        try {
            $projectCode = decrypt($data['code']);
        } catch (Exception $exception) {
            Log::error('Could not decrypt project code during project inflow', ['project_code' => $data['code']]);

            return redirect()->route('home');
        }

        // Check if email already exist as user.
        $user = User::query()
            ->with('person')
            ->where('email', '=', $data['email'])
            ->first();

        if ($user) {
            $person = $user->person;
        } else {
            $password = Str::random(10);

            $userData = [
                'email'    => $data['email'],
                'password' => Hash::make($password),
            ];

            $personData = [
                'email'         => $data['email'],
                'date_of_birth' => $data['date_of_birth'],
                'gender_code'   => $data['gender_code'],
                'country_id'    => $data['country_id'],
            ];

            $person = DB::transaction(function () use ($userData, $personData) {
                $person = Person::create($personData);

                $user = new User(array_merge($userData, [
                    'person_id' => $person->id,
                ]));
                $user->forceFill(['email_verified_at' => Date::now()])->save();

                return $person;
            });

            Mail::to($data['email'])
                ->locale(app()->getLocale())
                ->later(now()->addMinutes(5), new ProjectInflowRegistration($password));
        }

        if ( ! $person) {
            return redirect()->route('home');
        }

        $respondentMetaData = $this->getIncentivePackage($projectCode);

        // Set reference to reference ID for referral purposes.
        if ($this->getProjectCodeByReferralCode($projectId)) {
            $respondentMetaData['referral_id'] = $projectId;
        }
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            return $this->startRespondentEnrolment($respondent);
        }

        return redirect()->route('login');
    }

    /**
     * @param Respondent $respondent
     *
     * @return RedirectResponse
     */
    private function startRespondentEnrolment(Respondent $respondent): RedirectResponse
    {
        $invitation = $respondent->invitations()->create([
            'type'   => InvitationType::INFLOW,
            'status' => RespondentInvitationStatus::REDIRECTED,
        ]);

        return redirect()->route('invitation.land', $invitation->uuid);
    }

    /**
     * @param Person $person
     * @param string $projectCode
     * @param array  $respondentMetaData
     *
     * @return Respondent|null
     */
    private function getRespondent(Person $person, string $projectCode, array $respondentMetaData): ?Respondent
    {
        // Check if this person already participated in this project.
        $respondent = $person->respondent()
            ->where('project_code', '=', $projectCode)
            ->first();

        // Person already participated in this project.
        if ($respondent && in_array($respondent->current_status, [
                RespondentStatus::COMPLETED,
                RespondentStatus::DISQUALIFIED,
                RespondentStatus::CLOSED,
                RespondentStatus::TARGET_UNSUITABLE,
            ])
        ) {
            $respondent = null;
        } elseif ( ! $respondent) {
            /** @var Respondent $respondent */
            $respondent = $person->respondent()->create([
                'project_code'     => $projectCode,
                'current_status'   => RespondentStatus::SELECTED,
                'status_history'   => [RespondentStatus::SELECTED => date('Y-m-d H:i:s')],
                'meta_data'        => $respondentMetaData,
                'incentive_amount' => $respondentMetaData['usd_amount'] ?? 0,
            ]);
        }

        return $respondent;
    }

    /**
     * @param string $projectCode
     *
     * @return array|null
     */
    private function getIncentivePackage(string $projectCode): ?array
    {
        $projectConfigs = ProjectUtils::getConfigs($projectCode);

        $packageId = $projectConfigs['configs']['inflow_incentive_package_id'] ?? null;
        if ( ! $packageId) {
            return null;
        }

        if (empty($package = ProjectUtils::getIncentivePackage($projectCode, $packageId))) {
            return null;
        }

        return $package;
    }

    /**
     * @param string $referralCode
     * @return string|null
     */
    private function getProjectCodeByReferralCode(string $referralCode): ?string
    {
        $data = Referral::query()
            ->where('code', $referralCode)
            ->where('type', ReferralType::RESPONDENT_RECRUITMENT)
            ->value('data');

        return $data['project_code'] ?? null;
    }
}
