<?php

namespace App\Imports;

use App\Translation;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;

class ProfilingTranslationsImport implements ToModel {

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        return new Translation($row);
    }
}
