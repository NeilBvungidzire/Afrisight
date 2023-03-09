<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class TranslatableStringsExport implements FromCollection {

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->getData();
    }

    /**
     * @return Collection
     */
    private function getData()
    {
        $list = [
            'alert',
            'auth',
            'countries',
            'footer',
            'general',
            'hero_unit',
            'pages',
            'pagination',
            'passwords',
            'profile',
            'questionnaire',
            'survey_redirects',
            'validation',
            'email/contact',
            'email/general',
            'email/new_registration',
            'email/new_registration_social_media',
            'email/reset_password_notification',
            'email/survey_invite',
            'model/person',
            'model/user',
        ];

        $listFlatten = [];
        foreach ($list as $file) {
            $dotted = Arr::dot(__($file));
            $data = [];

            foreach ($dotted as $path => $text) {
                $data[] = [
                    'file' => $file,
                    'path' => $path,
                    'text' => $text,
                ];
            }

            $listFlatten = array_merge($listFlatten, $data);
        }

        return new Collection($listFlatten);
    }
}
