<?php

namespace App\Imports;

use App\Lead;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReferredLeadsImport implements ToModel, WithHeadingRow {

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        return new Lead([
            'email'     => $row['referee'],
            'name'      => 'empty',
            'meta_data' => [
                'country_id' => 19,
                'reference'  => 'referred_leads_01',
                'referral'   => [
                    'email' => $row['referral_email'],
                    'name'  => $row['referral_name'],
                ],
            ],
        ]);
    }
}
