<?php

namespace App\Libraries\Project;

use App\Constants\DataPointAttribute;
use App\Constants\InvitationType;
use App\Constants\RespondentStatus;
use App\IncentivePackage;
use App\Mail\ProjectAutoPausedNotifier;
use App\Project;
use App\Target;
use App\TargetTrack;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Psr\SimpleCache\InvalidArgumentException;

class ProjectUtils {

    /**
     * @param  string|null  $projectCode  If empty it will return all projects. Otherwise, it will try to return the
     *                                 project with that specific project code.
     * @param  bool  $fresh
     * @return array|null
     */
    public static function getConfigs(string $projectCode = null, bool $fresh = false): ?array {
        // Handle single
        if (is_string($projectCode)) {
            return self::getProject($projectCode, $fresh);
        }

        if ( ! is_null($projectCode)) {
            return null;
        }

        // Handle all
        $result = null;
        $projectCodes = DB::table('projects')->pluck('project_code');
        foreach ($projectCodes as $code) {
            if ( ! $project = self::getProject($code, $fresh)) {
                continue;
            }

            $result[$code] = $project;
        }

        return $result;
    }

    /**
     * @param  string  $projectCode
     * @param  string  $invitationType
     * @return string|null
     */
    public static function getInvitationTypeHandler(string $projectCode, string $invitationType): ?string {
        if ( ! $configs = self::getConfigs($projectCode)) {
            return null;
        }

        $invitationTypeResponse = null;

        // Handle mailable invite
        if ($invitationType === InvitationType::EMAIL) {
            $invitationTypeResponse = $configs['configs']['invitation_type_handler'][InvitationType::EMAIL] ?? \App\Mail\RespondentInvitationVariant1::class;
        }

        return $invitationTypeResponse;
    }

    public static function getDeviceTypeRestrictions(string $projectCode): ?array {
        if ( ! $configs = self::getConfigs($projectCode)) {
            return null;
        }

        $restriction = $configs['configs']['device_restrictions'] ?? null;
        if (empty($restriction)) {
            return null;
        }

        return $restriction;
    }

    public static function getAllowedDeviceTypes(string $projectCode): ?array {
        return self::getDeviceTypeRestrictions($projectCode) ?? [
            DataPointAttribute::MOBILE,
            DataPointAttribute::TABLET,
            DataPointAttribute::DESKTOP,
        ];
    }

    public static function isUserDeviceTypeAllowed(string $projectCode, bool $strict = false): bool {
        // Project configs not found so assume does not exist.
        if ( ! $allowedDevices = self::getAllowedDeviceTypes($projectCode)) {
            return false;
        }

        // Device couldn't be detected. In case not strict check needed, we give it the benefit of the doubt.
        $detectedDevice = self::getDeviceTypeViaBrowserData();
        if ( ! $detectedDevice && ! $strict) {
            return true;
        }

        return in_array($detectedDevice, $allowedDevices, true);
    }

    public static function getLanguageRestrictions(string $projectCode): ?array {
        $languageRestrictions = self::getConfigs($projectCode)['configs']['language_restrictions'] ?? null;

        return is_array($languageRestrictions) ? $languageRestrictions : null;
    }

    /**
     * @param  string  $projectCode
     * @param  mixed  $id
     * @param  bool  $all
     * @return array|null If null, no incentive package exists for this project.
     */
    public static function getIncentivePackage(string $projectCode, int $id = null, bool $all = false): ?array {
        if ($all) {
            $packages = IncentivePackage::query()
                ->where('project_code', $projectCode)
                ->select([
                    'reference_id',
                    'loi',
                    'usd_amount',
                    'local_currency',
                    'local_amount',
                ])
                ->get();

            return ($packages->count() > 0) ? $packages->keyBy('reference_id')->toArray() : null;
        }

        $package = null;
        if ( ! empty($id)) {
            $package = IncentivePackage::query()
                ->where('project_code', $projectCode)
                ->where('reference_id', $id)
                ->select([
                    'reference_id',
                    'loi',
                    'usd_amount',
                    'local_currency',
                    'local_amount',
                ])
                ->first();
        }

        if ( ! $projectConfigs = self::getConfigs($projectCode)) {
            return null;
        }

        $defaultId = $projectConfigs['configs']['default_incentive_package_id'] ?? null;
        if (empty($package)) {
            $package = IncentivePackage::query()
                ->where('project_code', $projectCode)
                ->where('reference_id', $defaultId)
                ->select([
                    'reference_id',
                    'loi',
                    'usd_amount',
                    'local_currency',
                    'local_amount',
                ])
                ->first();
        }

        if (empty($package)) {
            $last = DB::table('incentive_packages')
                ->where('project_code', $projectCode)
                ->max('reference_id');

            $package = IncentivePackage::query()
                ->where('project_code', $projectCode)
                ->where('reference_id', $last)
                ->select([
                    'reference_id',
                    'loi',
                    'usd_amount',
                    'local_currency',
                    'local_amount',
                ])
                ->first();
        }

        return $package ? $package->toArray() : null;
    }

