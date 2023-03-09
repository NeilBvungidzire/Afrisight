<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\RespondentStatus;
use App\DataPoint;
use App\Jobs\EngageAudienceForSurvey;
use App\Libraries\Project\AudienceQueryBuilder;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\Target;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class AudienceSelectionController extends BaseController {

    public function select(string $projectCode)
    {
        $this->authorize('manage-projects');

        $audienceQueryBuilder = new AudienceQueryBuilder();

        // Exclude persons already in queue.
        $queuedPersonsId = (array)cache()->get(self::getCacheKey($projectCode));
        $audienceQueryBuilder->excludedPersons($queuedPersonsId);

        // Get by countries
        if ($countryCode = request()->query('country')) {
            $audienceQueryBuilder->setCountries([$countryCode]);
        }

        // Get by age range
        if ($ageRange = request()->query('age_range')) {
            $audienceQueryBuilder->setAgeRanges([$ageRange]);
        }

        // Get by gender
        if ($genderCode = request()->query('gender')) {
            $audienceQueryBuilder->setGenders([$genderCode]);
        }

        // Get by city
        if ($cities = request()->query('city')) {
            $cities = explode(',', $cities);

            $personIds = DataPoint::query()
                ->whereIn('value', $cities)
                ->pluck('person_id')
                ->toArray();

            $audienceQueryBuilder->exclusivePersons($personIds);
        }

        // Get by subdivision
        if ($states = request()->query('state')) {
            $statesRaw = explode(',', $states);

            $states = [];
            foreach ($statesRaw as $rawState) {
                $states = array_merge($states, explode('/', $rawState));
            }

            $personIds = DataPoint::query()
                ->whereIn('value', $states)
                ->pluck('person_id')
                ->toArray();

            $audienceQueryBuilder->exclusivePersons($personIds);
        }

        // Specific persons by ID's
        if ($personIds = request()->query('person-ids')) {
            $personIds = explode(',', $personIds);

            $audienceQueryBuilder->exclusivePersons($personIds);
        }

        // Exclude already targeted persons.
        if (request()->query('exclude-respondents')) {
            $audienceQueryBuilder->excludeRespondents($projectCode);
        }

        // Target persons who already were selected for the survey and have certain status.
        if ($value = request()->query('status')) {
            $alreadyInvitedPersonsIds = $this
                ->getAlreadyTargetedPersonsById($projectCode, explode(',', $value))
                ->toArray();

            $audienceQueryBuilder->exclusivePersons($alreadyInvitedPersonsIds);
        }

        // Exclude respondents from other projects.
        if (request()->query('use-exclusion')) {
            $audienceQueryBuilder->excludedPersons($this->getExcludedPersonsId($projectCode));
        }

        // Order
        $orderDirection = request()->query('order') ?? 'ASC';
        if ($orderDirection === 'mixed') {
            $audienceQueryBuilder->orderBy();
        } else {
            $audienceQueryBuilder->orderBy('created_at', $orderDirection);
        }

        $audienceQueryBuilder = $audienceQueryBuilder->getQuery();

        // Only show person with mobile number set.
        if (request()->query('sms')) {
            $audienceQueryBuilder->whereNotNull('mobile_number');
        }

        // Get data points.
        $audienceQueryBuilder->with('dataPoints');

        // Limit the numbers of items retrieved.
        $limit = request()->query('limit', 30);

        $persons = $audienceQueryBuilder->paginate($limit);

        foreach ($persons as $person) {
            $person['respondent'] = null;

            $respondent = $person->respondent()
                ->with('invitations')
                ->where('project_code', $projectCode)
                ->first();

            if ($respondent) {
                $person['respondent'] = $respondent;
            }
        }

        $targets = Target::query()
            ->where('project_code', $projectCode)
            ->get()
            ->groupBy('criteria');

        $incentivePackages = $this->getPackage($projectCode);

        return view('admin.projects.audience-selection',
            compact('projectCode', 'persons', 'targets', 'incentivePackages'));
    }

    public function engagement(string $projectCode)
    {
        $personIds = request()->get('person_id');
        $packageId = request()->get('package_id');
        $type = request()->get('type');

        if (empty($personIds) || empty($type)) {
            Alert::makeWarning('No person and/or package was set.');

            return redirect()->back();
        }

        if ( ! $package = $this->getPackage($projectCode, $packageId)) {
            Alert::makeWarning("Package with ID ${packageId} does not exist for project ${projectCode}.");

            return redirect()->back();
        }

        EngageAudienceForSurvey::dispatch($personIds, $projectCode, $package, $type)
            ->delay(now()->addSeconds(15));

        try {
            $previousPersonIdsList = (array)cache()->pull(self::getCacheKey($projectCode));
            cache()->put(
                self::getCacheKey($projectCode),
                array_merge($previousPersonIdsList, $personIds),
                now()->addMinutes(15)
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            Alert::makeDanger('Something went wrong. Contact the tech-department.');

            return redirect()->back();
        }

        Alert::makeSuccess('Selected audience is set in queue to be engaged and marked as such. This will take a few minutes.');

        return redirect()->back();
    }

    /**
     * @param string $projectCode
     *
     * @return string
     */
    private static function getCacheKey(string $projectCode)
    {
        return "QUEUED_PERSONS_FOR_${projectCode}";
    }

    /**
     * @param string $projectCode
     * @param int|string|null $id
     *
     * @return array
     */
    private function getPackage(string $projectCode, $id = null): array
    {
        if ($id === null) {
            $package = ProjectUtils::getConfigs($projectCode)['incentive_packages'] ?? null;
        } else {
            $package = ProjectUtils::getIncentivePackage($projectCode, $id);
        }

        if (empty($package)) {
            return [];
        }

        return $package;
    }

    /**
     * @param string $projectCode
     * @param array|null $statuses
     *
     * @return Collection
     */
    private function getAlreadyTargetedPersonsById(string $projectCode, array $statuses = null): Collection
    {
        $respondents = DB::table('respondents')
            ->distinct()
            ->where('project_code', $projectCode);

        if ($statuses) {
            $respondents->whereIn('current_status', $statuses);
        }

        return $respondents->get('person_id')
            ->pluck('person_id');
    }

    /**
     * @param string $projectCode
     *
     * @return array
     */
    private function getExcludedPersonsId(string $projectCode): array
    {
        return (array)ProjectUtils::getExclusions($projectCode);
    }
}
