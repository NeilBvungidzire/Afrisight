<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\InvitationType;
use App\Constants\RespondentInvitationStatus;
use App\Constants\RespondentStatus;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\RespondentInvitation;
use CMText\Channels;
use CMText\Message;
use CMText\TextClient;
use CMText\TextClientStatusCodes;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InviteController extends BaseController {

    /**
     * @param string $projectCode
     *
     * @return View
     * @throws AuthorizationException
     */
    public function selectAudience(string $projectCode)
    {
        $this->authorize('manage-projects');

        return view('admin.invite.select', compact('projectCode'));
    }

    /**
     * @param string $projectCode
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function sendSms(string $projectCode)
    {
        $this->authorize('manage-projects');

        $data = request()->only(['person_id', 'package_id']);

        $package = ProjectUtils::getIncentivePackage($projectCode, $data['package_id']);

        if (empty($package)) {
            Alert::makeWarning('Package does not exist.');

            return redirect()->back();
        }

        $respondent = Respondent::query()
            ->join('respondent_invitations', 'respondents.id', '=', 'respondent_invitations.respondent_id')
            ->join('persons', 'respondents.person_id', '=', 'persons.id')
            ->where('respondents.person_id', $data['person_id'])
            ->where('respondents.project_code', '=', $projectCode)
            ->where('persons.mobile_number', '!=', '')
            ->where('persons.language_code', '=', 'EN')
            ->where('respondent_invitations.type', '!=', 'SMS')
            ->first(['respondents.*', 'persons.mobile_number']);

        if ( ! $respondent) {
            Alert::makeWarning('Person does not have the required params.');

            return redirect()->back();
        }

        $mobileNumber = $respondent->mobile_number;
        if (empty($mobileNumber)) {
            Alert::makeWarning('Person does not set a mobile number.');

            return redirect()->back();
        }

        // Generate, but don't save yet, a respondent invitation.
        $invitation = new RespondentInvitation();
        $uuid = Str::uuid();
        $invitation->fill([
            'respondent_id' => $respondent->id,
            'uuid'          => $uuid,
            'type'          => InvitationType::SMS,
            'status'        => RespondentInvitationStatus::SEND,
        ]);

        try {
            $client = new TextClient('852D5D9D-E3C2-4F0D-997F-132878017760');
            $message = new Message('Dear AfriSight member, we have a survey you can participate in. It is ' . $package['loi'] . ' minutes & reward is ' . $package['local_currency'] . ' ' . $package['local_amount'] . ' (USD ' . $package['usd_amount'] . '). The study is on a first-come-first service, complete it asap. To start press the link ' . route('invitation.land',
                    ['uuid' => $uuid]),
                'AfriSight', [$mobileNumber]);
            $message->WithChannels([Channels::SMS]);
            $result = $client->send([$message]);

            if ($result->statusCode !== TextClientStatusCodes::OK) {
                Alert::makeWarning('Could not send the SMS message!');

                return redirect()->back();
            }

        } catch (Exception $exception) {
            Alert::makeWarning('Could not send the SMS message!');

            return redirect()->back();
        }

        $newStatus = RespondentStatus::RESELECTED;
        $respondent->update([
            'current_status' => $newStatus,
            'status_history' => array_merge($respondent->status_history, [
                $newStatus => date('Y-m-d H:i:s'),
            ]),
            'meta_data'      => array_merge((array)$respondent->meta_data, [
                'loi'       => $package['loi'],
                'incentive' => $package['usd_amount'],
            ]),
        ]);

        $invitation->save();

        Alert::makeInfo('SMS was send successfully.');

        return redirect()->back();
    }
}
