<?php

namespace App\Imports;

use App\Lead;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeadsImport implements ToModel, WithHeadingRow {

    /**
     * @var string
     */
    private $listCode;

    /**
     * LeadsImport constructor.
     *
     * @param string $listCode
     */
    public function __construct(string $listCode)
    {
        $this->listCode = $listCode;
    }

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        return new Lead([
            'email'     => $row['email_1'],
            'name'      => $row['name'],
            'meta_data' => [
                'country_id' => 19,
                'university' => $row['university'],
                'list_code'  => $this->listCode,
            ],
        ]);
    }
}
