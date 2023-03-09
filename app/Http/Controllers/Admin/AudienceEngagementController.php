<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\InvitationType;
use App\Constants\RespondentStatus;
use App\Http\Controllers\Controller;
use App\Jobs\EngageAudienceForSurvey;
use App\Libraries\Project\AudienceQueryBuilder;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AudienceEngagementController extends Controller {

    private $pageTitle = 'Engagement cockpit';

    public function select(string $projectCode) {
        ini_set('memory_limit', '1024M');

        $this->authorize('manage-projects');

        if (request()->query('action') === 'engage') {
            $this->engage($projectCode);

            $queryParams = request()->query;
            $queryParams->set('action', 'feasibility');

            return redirect()->route('admin.projects.audience_selection',
                array_merge(['project_code' => $projectCode], $queryParams->all()));
        }

        $data = [];
        $feasible = null;
        if (request()->query('action') === 'feasibility') {
            $data = $this->prepareAndValidateData(request()->all(), false);
            $feasible = count($this->getFeasiblePersonsId($projectCode, $data));
        }

        $projectConfigs = ProjectUtils::getConfigs($projectCode);
        $countryCode = $data['country_codes'] ?? $projectConfigs['targets']['country'][0];
        $subdivisions = $data['subdivision_codes'] ?? [];
        if ($value = $projectConfigs['targets']['state'] ?? null) {
            $subdivisions = str_replace('|', ',', $value);
        }
        $genders = $data['genders'] ?? $projectConfigs['targets']['gender'] ?? [];
        $ageRanges = $data['age_ranges'] ?? $projectConfigs['targets']['age_range'] ?? [];
        $languageRestrictions = $data['languages'] ?? (array) ProjectUtils::getLanguageRestrictions($projectCode);
        $statuses = RespondentStatus::getConstants();
        $incentivePackages = ProjectUtils::getIncentivePackage($projectCode, null, true);

        $title = $this->pageTitle;
        $userPersonId = authUser()->person_id;
        $isLive = ProjectUtils::isLive($projectCode);

        return view('admin.projects.audience-selection-lean', compact('projectCode',
            'statuses', 'countryCode', 'languageRestrictions', 'feasible', 'incentivePackages', 'title',
            'userPersonId', 'isLive', 'subdivisions', 'data', 'genders', 'ageRanges'));
    }

    public function reselect(string $projectCode) {
        if ($projectCode !== 'dynata_034_ng') {
            return redirect()->back();
        }

        $respondentUuids = config('dynata_034_ng_respondents');
        $personsId = Respondent::query()
            ->where('project_code', $projectCode)
            ->whereIn('uuid', $respondentUuids)
            ->pluck('person_id')
            ->toArray();

        $incentivePackageId = 1;
        $incentivePackage = ProjectUtils::getIncentivePackage($projectCode, $incentivePackageId);

        $invitationType = InvitationType::EMAIL;
        $invitationTypeHandler = ProjectUtils::getInvitationTypeHandler($projectCode, $invitationType);

        EngageAudienceForSurvey::dispatch($personsId, $projectCode, $incentivePackage, $invitationType, [],
            $invitationTypeHandler)
            ->delay(now()->addSeconds(5));
    }

    private function engage(string $projectCode): void {
        ini_set('memory_limit', '1024M');

        $data = $this->prepareAndValidateData(request()->all());

        $feasiblePersonsId = $this->getFeasiblePersonsId($projectCode, $data);

        // Get a random list of person IDs.
        $limit = $data['size'];
        $feasiblePersonsCount = count($feasiblePersonsId);
        $randomIdKeys = (array)array_rand($feasiblePersonsId,
            ($limit > $feasiblePersonsCount) ? $feasiblePersonsCount : $limit);
        $ids = array_intersect_key($feasiblePersonsId, array_flip($randomIdKeys));

        if (empty($ids)) {
            Alert::makeWarning('No panellist found!');
            return;
        }

        $channel = $data['channel'];
        $incentivePackageId = $data['incentive_package_id'];
        $incentivePackage = ProjectUtils::getIncentivePackage($projectCode, $incentivePackageId);

        if ($channel === 'email') {
            $invitationType = InvitationType::EMAIL;
        }
        if ($channel === 'sms') {
            $invitationType = InvitationType::SMS;
        }
        if (empty($invitationType)) {
            Alert::makeWarning('Channel is not set!');
            return;
        }

        $invitationTypeHandler = isset($invitationType)
            ? ProjectUtils::getInvitationTypeHandler($projectCode, $invitationType)
            : null;

        EngageAudienceForSurvey::dispatch($ids, $projectCode, $incentivePackage, $invitationType, [], $invitationTypeHandler)
            ->delay(now()->addSeconds(5));

        try {
            $previousPersonIdsList = (array) cache()->pull(self::getCacheKey($projectCode));
            cache()->put(
                self::getCacheKey($projectCode),
                array_merge($previousPersonIdsList, $ids),
                now()->addMinutes(15)
            );
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());
        }
    }

    private function getFeasiblePersonsId(string $projectCode, array $data): array {
        // If already set, we assume the IDs are for testing purposes.
        $personsId = $data['person_ids'] ?? [];

        $foundPersonsId = [];
        if (empty($personsId)) {
            if ($uuids = $data['uuids'] ?? null) {
                $uuids = trim($data['uuids']);
                $uuids = $uuids ? explode("\n", $uuids) : null;

                $foundPersonsId = $this->getPersonsIdByUUID($projectCode, $uuids);
            } else {
                $foundPersonsId = $this->getPersonsId($data);
            }
        }

        if ($personsId) {
            $feasiblePersonsId = $personsId;
        } elseif ( ! empty($uuids)) {
            $feasiblePersonsId = $foundPersonsId;
        } else {
            if ($value = $data['include_with_status'] ?? null) {
                $engagedPersonsId = AudienceQueryBuilder::getEngagedPersonsId($projectCode, $value);
            } else {
                $engagedPersonsId = AudienceQueryBuilder::getEngagedPersonsId($projectCode);
            }

            // Exclude persons already in queue.
            $queuedPersonsId = (array) cache()->get(self::getCacheKey($projectCode));

            $excludeByParticipationInOtherSample = ProjectUtils::getToBeExcludedPersonsId($projectCode) ?? [];

            $toExcludePersonsId = array_merge($engagedPersonsId, $queuedPersonsId,
                $excludeByParticipationInOtherSample);

            $feasiblePersonsId = array_diff($foundPersonsId, $toExcludePersonsId);
        }

        return $feasiblePersonsId;
    }

    /**
     * @param  array  $data
     * @param  bool  $isEngageAction
     * @return array
     */
    private function prepareAndValidateData(array $data, bool $isEngageAction = true): array {
        $toPrepareData = [
            'languages',
            'genders',
            'include_with_status',
            'age_ranges',
            'subdivision_codes',
            'person_ids',
        ];

        foreach ($toPrepareData as $dataKey) {
            if ($rawData = $data[$dataKey] ?? null) {
                $data[$dataKey] = $this->explodeCommaSeparatedData($rawData);
            }
        }

        $validationRules = [
            'country_codes'         => ['required', 'string'],
            'subdivision_codes'     => ['nullable', 'array'],
            'subdivision_codes.*'   => ['nullable', 'string'],
            'languages'             => ['nullable', 'array'],
            'languages.*'           => ['nullable', 'string', Rule::in(['EN', 'FR', 'PT'])],
            'age_ranges'            => ['nullable', 'array'],
            'age_ranges.*'          => ['nullable', 'string'],
            'genders'               => ['nullable', 'array'],
            'genders.*'             => ['nullable', 'string', Rule::in(['m', 'w'])],
            'include_with_status'   => ['nullable', 'array'],
            'include_with_status.*' => ['nullable', 'string', Rule::in(RespondentStatus::getConstants())],
            'uuids'                 => ['nullable', 'string'],
            'person_ids'            => ['nullable', 'array'],
            'person_ids.*'          => ['nullable', 'integer'],
        ];
        if ($isEngageAction) {
            $validationRules = array_merge($validationRules, [
                'channel'              => ['required', 'string', Rule::in(['email', 'sms'])],
                'size'                 => ['required', 'integer'],
                'incentive_package_id' => ['required', 'integer'],
            ]);
        }

        Validator::make($data, $validationRules)->validate();

        return $data;
    }

    /**
     * @param  string  $rawData
     * @return false|string[]
     */
    private function explodeCommaSeparatedData(string $rawData) {
        return array_filter(explode(',', preg_replace('/\s+/', '', $rawData)));
    }

    /**
     * @param  array  $data
     * @return array
     * @throws Exception
     */
    private function getPersonsId(array $data): array {
        $queryBuilder = (new AudienceQueryBuilder());

        $dataKey = 'country_codes';
        if (isset($data[$dataKey]) && ! empty($data[$dataKey])) {
            $queryBuilder->setCountries([$data[$dataKey]]);
        }

        $dataKey = 'subdivision_codes';
        if (isset($data[$dataKey]) && ! empty($data[$dataKey])) {
            $queryBuilder->setSubdivisionCodes($data[$dataKey]);
        }

        $dataKey = 'languages';
        if (isset($data[$dataKey]) && ! empty($data[$dataKey])) {
            $queryBuilder->setLanguage($data[$dataKey]);
        }

        $dataKey = 'genders';
        if (isset($data[$dataKey]) && ! empty($data[$dataKey])) {
            $queryBuilder->setGenders($data[$dataKey]);
        }

        $dataKey = 'age_ranges';
        if (isset($data[$dataKey]) && ! empty($data[$dataKey])) {
            $queryBuilder->setAgeRanges($data[$dataKey]);
        }

        return $queryBuilder->getQuery()->pluck('id')->toArray();
    }

    private function getPersonsIdByUUID(string $projectCode, array $uuids): array {
        return DB::table('respondents')
            ->where('project_code', $projectCode)
            ->whereIn('uuid', $uuids)
            ->pluck('person_id')
            ->toArray();
    }

    /**
     * @param  string  $projectCode
     *
     * @return string
     */
    private static function getCacheKey(string $projectCode): string {
        return "QUEUED_PERSONS_FOR_${projectCode}";
    }
}
