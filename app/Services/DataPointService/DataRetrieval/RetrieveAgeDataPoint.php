<?php

namespace App\Services\DataPointService\DataRetrieval;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RetrieveAgeDataPoint extends RetrieveDataPointBase {

    /**
     * @param int|string $personId
     * @param bool       $fresh
     * @return int|null
     */
    public function getValue($personId, bool $fresh): ?int
    {
        if ($fresh) {
            $this->forgetValue($personId);
        }

        return $this->rememberValue($personId, now()->addDay(), static function () use ($personId) {
            $personDateOfBirth = DB::table('persons')
                ->where('id', $personId)
                ->value('date_of_birth');

            try {
                return Carbon::make($personDateOfBirth)->age ?? null;
            } catch (Exception $exception) {
                return null;
            }
        });
    }
}
