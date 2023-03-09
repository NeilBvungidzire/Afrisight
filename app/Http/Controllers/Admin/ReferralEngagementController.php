<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\ReferralType;
use App\ExternalReferrer;
use App\Http\Controllers\Controller;
use App\Person;
use App\Referral;
use App\Services\SMSService\SMSService;

class ReferralEngagementController extends Controller {

    private $params = [
        'referral.url' => 'PARAM_CTA_LINK',
    ];

    public function draftMessage(int $id, string $channel)
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $mobileNumber = $this->getMobileNumber($referral);

        $params = $this->params;
        return view('admin.referral_management.create-referral-message', compact('id',
            'channel', 'params', 'mobileNumber'));
    }

    public function sendMessage(int $id, string $channel)
    {
        $this->authorize('reward-management');

        if ( ! $referral = Referral::find($id)) {
            Alert::makeWarning('Could not find referral instance!');

            return redirect()->route('admin.referral_management.overview');
        }

        $message = trim(request()->get('message'));
        $mobileNumber = trim(request()->get('mobile_number'));
        if (empty($message) || empty($mobileNumber)) {
            Alert::makeWarning('Either mobile number or message not set (correctly).');

            return redirect()->back();
        }

        $search = [];
        $replace = [];
        foreach ($this->params as $path => $placeholder) {
            if ($path === 'referral.url') {
                $search[] = $placeholder;
                $replace[] = Referral::generateUrl($referral->type, $referral->code);
            }
        }

        $message = str_replace($search, $replace, $message);

        $smsService = new SMSService($message, $mobileNumber);
        if ($smsService->send(ReferralType::RESPONDENT_RECRUITMENT)) {
            $data = $referral->data;
            $data['SMS'] = isset($data['SMS']) ? (int)$data['SMS'] + 1 : 1;
            $referral->data = $data;
            $referral->save();

            Alert::makeSuccess('SMS message send successfully.');
        } else {
            Alert::makeWarning('Could not send SMS message.');
        }

        return redirect()->route('admin.referral_management.overview');
    }

    private function getMobileNumber(Referral $referral): ?string
    {
        if ($referral->referrerable_type === Person::class) {
            return $referral->referrerable->mobile_number;
        }

        if ($referral->referrerable_type === ExternalReferrer::class) {
            return $referral->referrerable->contacts['phone'] ?? null;
        }

        return null;
    }
}
