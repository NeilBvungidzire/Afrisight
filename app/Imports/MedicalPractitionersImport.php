<?php

namespace App\Imports;

use App\MedicalPractitioner;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Propaganistas\LaravelPhone\PhoneNumber;

class MedicalPractitionersImport implements ToModel, WithHeadingRow {

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        $otherData = [];
        $emails = explode('/', $row['email']);
        $primaryEmail = empty($emails[0]) ? null : $emails[0];
        if (isset($emails[1])) {
            $otherData['secondary_email'] = $emails[1];
        }

        $mobileNumbers = explode('/', $row['mobile_number']);
        $primaryMobileNumber = null;
        if (isset($mobileNumbers[0])) {
            $primaryMobileNumber = (string)PhoneNumber::make($mobileNumbers[0], 'UG');
        }
        if (isset($mobileNumbers[1])) {
            $otherData['secondary_mobile_number'] = (string)PhoneNumber::make($mobileNumbers[1], 'UG');
        }

        $data = [
            'email' => $primaryEmail,
            'mobile_number' => $primaryMobileNumber,
            'other_data' => $otherData ?? null,
        ];

        return new MedicalPractitioner($data);
    }
}