    public static function setChannelIncentivePackage(string $projectCode, string $channel, int $id): bool {
        $channelMapping = [
            'default' => 'default_incentive_package_id',
            'inflow'  => 'inflow_incentive_package_id',
        ];

        if ( ! array_key_exists($channel, $channelMapping)) {
            return false;
        }

        $project = Project::query()
            ->where('project_code', $projectCode)
            ->first();
        if ( ! $project) {
            return false;
        }

        $projectConfigs = $project->configs;
        $projectConfigs[$channelMapping[$channel]] = $id;
        $project->configs = $projectConfigs;
        if ($project->save()) {
            try {
                self::getConfigs($projectCode, true);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param  string  $projectCode
     *
     * @return array|null
     */
    public static function getTargetsRelation(string $projectCode): ?array {
        if ( ! $project = self::getProject($projectCode)) {
            return null;
        }

        $targetsRelation = $project['targets_relation'];

        if ($targetsRelation) {
            return $targetsRelation;
        }

        return null;
    }

    /**
     * @param  string  $projectCode
     * @param  array  $targetsIdByCriteria
     * @param  bool  $keyByCriteria
     *
     * @return array
     */
    public static function buildTargetsJoins(
        string $projectCode,
        array $targetsIdByCriteria,
        bool $keyByCriteria = false
    ): array {
        if ( ! $targetPaths = self::getProjectTargetPaths($projectCode)) {
            return [];
        }

        $targetsJoins = [];
        foreach ($targetPaths as $targetPath) {
            $trackCriteria = explode('.', $targetPath);

            $list = [];
            foreach ($trackCriteria as $criteria) {
                if ( ! isset($targetsIdByCriteria[$criteria])) {
                    continue;
                }

                if (count($targetsIdByCriteria[$criteria]) === 0) {
                    continue;
                }

                $list[] = $targetsIdByCriteria[$criteria];
            }

            $targetsJoins = array_merge_recursive($targetsJoins, Arr::crossJoin(...$list));
        }

        if ( ! $keyByCriteria) {
            return $targetsJoins;
        }

        $criteriaById = [];
        foreach ($targetsIdByCriteria as $criteria => $ids) {
            foreach ($ids as $id) {
                $criteriaById[$id] = $criteria;
            }
        }

        $keyedTargetsJoins = [];
        foreach ($targetsJoins as $joinedIds) {
            $keyedTargetsJoins[] = array_flip(array_intersect_key($criteriaById, array_flip($joinedIds)));
        }

        return $keyedTargetsJoins;
    }

    /**
     * @param  string  $projectCode
     * @param  bool  $byPath
     *
     * @return array
     */
    public static function getProjectTargetPaths(string $projectCode, bool $byPath = false): array {
        if ( ! $targetsRelation = self::getTargetsRelation($projectCode)) {
            return [];
        }

        $paths = array_keys(Arr::dot($targetsRelation));

        if ( ! $byPath) {
            return $paths;
        }

        $byPathResult = [];
        foreach ($paths as $path) {
            $byPathResult[$path] = explode('.', $path);
        }

        return $byPathResult;
    }

    /**
     * Return only target ID's by quota if the quota is fully hit.
     * Assumes passed targets hit one of each required criteria target.
     *
     * @param  string  $projectCode
     * @param  int[]  $hitTargetsIdByCriteria  [TARGET_CRITERIA_NAME => TARGET_ID, ...]
     *
     * @return int[][] [[TARGET_CRITERIA_NAME => TARGET_ID, ...], ...]
     */
    public static function getHitTargetPaths(string $projectCode, array $hitTargetsIdByCriteria): array {
        $quotaTracks = [];
        foreach (self::getProjectTargetPaths($projectCode) as $dottedTrack) {
            $quotaTracks[] = explode('.', $dottedTrack);
        }

        $hitQuota = [];
        foreach ($quotaTracks as $quotaTrack) {
            $requiredNumberOfTargets = count($quotaTrack);

            $targetsIntersection = array_intersect_key($hitTargetsIdByCriteria, array_flip($quotaTrack));
            $hitNumberOfTargets = count($targetsIntersection);

            if ($requiredNumberOfTargets !== $hitNumberOfTargets) {
                continue;
            }

            $hitQuota[] = $targetsIntersection;
        }

        return $hitQuota;
    }

    /**
     * @param  string  $projectCode
     * @param  int[]  $hitTargetsId
     * @param  bool  $forceAllQuotas
     * @param  bool  $ignoreQuotaLimit
     */
    public static function incrementHitQuotas(
        string $projectCode,
        array $hitTargetsId,
        bool $forceAllQuotas = true,
        bool $ignoreQuotaLimit = false
    ) {
        self::incrementTotalCompletes($projectCode);

        $targetTracks = self::getOpenQuotas($projectCode, $hitTargetsId, $forceAllQuotas, $ignoreQuotaLimit);

        foreach ($targetTracks as $targetTrack) {
            $targetTrack->increment('count', 1);
        }
    }

    /**
     * @param  string  $projectCode
     * @param  int[]  $hitTargetsId
     * @param  bool  $forceAllQuotas
     * @param  bool  $ignoreQuotaLimit
     */
    public static function decrementHitQuotas(
        string $projectCode,
        array $hitTargetsId,
        bool $forceAllQuotas = true,
        bool $ignoreQuotaLimit = false
    ) {
        $targetTracks = self::getOpenQuotas($projectCode, $hitTargetsId, $forceAllQuotas, $ignoreQuotaLimit);

        foreach ($targetTracks as $targetTrack) {
            $targetTrack->decrement('count', 1);
        }
    }

    /**
     * @param  string  $projectCode
     * @param  array  $hitTargetsId
     * @param  bool  $forceAllQuotas
     * @param  bool  $ignoreQuotaLimit
     *
     * @return TargetTrack[]
     */
    public static function getOpenQuotas(
        string $projectCode,
        array $hitTargetsId,
        bool $forceAllQuotas = true,
        bool $ignoreQuotaLimit = false
    ): array {
        // Find required number of target paths for the project
        $projectTargetPaths = self::getProjectTargetPaths($projectCode);

        // Construct target paths based on hit targets
        $hitProjectTargetPaths = self::getProjectTargetPathsByTargetsId($projectCode, $hitTargetsId);

        // Not all project target paths are hit.
        if ($forceAllQuotas && count($projectTargetPaths) !== count($hitProjectTargetPaths)) {
            return [];
        }

        // Check if those paths, as quotas, are open
        $targetTracks = [];
        foreach ($hitProjectTargetPaths as $hitProjectTargetPath) {
            $targetTrackIds = DB::table('target_target_track')
                ->select(['target_track_id', DB::raw('COUNT(target_track_id) AS count')])
                ->whereIn('target_id', $hitProjectTargetPath)
                ->groupBy(['target_track_id'])
                ->having('count', '=', count($hitProjectTargetPath))
                ->pluck('target_track_id');

            $targetTrack = TargetTrack::query()
                ->whereIn('id', $targetTrackIds)
                ->when(! $ignoreQuotaLimit, function ($query) {
                    $query->whereRaw('count < quota_amount');
                })
                ->first(['id']);

            // Force only open quotas, if requested.
            if ($forceAllQuotas && ! $targetTrack) {
                return [];
            }

            if ( ! $targetTrack) {
                continue;
            }

            $targetTracks[] = $targetTrack;
        }

        return $targetTracks;
    }

    /**
     * @param  string  $projectCode
     * @param  array  $hitTargetsId
     *
     * @return int[][]
     */
    public static function getProjectTargetPathsByTargetsId(string $projectCode, array $hitTargetsId): array {
        $targets = Target::query()
            ->whereIn('id', $hitTargetsId)
            ->get(['id', 'criteria']);

        $hitTargetsIdByCriteria = [];
        foreach ($targets as $target) {
            $hitTargetsIdByCriteria[$target->criteria] = $target->id;
        }

        return self::getHitTargetPaths($projectCode, $hitTargetsIdByCriteria);
    }

    /**
     * @return array[]
     */
    public static function getPartnersProjects(): array {
        $partners = [
            'test'                 => [
                'name'     => 'Test company',
                'prefix'   => 'test',
                'projects' => [],
            ],
            'as'                   => [
                'name'     => 'AfriSight',
                'prefix'   => 'as',
                'projects' => [],
            ],
            'dynata'               => [
                'name'     => 'Dynata',
                'prefix'   => 'dynata',
                'projects' => [],
            ],
            'ipsos'                => [
                'name'     => 'Ipsos',
                'prefix'   => 'ipsos',
                'projects' => [],
            ],
            'gmrg'                 => [
                'name'     => 'Global Market Research Group',
                'prefix'   => 'gmrg',
                'projects' => [],
            ],
            'pdi'                  => [
                'name'     => 'PDI',
                'prefix'   => 'pdi',
                'projects' => [],
            ],
            'tsr'                  => [
                'name'     => 'Tapestry Research',
                'prefix'   => 'tsr',
                'projects' => [],
            ],
            'msi'                  => [
                'name'     => 'MSI-ACI',
                'prefix'   => 'msi',
                'projects' => [],
            ],
            'toluna'               => [
                'name'     => 'Toluna',
                'prefix'   => 'toluna',
                'projects' => [],
            ],
            'market_cube'          => [
                'name'     => 'Market Cube',
                'prefix'   => 'market_cube',
                'projects' => [],
            ],
            'borderless_access'    => [
                'name'     => 'Borderless Access',
                'prefix'   => 'borderless_access',
                'projects' => [],
            ],
            'ugam_solutions'       => [
                'name'     => 'Ugam',
                'prefix'   => 'ugam_solutions',
                'projects' => [],
            ],
            'norstat'              => [
                'name'     => 'Norstat',
                'prefix'   => 'norstat',
                'projects' => [],
            ],
            'bs_rg'                => [
                'name'     => 'BS-Research Globe',
                'prefix'   => 'bs_rg',
                'projects' => [],
            ],
            'skim'                 => [
                'name'     => 'SKIM Group',
                'prefix'   => 'skim',
                'projects' => [],
            ],
            'ids'                  => [
                'name'     => 'Innovative Data Solutions',
                'prefix'   => 'ids',
                'projects' => [],
            ],
            'cint'                 => [
                'name'     => 'Cint',
                'prefix'   => 'cint',
                'projects' => [],
            ],
            'lucid'                => [
                'name'     => 'Lucid',
                'prefix'   => 'lucid',
                'projects' => [],
            ],
            'universum'            => [
                'name'     => 'Universum Global',
                'prefix'   => 'universum',
                'projects' => [],
            ],
            'jtn'                  => [
                'name'     => 'JTN Research',
                'prefix'   => 'jtn',
                'projects' => [],
            ],
            'validators'           => [
                'name'     => 'Validators',
                'prefix'   => 'validators',
                'projects' => [],
            ],
            'gfk'                  => [
                'name'     => 'GfK',
                'prefix'   => 'gfk',
                'projects' => [],
            ],
            'kantar'               => [
                'name'     => 'Kantar',
                'prefix'   => 'kantar',
                'projects' => [],
            ],
            'field_interactive_mr' => [
                'name'     => 'Field Interactive MR',
                'prefix'   => 'field_interactive_mr',
                'projects' => [],
            ],
            'savanta'              => [
                'name'     => 'Savanta',
                'prefix'   => 'savanta',
                'projects' => [],
            ],
            'azure'                => [
                'name'     => 'Azure Knowledge',
                'prefix'   => 'azure',
                'projects' => [],
            ],
            'syno_int'             => [
                'name'     => 'Syno International',
                'prefix'   => 'syno_int',
                'projects' => [],
            ],
            'schlesinger'          => [
                'name'     => 'Schlesinger Group',
                'prefix'   => 'schlesinger',
                'projects' => [],
            ],
            'quest_mindshare'      => [
                'name'     => 'Quest Mindshare',
                'prefix'   => 'quest_mindshare',
                'projects' => [],
            ],
            'pureprofile'      => [
                'name'     => 'Pureprofile',
                'prefix'   => 'pureprofile',
                'projects' => [],
            ],
            'course5i'      => [
                'name'     => 'Course5 Intelligence',
                'prefix'   => 'course5i',
                'projects' => [],
            ],
        ];

        $projects = self::getConfigs();

        $projectCodes = array_keys($projects);
        foreach ($partners as &$partner) {
            foreach ($projectCodes as $projectCode) {
                if ( ! $projects[$projectCode]['enabled_for_admin'] || ! Str::startsWith($projectCode,
                        $partner['prefix'])) {
                    continue;
                }

                $partner['projects'][$projectCode] = $projects[$projectCode];
                $partner['projects'][$projectCode]['description'] = $partner['projects'][$projectCode]['description'] ?? null;
            }
        }

        return $partners;
    }

    /**
     * @param  string  $projectCode
     * @param  string  $type  Options: live, test
     * @param  string  $respondentId
     * @return string|null
     */
    public static function generateSurveyLink(string $projectCode, string $type, string $respondentId): ?string {
        if ( ! $projectConfigs = self::getConfigs($projectCode)) {
            return null;
        }

        $link = null;
        switch ($type) {

            case 'live':
                $link = $projectConfigs['configs']['survey_link_live'] ?? null;
                break;

            case 'test':
                $link = $projectConfigs['configs']['survey_link_test'] ?? null;
                break;
        }

        if ( ! $link) {
            return null;
        }

        return Str::replaceFirst('{RID}', $respondentId, $link);
    }

    /**
     * Get other sample codes and hereby applicable statuses which should exclude panellists participating in this
     * sample because they participated and got certain end result in other sample(s).
     *
     * @param  string  $projectCode
     * @return array|null List of all other samples codes with specified statuses to ignore the panellist by.
     */
    public static function getExclusions(string $projectCode): ?array {
        $excludeByProjectCodes = self::getConfigs($projectCode)['configs']['exclude_respondents_from_projects'] ?? null;
        if ( ! $excludeByProjectCodes) {
            return null;
        }

        $excludeByStatuses = self::getConfigs($projectCode)['configs']['exclude_respondents_by_status'] ?? null;
        if (empty($excludeByStatuses)) {
            $excludeByStatuses = [
                RespondentStatus::COMPLETED,
                RespondentStatus::DISQUALIFIED,
                RespondentStatus::SCREEN_OUT,
                RespondentStatus::POST_DISQUALIFIED,
                RespondentStatus::STARTED,
            ];
        }

        $result = [];
        foreach ($excludeByProjectCodes as $excludeByProjectCode) {
            $result[$excludeByProjectCode] = $excludeByStatuses;
        }

        return empty($result) ? null : $result;
    }

    /**
     * Get person IDs which should be excluded by end result (status) for the specific project code.
     *
     * @param  string  $projectCode
     * @return array|null Person IDs
     */
    public static function getToBeExcludedPersonsId(string $projectCode): ?array {
        $excludeByProjectCodes = self::getConfigs($projectCode)['configs']['exclude_respondents_from_projects'] ?? null;
        if ( ! $excludeByProjectCodes) {
            return null;
        }

        $excludeByStatuses = self::getConfigs($projectCode)['configs']['exclude_respondents_by_status'] ?? null;
        if (empty($excludeByStatuses)) {
            $excludeByStatuses = [
                RespondentStatus::COMPLETED,
                RespondentStatus::DISQUALIFIED,
                RespondentStatus::SCREEN_OUT,
                RespondentStatus::POST_DISQUALIFIED,
                RespondentStatus::STARTED,
            ];
        }

        $personsIdToExclude = DB::table('respondents')
            ->whereIn('project_code', $excludeByProjectCodes)
            ->whereIn('current_status', $excludeByStatuses)
            ->pluck('person_id')
            ->toArray();

        return $personsIdToExclude ?? null;
    }

    /**
     * Check if project is live, so open for respondents entering.
     *
     * @param  string  $projectCode
     * @return bool
     */
    public static function isLive(string $projectCode): bool {
        if ( ! $projectConfigs = self::getConfigs($projectCode)) {
            return false;
        }

        return $projectConfigs['live'] ?? false;
    }

    /**
     * @param  string  $projectCode
     * @param  bool|null  $setLive
     * @return bool Whether the record was updated.
     */
    public static function switchIsLiveStatus(string $projectCode, bool $setLive = null): bool {
        if ( ! $projectConfigs = self::getConfigs($projectCode)) {
            return false;
        }

        if (is_null($setLive)) {
            $setLive = ! $projectConfigs['live'];
        }

        $updated = (bool) DB::table('projects')
            ->where('project_code', $projectCode)
            ->update([
                'is_live' => $setLive,
            ]);

        try {
            if ($updated) {
                self::getConfigs($projectCode, true);
            }

            return $updated;
        } catch (Exception $exception) {
            return false;
        }
    }

    public static function getRequiredTargetHits(string $projectCode): int {
        if ( ! $projectConfigs = self::getConfigs($projectCode)) {
            return false;
        }

        return isset($projectConfigs['targets']) ? count($projectConfigs['targets']) : 0;
    }

    /**
     * @param  string  $projectCode
     * @param  bool  $fresh
     * @return int|null
     */
    public static function getTotalCompletes(string $projectCode, bool $fresh = false): ?int {
        try {
            $cacheKey = self::getProjectCacheKey($projectCode, 'TOTAL_COMPLETES');

            if ($fresh) {
                cache()->forget($cacheKey);
            }
            return cache()->remember($cacheKey, now()->addHours(6), function () use ($projectCode) {
                return DB::table('respondents')
                    ->where('project_code', $projectCode)
                    ->where('current_Status', RespondentStatus::COMPLETED)
                    ->count();
            });
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param  string  $projectCode
     * @return string
     */
    public static function getTotalCompleteLimitCacheKey(string $projectCode): string {
        return self::getProjectCacheKey($projectCode, 'TOTAL_COMPLETES_LIMIT');
    }

    private static function getProjectCacheKey(string $projectCode, string $postfix = null): string {
        $base = "PROJECT.${projectCode}";

        return $postfix ? "${base}.${postfix}" : $base;
    }

    /**
     * @return string|null
     */
    private static function getDeviceTypeViaBrowserData(): ?string {
        $agent = new Agent();
        $deviceType = null;
        if ($agent->isMobile()) {
            $deviceType = DataPointAttribute::MOBILE;
        } elseif ($agent->isTablet()) {
            $deviceType = DataPointAttribute::TABLET;
        } elseif ($agent->isDesktop()) {
            $deviceType = DataPointAttribute::DESKTOP;
        }

        return $deviceType;
    }

    /**
     * @param  string  $projectCode
     * @param  bool  $fresh
     * @return array|null
     */
    private static function getProject(string $projectCode, bool $fresh = false): ?array {
        try {
            $cacheKey = self::getProjectCacheKey($projectCode, "CONFIGS");

            if ($fresh) {
                cache()->forget($cacheKey);
            }

            $project = cache()->remember($cacheKey, now()->addDay(), static function () use ($projectCode) {
                return Project::query()
                    ->with('incentivePackages')
                    ->where('project_code', $projectCode)
                    ->first();
            });
        } catch (Exception $exception) {
            return null;
        }

        if ( ! $project) {
            return null;
        }

        $project['live'] = $project['is_live'];

        return $project->toArray();
    }

    /**
     * @param  string  $projectCode
     * @return int
     */
    private static function incrementTotalCompletes(string $projectCode): int {
        $cacheKey = self::getProjectCacheKey($projectCode, 'TOTAL_COMPLETES');

        $totalCompletes = 0;
        try {
            if ( ! cache()->has($cacheKey)) {
                $totalCompletes = self::getTotalCompletes($projectCode, true);
            } else {
                $totalCompletes = cache()->increment($cacheKey);
            }
        } catch (Exception|InvalidArgumentException $e) {
            return $totalCompletes;
        }

        $updated = false;
        if (self::totalCompleteLimitHit($projectCode)) {
            $updated = self::switchIsLiveStatus($projectCode, false);
        }

        if ($updated) {
            \Mail::to(config('admin.auto_paused_project.to_be_notified'))
                ->send(new ProjectAutoPausedNotifier($projectCode));
        }

        return $totalCompletes;
    }

    /**
     * @param  string  $projectCode
     * @return bool
     */
    private static function totalCompleteLimitHit(string $projectCode): bool {
        try {
            $cacheKEy = self::getTotalCompleteLimitCacheKey($projectCode);

            $limit = cache()->remember($cacheKEy, now()->addMonth(), function () use ($projectCode) {
                return DB::table('projects')
                    ->where('project_code', $projectCode)
                    ->value('total_complete_limit');
            });
        } catch (Exception $e) {
            return false;
        }

        if (empty($limit)) {
            return false;
        }

        if ( ! $totalCompletes = self::getTotalCompletes($projectCode)) {
            return false;
        }

        return $totalCompletes >= $limit;
    }
}
