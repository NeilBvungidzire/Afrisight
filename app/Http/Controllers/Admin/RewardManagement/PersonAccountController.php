<?php

namespace App\Http\Controllers\Admin\RewardManagement;

use App\Country;
use App\Http\Controllers\Controller;
use App\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PersonAccountController extends Controller {

    public function overview()
    {
        $this->authorize('reward-management');

        $countries = cache()->remember('COUNTRIES_BY_ID_NAME', now()->addDays(30), function () {
            return DB::table('countries')->pluck('name', 'id')->toArray();
        });
        $countries['empty'] = 'Not Set';

        $boxOptions = [
            'project_code',
            'respondent_uuid',
            'person_id',
            'email',
        ];

        $filters = [
            'country'   => [
                'label'         => 'Country (all)',
                'type'          => 'select',
                'current_value' => request()->query('country'),
                'options'       => $countries,
            ],
            'box_type'  => [
                'label'         => 'Specific (none)',
                'type'          => 'select',
                'current_value' => request()->query('box_type'),
                'options'       => array_combine($boxOptions, $boxOptions),
            ],
            'box_value' => [
                'label'         => 'Value',
                'type'          => 'text',
                'current_value' => request()->query('box_value'),
            ],
        ];

        $personsQuery = Person::withTrashed();

        // By country
        if ( ! empty($filters['country']['current_value'])) {
            $value = ($filters['country']['current_value'] === 'empty') ? null : $filters['country']['current_value'];
            $personsQuery->where('country_id', $value);
        }

        // Transaction for specific box filtering
        if ( ! empty($filters['box_type']['current_value']) && ! empty($filters['box_value']['current_value'])) {
            $boxType = $filters['box_type']['current_value'];
            $boxValue = $filters['box_value']['current_value'];

            $personsId = null;
            if ($boxType === 'project_code') {
                $personsId = DB::table('respondents')
                    ->where('project_code', $boxValue)
                    ->pluck('person_id')
                    ->toArray();
            }

            if ($boxType === 'respondent_uuid') {
                $personsId = DB::table('respondents')
                    ->where('uuid', $boxValue)
                    ->pluck('person_id')
                    ->toArray();
            }

            if (in_array($boxType, ['project_code', 'respondent_uuid'])) {
                if (empty($personsId)) {
                    // Avoid getting all when no person ID is found.
                    $personsQuery->where('id', null);
                } else {
                    // Avoid getting all when no person ID is found.
                    $personsQuery->whereIn('id', $personsId);
                }
            }

            if ($boxType === 'email') {
                $personId = DB::table('persons')->where('email', $boxValue)->value('id');
                $personsQuery->where('id', $personId);
            }

            if ($boxType === 'person_id') {
                $personsQuery->where('id', $boxValue);
            }
        }

        // Order
        $personsQuery->orderBy('updated_at', 'desc');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 30;

        $persons = $personsQuery->paginate($limit);

        return view('admin.reward-management.account', compact('filters', 'persons', 'countries'));
    }

    public function filter(): RedirectResponse
    {
        $this->authorize('reward-management');

        $filters = request()->all(['country', 'box_type', 'box_value']);

        return redirect()->route('admin.reward_management.member-account', $filters);
    }
}
