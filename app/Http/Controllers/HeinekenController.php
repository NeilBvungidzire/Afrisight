<?php

namespace App\Http\Controllers;

use App\Heineken\Heineken;
use App\Libraries\Elastic\Elastic;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HeinekenController extends Controller {

    /**
     * @var array[]
     */
    private $mapping = [
        'salone_consumer' => [
            'active'    => true,
            'index'     => 'heineken_salone_consumer',
            'file_path' => 'heineken-results/Salone Consumer.csv',
        ],
        'salone_customer' => [
            'active'    => true,
            'index'     => 'heineken_salone_customer',
            'file_path' => 'heineken-results/Salone Customer.csv',
        ],
        'trenk_consumer'  => [
            'active'    => true,
            'index'     => 'heineken_trenk_consumer',
            'file_path' => 'heineken-results/Trenk Consumer.csv',
        ],
        'trenk_customer'  => [
            'active'    => true,
            'index'     => 'heineken_trenk_customer',
            'file_path' => 'heineken-results/Trenk Customer.csv',
        ],
    ];

    public function overview()
    {
        $this->authorize('heineken-project');

        $files = [];
        foreach ($this->mapping as $index => $item) {
            $files[$index] = $item['file_path'];
        }

        $overview = (new Heineken())->overview($files);

        $data = $overview->generateOverview();
        $dates = $overview->getFoundDates();

        return view('heineken.overview', compact('data', 'dates'));
    }

    /**
     * @param string $survey
     *
     * @return JsonResponse|RedirectResponse
     */
    public function data(string $survey)
    {
        try {
            $this->authorize('heineken-project');
        } catch (AuthorizationException $exception) {
            return redirect()->route('login');
        }

        if ( ! isset($this->mapping[$survey]) || ! $this->mapping[$survey]['active']) {
            return response()->json(null, 204);
        }

        $data = $this->getData($survey);

        if (request()->query('elastic') && ! empty($data) && isset($this->mapping[$survey]['index']) && $version = request()->query('version')) {
            $data = $this->addToElastic($this->mapping[$survey]['index'], $data, $version);
        }

        return response()->json($data);
    }

    public function table(string $survey)
    {
        try {
            $this->authorize('heineken-project');
        } catch (AuthorizationException $exception) {
            return redirect()->route('login');
        }

        if ( ! isset($this->mapping[$survey]) || ! $this->mapping[$survey]['active']) {
            return response()->json(null, 204);
        }

        $data = $this->getData($survey);

        $columnsUnsorted = [];
        $labeledData = [];
        foreach ($data as $record) {
            $result = [];

            foreach ($record as $questionId => $item) {
                $key = "${questionId}";

                if ( ! is_array($item)) {
                    $result[$key] = $item;

                    if ( ! array_key_exists($key, $columnsUnsorted)) {
                        $columnsUnsorted[$key] = 1;
                        continue;
                    }
                    continue;
                }

                if (is_array($item) && empty($item)) {
                    $result[$key] = $item;

                    if ( ! array_key_exists($key, $columnsUnsorted)) {
                        $columnsUnsorted[$key] = 1;
                        continue;
                    }
                    continue;
                }

                if ( ! is_array($item[0])) {
                    $result[$key] = $item;

                    if ( ! array_key_exists($key, $columnsUnsorted)) {
                        $columnsUnsorted[$key] = count($item);
                        continue;
                    }

                    if (count($item) > $columnsUnsorted[$key]) {
                        $columnsUnsorted[$key] = count($item);
                        continue;
                    }

                    continue;
                }

                // Question[][label, value[]|string]
                foreach ($item as $value) {
                    $key = "${questionId}:{$value['label']}";
                    $result[$key] = $value['value'];

                    if ( ! is_array($value['value'])) {

                        if ( ! array_key_exists($key, $columnsUnsorted)) {
                            $columnsUnsorted[$key] = 1;
                        }

                        continue;
                    }

                    if ( ! array_key_exists($key, $columnsUnsorted)) {
                        $columnsUnsorted[$key] = count($value['value']);;
                        continue;
                    }

                    if (count($value['value']) > $columnsUnsorted[$key]) {
                        $columnsUnsorted[$key] = count($value['value']);
                        continue;
                    }
                }
            }

            $labeledData[] = $result;
        }

        $columnsSorted = [];
        foreach (array_keys($data[0]) as $questionId) {
            foreach ($columnsUnsorted as $key => $count) {
                $splitKey = explode(':', $key);

                if ($questionId !== $splitKey[0]) {
                    continue;
                }

                if ($count === 1) {
                    $columnsSorted[] = $key;
                    continue;
                }

                $columnsSorted = array_merge($columnsSorted, array_fill(0, $count, $key));
            }
        }

        $columns = [];
        foreach (array_flip($columnsSorted) as $key => $index) {
            $columns[$key] = $columnsUnsorted[$key];
        }

        $results = [];
        foreach ($labeledData as $record) {
            $result = [];

            foreach ($record as $key => $value) {
                if ($columns[$key] === 1) {
                    $result[$key] = $value;
                    continue;
                }

                $result[$key] = array_merge((array)$value, array_fill(0, ($columns[$key] - count((array)$value)), null));
            }

            $results[] = $result;
        }

        return view('heineken.table-format', compact('results', 'columns'));
    }

    /**
     * @param string $survey
     *
     * @return array|null
     */
    private function getData(string $survey)
    {
        if ( ! isset($this->mapping[$survey]) || ! $this->mapping[$survey]['active']) {
            return null;
        }

        $cacheKey = strtoupper("heineken_${survey}_data");
        try {
            return cache()->remember($cacheKey, now()->addDays(10), function () use ($survey) {
                return (new Heineken())->getData($survey, $this->mapping[$survey]['file_path']);
            });
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $index
     * @param array $data
     * @param string $version
     *
     * @return array
     */
    private function addToElastic(string $index, array $data, string $version)
    {
        $elastic = new Elastic();

        $results = [];
        foreach ($data as $record) {
            if ($record['status'] !== 'Complete') {
                continue;
            }

            $results[] = $elastic->addDocument($index, $data['respondent_id'], $record, $version);
        }

        return $results;
    }
}
