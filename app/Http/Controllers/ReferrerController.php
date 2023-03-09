<?php

namespace App\Http\Controllers;

use App\Constants\ReferralType;
use App\Libraries\Project\ProjectUtils;
use App\Referral;

class ReferrerController extends Controller {

    public function overview(string $locale, string $id)
    {
        // Set locale
        config()->set('app.locale', $locale);

        try {
            ['type' => $type, 'id' => $id] = decrypt($id);
        } catch (\Exception $exception) {
            return redirect()->route('home');
        }

        $referrals = Referral::query()
            ->where('referrerable_type', $type)
            ->where('referrerable_id', $id)
            ->where('type', ReferralType::RESPONDENT_RECRUITMENT)
            ->get();

        // Add incentive package. In case incentive package not set, remove referral from list!
        foreach ($referrals as $key => $referral) {
            $referral->incentive_package = null;
            $referral->is_available = false;

            if ( ! $projectCode = $referral->data['project_code'] ?? null) {
                $referrals->forget($key);
                continue;
            }

            $referral->is_available = ProjectUtils::isLive($projectCode);

            if ( ! $referral->incentive_package = $this->getInflowIncentivePackage($projectCode)) {
                $referrals->forget($key);
            }
        }

        return view('referrer.overview', compact('referrals'));
    }

    /**
     * @param string $projectCode
     *
     * @return array|null
     */
    private function getInflowIncentivePackage(string $projectCode): ?array
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
}
