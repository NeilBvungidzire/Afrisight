<?php

namespace App\Imports;

use App\Translation;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AppTranslationsImport implements ToModel, WithHeadingRow {

    /**
     * @param array $row
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        $key = str_replace('/', '.', $row['file']) . '.' . $row['path'];
        if ($key === '.') {
            return null;
        }

        $text = [];
        if (isset($row['english'])) {
            $text['en'] = $this->cleanText($row['english']);
        }
        if (isset($row['portuguese'])) {
            $text['pt'] = $this->cleanText($row['portuguese']);
        }
        if (isset($row['french'])) {
            $text['fr'] = $this->cleanText($row['french']);
        }

        return new Translation([
            'key'  => $key,
            'text' => $text,
        ]);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function cleanText(string $input)
    {
        return trim(preg_replace('/\s+/', ' ', $input));
    }
}
