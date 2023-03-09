<?php

namespace App\Http\Controllers\API;

use App\Language;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LanguageController extends ApiController {

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('manage-own-data');

        return response()->json([
            'error' => false,
            'data'  => Language::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('manage-own-data');

        $data = $request->all([
            'code',
            'title',
        ]);

        $validator = Validator::make($data, [
            'code'  => 'required|alpha|unique:languages|min:2|max:2',
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'data'  => $validator->errors(),
            ]);
        }

        if ($createdLanguage = Language::create($data)) {
            return response()->json([
                'error' => false,
                'data'  => $createdLanguage->toArray(),
            ]);
        }

        return response()->json([
            'error' => true,
            'data'  => __('Something went wrong'),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('manage-own-data');

        $language = Language::find($id);

        if ($language) {
            return response()->json([
                'error' => false,
                'data'  => $language->toArray(),
            ]);
        }

        return response()->json([
            'error' => true,
            'data'  => __('Something went wrong'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return void
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->authorize('manage-own-data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return void
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('manage-own-data');
    }
}
