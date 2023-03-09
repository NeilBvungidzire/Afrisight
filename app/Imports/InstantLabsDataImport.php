<?php

namespace App\Imports;

use App\FlexTable;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;

class InstantLabsDataImport implements ToModel {

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        return new FlexTable([
            //
        ]);
    }
}
