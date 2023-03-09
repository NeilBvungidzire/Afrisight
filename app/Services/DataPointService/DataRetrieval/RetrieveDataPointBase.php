<?php

namespace App\Services\DataPointService\DataRetrieval;

use App\ProfilingQuestion;
use Closure;
use DateInterval;
use DateTimeInterface;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;

abstract class RetrieveDataPointBase {

    /**
     * @var string
     */
    protected $dataPointAttribute;

    public function __construct(string $dataPointAttribute)
    {
        $this->dataPointAttribute = $dataPointAttribute;
    }

    /**
     * @param bool       $fresh
     * @param int|string $personId
     * @return mixed
     */
    abstract public function getValue($personId, bool $fresh);

    /**
     * @param bool $fresh
     * @return array|null
     */
    protected function getProfilingDataPointQuestions(bool $fresh = false): ?array
    {
        $dataPointAttribute = $this->dataPointAttribute;
        $cacheKey = "PROFILING_QUESTIONS_FOR_${dataPointAttribute}_DATA_POINT";

        if ($fresh) {
            try {
                cache()->delete($cacheKey);
            } catch (Exception | InvalidArgumentException $exception) {
                return null;
            }
        }

        try {
            return cache()->remember($cacheKey, now()->addDay(), static function () use ($dataPointAttribute) {
                $profilingQuestions = ProfilingQuestion::query()
                    ->where('datapoint_identifier', $dataPointAttribute)
                    ->get([
                        'id',
                        'answer_params',
                        'conditions',
                        'datapoint_identifier',
                    ]);

                if ( ! $profilingQuestions->isEmpty()) {
                    return $profilingQuestions->keyBy('id')->toArray();
                }

                return null;
            });
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param int|string                              $personId
     * @param DateTimeInterface|DateInterval|int|null $ttl
     * @return mixed
     */
    protected function rememberValue($personId, $ttl, Closure $callback)
    {
        $cacheKey = $this->getValueCacheKey($personId);
        try {
            return cache()->remember($cacheKey, $ttl, $callback);
        } catch (Exception $exception) {
            return null;
        }
    }

    protected function forgetValue($personId): bool
    {
        $cacheKey = $this->getValueCacheKey($personId);
        try {
            return cache()->delete($cacheKey);
        } catch (Exception | InvalidArgumentException $exception) {
            return false;
        }
    }

    /**
     * @param int|string $personId
     * @return string
     */
    private function getValueCacheKey($personId): string
    {
        return "DATA_POINT_{$this->dataPointAttribute}_FOR_${personId}";
    }
}
