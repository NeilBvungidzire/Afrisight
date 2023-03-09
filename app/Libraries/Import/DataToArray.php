<?php

namespace App\Libraries\Import;

class DataToArray {

    /**
     * @param string $data
     * @param string $delimiter
     *
     * @return array[] [columns => [...], data => [...]]
     */
    public static function transform(string $data, string $delimiter = ",")
    {
        $rawData = explode("\r\n", trim($data));

        $preparedData = [];
        $columns = [];
        foreach ($rawData as $index => $data) {
            $data = explode($delimiter, $data);
            unset($rawData[$index]);

            if ($index === 0) {
                $columns = $data;
                continue;
            }

            $preparedData[] = array_combine($columns, $data);
        }

        return [
            'columns' => $columns,
            'data'    => $preparedData,
        ];
    }
}
