<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\ProfilingQuestion as ProfilingQuestionResource;
use App\ProfilingQuestion;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfilingQuestionController extends ApiController {

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('manage-own-data');

        $profilingQuestion = ProfilingQuestion::withoutGlobalScope('is_published')->get();

        return response()->json([
            'error' => false,
            'data'  => ProfilingQuestionResource::collection($profilingQuestion),
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

        $data = $request->only([
            'title',
            'type',
            'isPublished',
            'isDefinitive',
            'settings',
            'sort',
            'answerParams',
        ]);

        $validator = Validator::make($data, [
            'title'               => 'required|string',
            'type'                => 'required|in:' . implode(',', config('profiling.supported_question_types')),
            'isPublished'         => 'sometimes|boolean',
            'isDefinitive'        => 'sometimes|boolean',
            'settings'            => 'required|array',
            'settings.isRequired' => 'required|boolean',
            'sort'                => 'sometimes|required|integer',
            'answerParams'        => 'required|array',
            'answerParams.*'      => [
                function ($attribute, $value, $fail) use ($data) {
                    switch ($data['type']) {
                        case 'MULTIPLE_CHOICE':
                        case 'CHECKBOXES':
                        case 'DROPDOWN':
                            if (empty($value['label'])) {
                                $fail('Label is required');
                            }
                            break;
                        case 'SINGLE_TEXT_BOX':
                            if (empty($value)) {
                                $fail('Value type is required');
                            }
                            break;
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'data'  => $validator->errors(),
            ]);
        }

        $camelToSnakeCaseData = [];
        foreach ($data as $key => $value) {
            $camelToSnakeCaseData[Str::snake($key)] = $value;
        }

        // Add id to answer params.
        switch ($camelToSnakeCaseData['type']) {
            case 'MULTIPLE_CHOICE':
            case 'CHECKBOXES':
            case 'DROPDOWN':
                foreach ($camelToSnakeCaseData['answer_params'] as $key => $value) {
                    $camelToSnakeCaseData['answer_params'][$key]['uuid'] = Str::uuid();
                }
                break;
        }

        $createdProfilingQuestion = ProfilingQuestion::create($camelToSnakeCaseData);

        if ( ! $createdProfilingQuestion) {
            Log::error('Could not create ProfilingQuestion', ['data' => $camelToSnakeCaseData]);

            return response()->json([
                'error' => true,
                'data'  => __('Something went wrong. We could not create this profiling question.'),
            ]);
        }

        // Get also  sort column, which is by default set during record creation by the DB.
        $createdProfilingQuestionRefreshed = $createdProfilingQuestion->fresh();

        return response()->json([
            'error' => false,
            'data'  => new ProfilingQuestionResource($createdProfilingQuestionRefreshed),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, int $id)
    {
        $this->authorize('manage-own-data');

        $profilingQuestion = ProfilingQuestion::withoutGlobalScope('is_published')
            ->where('id', $id)
            ->first();

        if ( ! $profilingQuestion) {
            return response()->json([
                'error' => true,
                'data'  => __('Couldn\'t find the profiling question.'),
            ]);
        }

        if ($profilingQuestion->is_published) {
            return response()->json([
                'error' => true,
                'data'  => 'You can\'t change a published question.',
            ]);
        }

        $data = $request->only([
            'title',
            'type',
            'isPublished',
            'isDefinitive',
            'settings',
            'sort',
            'answerParams',
        ]);

        $validator = Validator::make($data, [
            'title'               => 'sometimes|required|string',
            'type'                => 'sometimes|required|in:' . implode(',',
                    config('profiling.supported_question_types')),
            'isPublished'         => 'sometimes|boolean',
            'isDefinitive'        => 'sometimes|boolean',
            'settings'            => 'sometimes|required|array',
            'settings.isRequired' => 'sometimes|required|boolean',
            'sort'                => 'sometimes|integer',
            'answerParams'        => 'required|array',
            'answerParams.*'      => [
                function ($attribute, $value, $fail) use ($data) {
                    switch ($data['type']) {
                        case 'MULTIPLE_CHOICE':
                        case 'CHECKBOXES':
                        case 'DROPDOWN':
                            if (empty($value['label'])) {
                                $fail('Label is required');
                            }
                            break;
                        case 'SINGLE_TEXT_BOX':
                            if (empty($value)) {
                                $fail('Value type is required');
                            }
                            break;
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'data'  => $validator->errors(),
            ]);
        }

        $camelToSnakeCaseData = [];
        foreach ($data as $key => $value) {
            $camelToSnakeCaseData[Str::snake($key)] = $value;
        }

        // Add id to answer params.
        switch ($camelToSnakeCaseData['type']) {
            case 'MULTIPLE_CHOICE':
            case 'CHECKBOXES':
            case 'DROPDOWN':
                $answerParamUuids = data_get($profilingQuestion->answer_params, '*.uuid');

                foreach ($camelToSnakeCaseData['answer_params'] as $key => $answerParam) {
                    $tempAnswerParam = [
                        'label' => $answerParam['label'],
                    ];

                    // Passed answer param does not include a UUID, so it's a new answer param.
                    if ( ! isset($answerParam['uuid'])) {
                        $tempAnswerParam['uuid'] = Str::uuid();
                        $camelToSnakeCaseData['answer_params'][$key] = $tempAnswerParam;
                        continue;
                    }

                    // Passed answer param with this UUID exist in DB, so let it be saved.
                    if (in_array($answerParam['uuid'], $answerParamUuids)) {
                        $tempAnswerParam['uuid'] = $answerParam['uuid'];
                        $camelToSnakeCaseData['answer_params'][$key] = $tempAnswerParam;
                        continue;
                    }

                    // Passed answer param with this UUID does not exist in DB, so filter out this one.
                    unset($camelToSnakeCaseData['answer_params'][$key]);
                }
                break;
        }

        $updated = $profilingQuestion->fill($camelToSnakeCaseData)->save();

        if ( ! $updated) {
            Log::error('Could not update ProfilingQuestion', ['data' => $data]);

            return response()->json([
                'error' => true,
                'data'  => __('Something went wrong. We could not update this profiling question.'),
            ]);
        }

        return response()->json([
            'error' => false,
            'data'  => new ProfilingQuestionResource($profilingQuestion),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(int $id)
    {
        $this->authorize('manage-own-data');

        $profilingQuestion = ProfilingQuestion::withoutGlobalScope('is_published')
            ->where('id', $id)
            ->first();

        if ( ! $profilingQuestion) {
            return response()->json([
                'error' => true,
                'data'  => __('Couldn\'t find the profiling question.'),
            ]);
        }

        if ($profilingQuestion->is_published || $profilingQuestion->is_definitive) {
            return response()->json([
                'error' => true,
                'data'  => __('You can\'t delete a published/definitive question.'),
            ]);
        }

        try {
            $profilingQuestion->delete();

            return response()->json([
                'error' => false,
                'data'  => __('Question removed successfully.'),
            ]);
        } catch (Exception $exception) {
            Log::error('Could not remove profiling question.', ['data' => $profilingQuestion->toArray()]);
        }

        return response()->json([
            'error' => true,
            'data'  => __('Something went wrong.'),
        ]);
    }
}
