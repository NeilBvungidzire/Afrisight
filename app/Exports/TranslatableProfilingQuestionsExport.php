<?php

namespace App\Exports;

use App\ProfilingQuestion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class TranslatableProfilingQuestionsExport implements FromCollection {

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
        $profilingQuestions = ProfilingQuestion::select('id', 'uuid', 'title', 'type', 'answer_params', 'conditions')
            ->get();

        $formatted = [];
        foreach ($profilingQuestions as $profilingQuestion) {
            $questionData = [
                'id'      => $profilingQuestion->id,
                'uuid'    => $profilingQuestion->uuid,
                'title'   => $profilingQuestion->title,
                'type'    => $profilingQuestion->type,
                'country' => isset($profilingQuestion->conditions['country_id']) ? $profilingQuestion->conditions['country_id'] : '',
            ];

            $data = [];
            foreach ($profilingQuestion->answer_params as $answerParam) {
                $data[] = array_merge($questionData, [
                    'answer' => $answerParam['label']
                ]);
            }

            $formatted = array_merge($formatted, $data);
        }

        return new Collection($formatted);
    }
}
