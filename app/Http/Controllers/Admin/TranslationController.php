<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Role;
use App\Translation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TranslationController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return View
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('manage-translations');

        $query = Translation::query();
        if (request()->query('tags')) {
            $tags = request()->query('tags');
            $query->whereJsonContains('tags', $tags);
        }
        if (request()->query('published') !== null) {
            $published = request()->query('published');
            $query->where('is_published', '=', $published);
        }
        $translations = $query->get();

        $elements = [];
        foreach ($translations as $translation) {
            $elements[$translation->id] = $this->generateTranslationParams($translation);
        }

        return view('admin.translation.index', compact('translations', 'elements'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('manage-translations');

        return view('admin.translation.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('manage-translations');

        $data = $request->all([
            'key',
            'tags',
            'text',
        ]);

        $data['tags'] = Translation::convertTagsToArray($data['tags']);

        $validator = Validator::make($data, [
            'key' => ['required', 'string', 'unique:translations'],
        ]);

        $validator->validate();

        $data['text'] = $this->cleanText($data['text']);

//        if (isset($data['text']['en'])) {
//            $data['text'] = array_merge($data['text'], $this->getTranslationByGoogle($data['text']['en']));
//        }

        Translation::create($data);

        return redirect()->route('translation.index');
    }

    /**
     * Display the specified resource.
     *
     * @param Translation $translation
     *
     * @return View
     * @throws AuthorizationException
     */
    public function show(Translation $translation)
    {
        $this->authorize('manage-translations');

        $elements = $this->generateTranslationParams($translation);

        return view('admin.translation.show', compact('translation', 'elements'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Translation $translation
     *
     * @return View
     * @throws AuthorizationException
     */
    public function edit(Translation $translation)
    {
        $this->authorize('manage-translations');

        $elements = $this->generateTranslationParams($translation);

        $translation->tags = Translation::convertTagsToString($translation->tags);

        return view('admin.translation.edit', compact('translation', 'elements'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request     $request
     * @param Translation $translation
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Translation $translation)
    {
        $this->authorize('manage-translations');

        $data = $request->all([
            'key',
            'tags',
            'text',
        ]);

        $validator = Validator::make($data, [
            'key' => ['required', 'string'],
        ]);

        $validator->validate();

        $data['tags'] = Translation::convertTagsToArray($data['tags']);

        $data['text'] = $this->cleanText($data['text']);
        $translation->update($data);

        return redirect()->route('translation.edit', ['translation' => $translation]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Translation $translation
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy(Translation $translation)
    {
        $this->authorize('manage-translations');

        if (Auth::user()->role === Role::SUPER_ADMIN) {
            $translation->delete();
        }

        return redirect()->route('translation.index');
    }

    /**
     * @param string|null $string
     *
     * @return array
     */
    private function getTranslationParams($string = null)
    {
        if ( ! $string) {
            return [];
        }

        preg_match_all('/:[0-9a-z_]+/', $string, $elements);

        return Arr::collapse($elements);
    }

    /**
     * @param Translation $translation
     *
     * @return array
     */
    private function generateTranslationParams(Translation $translation)
    {
        $elements = [
            'list'      => [],
            'by_locale' => [],
        ];
        foreach ($translation->text as $locale => $text) {
            $list = $this->getTranslationParams($text);
            $elements['list'] = array_merge($elements['list'], $list);
            $elements['by_locale'][$locale] = $list;
        }
        $elements['list'] = array_unique($elements['list']);

        return $elements;
    }

    /**
     * @param array $input
     *
     * @return array
     */
    private function cleanText(array $input)
    {
        foreach ($input as $locale => $text) {
            $input[$locale] = trim(preg_replace('/\s+/', ' ', $text));
        }

        return $input;
    }

    private function getTranslationByGoogle(string $englishText): array
    {
        $apiKey = 'AIzaSyAkfecVL0wdE113sc-sHm6_wBEFf4IxnDE';
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://translation.googleapis.com',
        ]);

        $response = $client->post('/language/translate/v2', [
            'query' => [
                'key'    => $apiKey,
                'source' => 'en',
                'target' => 'fr',
                'q'      => $englishText,
            ],
        ])->getBody()->getContents();
        $fr = json_decode($response, true)['data']['translations'][0]['translatedText'] ?? '';

        $response = $client->post('/language/translate/v2', [
            'query' => [
                'key'    => $apiKey,
                'source' => 'en',
                'target' => 'pt',
                'q'      => $englishText,
            ],
        ])->getBody()->getContents();
        $pt = json_decode($response, true)['data']['translations'][0]['translatedText'] ?? '';

        return [
            'fr' => htmlspecialchars_decode($fr, ENT_QUOTES),
            'pt' => htmlspecialchars_decode($pt, ENT_QUOTES),
        ];
    }
}
