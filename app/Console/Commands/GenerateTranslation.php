<?php

namespace App\Console\Commands;

use App\Translation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class GenerateTranslation extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:generate {separate=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get and put translations from DB into translation file for each locale.';

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
        $separate = (boolean)$this->argument('separate');

        if ($separate) {
            $this->generateSeparateFiles();
        } else {
            $this->generateFile();
        }

        $this->info('Done');
    }

    private function keyFileMapping(): array
    {
        return config('translation.key_file_mapping', []);
    }

    private function generateSeparateFiles(): void
    {
        $mapping = $this->keyFileMapping();

        foreach ($mapping as $keyLike => $filePath) {
            $translations = Translation::query()
                ->where('is_published', '=', 1)
                ->where('key', 'like', "${keyLike}%")
                ->get();

            // Remove file reference, because the reference is via file, instead of array key.
            $data = $this->getDataPerLocale($translations, "${keyLike}.");

            foreach ($data as $locale => $input) {
                Storage::disk('translations')->put("${locale}/${filePath}.php", $this->generateContent($input));
            }
        }
    }

    /**
     * Generate translation file.
     */
    private function generateFile(): void
    {
        $translations = $this->getAllPublishedTranslations();
        $data = $this->getDataPerLocale($translations);

        foreach ($data as $locale => $input) {
            Storage::disk('translations')->put("${locale}/generated.php", $this->generateContent($input));
        }
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function generateContent(array $data): string
    {
        $input = [];
        foreach ($data as $key => $text) {
            Arr::set($input, $key, $text);
        }

        $transformed = var_export($input, true);

        return <<<EOD
<?php

return $transformed;

EOD;
    }

    /**
     * @param Collection $translations
     * @param string     $replace
     *
     * @return array
     */
    private function getDataPerLocale(Collection $translations, string $replace = ''): array
    {
        $data = [];
        foreach ($translations as $translation) {
            foreach ($translation->text as $locale => $text) {
                if ( ! isset($data[$locale])) {
                    $data[$locale] = [];
                }

                $key = substr_replace($translation->key, '', 0, strlen($replace));
                $data[$locale][$key] = $text;
            }
        }

        return $data;
    }

    /**
     * @return Collection
     */
    private function getAllPublishedTranslations(): Collection
    {
        return Translation::query()
            ->where('is_published', '=', 1)
            ->get();
    }
}
