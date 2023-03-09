<?php

namespace App\Imports;

use App\TargetTrack;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;

class Dynata002TargetQuotasImport implements ToModel {

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        return new TargetTrack([
            //
        ]);
    }
}
