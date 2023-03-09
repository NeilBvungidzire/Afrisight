<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class HeinekenSurveyResultsImport implements ToCollection {

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
    }
}
