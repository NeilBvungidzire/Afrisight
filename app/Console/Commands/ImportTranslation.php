<?php

namespace App\Console\Commands;

use App\Imports\AppTranslationsImport;
use App\Imports\ProfilingTranslationsImport;
use App\Translation;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportTranslation extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import translation from file into the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filePath = $this->choice('Which file do you want to import?', [
            1 => 'translation files',
            2 => 'app_data.xlsx',
            3 => 'profiling_data.xlsx',
        ]);

        switch ($filePath) {

            case 'translation files':
                $this->importTranslations();
                break;

            case 'app_data.xlsx':
                $this->importAppData($filePath);
                break;

            case 'profiling_data.xlsx':
                $this->importProfilingData($filePath);
                break;
        }
    }

    private function importTranslations(): void
    {
        $list = config('translation.key_file_mapping');

        foreach ($list as $keyPart => $path) {
            $tag = explode('.', $keyPart)[0];

            $languages = [
                'en',
                'fr',
                'pt',
            ];

            $translations = [];
            foreach ($languages as $language) {
                $records = \Illuminate\Support\Arr::dot([$keyPart => __($path, [], $language)]);

                foreach ($records as $recordKey => $recordText) {
                    $translations[$recordKey][$language] = $recordText;
                }
            }

            foreach ($translations as $translationKey => $translationText) {
                \App\Translation::create([
                    'key'          => $translationKey,
                    'text'         => $translationText,
                    'tags'         => [$tag],
                    'is_published' => true,
                ]);
            }
        }
    }

    private function importAppData(string $filePath): void
    {
        Excel::import(new AppTranslationsImport, $filePath);
    }

    private function importProfilingData(string $filePath): void
    {
        $fileData = Excel::toArray(new ProfilingTranslationsImport, $filePath);

        $data = [
            'questions' => [],
            'answers'   => [],
        ];
        foreach ($fileData[0] as $index => $rawData) {
            if ($index <= 1) {
                continue;
            }

            // Handle questions
            if ( ! empty($rawData[4])) {
                $id = (integer)$rawData[0];
                $key = "profiling.question.${id}";

                if ( ! isset($data['questions'][$key])) {
                    $data['questions'][$key] = [
                        'key'  => $key,
                        'text' => [
                            'en' => null,
                            'fr' => null,
                            'pt' => null,
                        ],
                        'tags' => ['profiling', 'question'],
                    ];
                }

                $data['questions'][$key]['text']['en'] = $rawData[4];
                $data['questions'][$key]['text']['fr'] = $rawData[6];
                $data['questions'][$key]['text']['pt'] = $rawData[8];
            }

            // Handle answers
            if ( ! empty($rawData[5])) {
                $key = "profiling.answer.${index}";

                if ( ! isset($data['answers'][$key])) {
                    $data['answers'][$key] = [
                        'key'  => $key,
                        'text' => [
                            'en' => null,
                            'fr' => null,
                            'pt' => null,
                        ],
                        'tags' => ['profiling', 'answer'],
                    ];
                }

                $data['answers'][$key]['text']['en'] = $rawData[5];
                $data['answers'][$key]['text']['fr'] = $rawData[7];
                $data['answers'][$key]['text']['pt'] = $rawData[9];
            }
        }

        foreach ($data['questions'] as $question) {
            Translation::create($question);
        }

        foreach ($data['answers'] as $answer) {
            Translation::create($answer);
        }
    }
}
