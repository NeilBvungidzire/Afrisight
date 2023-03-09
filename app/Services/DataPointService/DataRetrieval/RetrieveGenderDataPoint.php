<?php

namespace App\Services\DataPointService\DataRetrieval;

use App\Constants\Gender;
use Illuminate\Support\Facades\DB;

class RetrieveGenderDataPoint extends RetrieveDataPointBase {

    /**
     * @param int|string $personId
     * @param bool       $fresh
     * @return string|null
     */
    public function getValue($personId, bool $fresh): ?string
    {
        if ($fresh) {
            $this->forgetValue($personId);
        }

        $genderConstants = Gender::getConstants();
        return $this->rememberValue($personId, now()->addDay(), static function () use ($personId, $genderConstants) {
            $genderCode = DB::table('persons')
                ->where('id', $personId)
                ->value('gender_code');

            return array_flip($genderConstants)[$genderCode] ?? null;
        });
    }
}
