<?php

namespace App\Libraries\ProfilingQuestionnaire;

use Illuminate\Support\Facades\Cache;

class ProfilingQuestionnaire {

    public const ADD = 'add';
    public const REMOVE = 'remove';

    /**
     * @param int|string $personId
     * @return string
     */
    public static function getCacheKey($personId): string {
        return "MEMBERS_PROFILING_ANSWERS_LIST_BY_PERSON_ID_" . $personId;
    }

    /**
     * @param int|string $personId
     * @param  string  $action See constants
     * @param  array  $answerIds
     * @return bool
     */
    public static function handleCache($personId, string $action, array $answerIds): bool {
        $cacheKey = self::getCacheKey($personId);

        if ($action === self::ADD) {
            return Cache::put($cacheKey, array_unique(array_merge(self::getCache($personId), $answerIds)));
        }
        if ($action === self::REMOVE) {
            return Cache::put($cacheKey, array_diff(self::getCache($personId), $answerIds));
        }

        return false;
    }

    /**
     * @param $personId
     * @return array
     */
    public static function getCache($personId): array {
        return Cache::get(self::getCacheKey($personId), []);
    }
}