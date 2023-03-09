<?php

namespace App\Http\Controllers\CAPI;

use App\Constants\RespondentStatus;
use App\OtherRespondent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class SurveyEntryLinksController extends Controller {

    public function edit() {
        $this->authorize('admin-projects');

        return view('capi.import.edit');
    }

    public function create(Request $request) {
        $this->authorize('admin-projects');

        $sampleCode = $request->get('sample_code');
        $entryLinks = array_filter(explode("\r\n", trim($request->get('entry_links'))));

        foreach ($entryLinks as $entryLink) {
            $uuid = Str::uuid()->toString();

            $data = [
                'uuid'        => $uuid,
                'sample_code' => $sampleCode,
                'external_id' => $uuid,
                'source_id'   => 'lm001',
                'new_status'  => RespondentStatus::SELECTED,
                'meta_data'   => ['entry_link' => $entryLink],
            ];
            OtherRespondent::create($data);
        }

        return redirect()->route('capi.admin.import');
    }
}
