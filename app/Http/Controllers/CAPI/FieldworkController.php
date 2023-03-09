<?php

namespace App\Http\Controllers\CAPI;

use App\Constants\RespondentStatus;
use App\Interviewer;
use App\OtherRespondent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class FieldworkController extends Controller {

    public function entry(Request $request) {
        if ( ! $intId = $request->query('int_id')) {
            return redirect()->route('home');
        }

        $interviewer = Interviewer::query()
            ->where('key', $intId)
            ->first('sample_code');
        if ( ! $interviewer) {
            return redirect()->route('home');
        }

        $configs = config('capi.' . $interviewer->sample_code);
        if ( ! $configs) {
            return redirect()->route('home');
        }

        $isLive = $configs['live'] ?? false;

        return view('capi.fieldwork.entry', compact('intId', 'isLive'));
    }

    public function start(Request $request): RedirectResponse {
        if ( ! $intId = $request->query('int_id')) {
            return redirect()->route('home');
        }

        $interviewer = Interviewer::query()
            ->where('key', $intId)
            ->first('sample_code');
        if ( ! $interviewer) {
            return redirect()->route('home');
        }

        $sampleCode = $interviewer->sample_code;
        if ( ! $configs = config('capi.' . $sampleCode)) {
            return redirect()->route('home');
        }

        $isTest = $request->query('is_test') == 1;

        /** @var OtherRespondent $respondent */
        $respondent = OtherRespondent::query()
            ->where('sample_code', $sampleCode)
            ->where('status', RespondentStatus::SELECTED)
            ->when($isTest, function ($query) {
                $query->where('meta_data->is_test', true);
            })
            ->first();

        if ($respondent) {
            $respondent->interviewer_id = $intId;
            $respondent->save();
        } else {
            $uuid = Str::uuid()->toString();
            $data = [
                'uuid'           => $uuid,
                'sample_code'    => $sampleCode,
                'external_id'    => $uuid,
                'interviewer_id' => $intId,
                'source_id'      => 'lm001',
                'new_status'     => RespondentStatus::SELECTED,
                'meta_data'      => ['entry_link' => null],
            ];
            if ($isTest && $entryLink = $configs['config']['survey_link_test'] ?? null) {
                $data['meta_data']['entry_link'] = $entryLink;
                $data['meta_data']['is_test'] = true;

                /** @var OtherRespondent $respondent */
                $respondent = OtherRespondent::create($data);
            } elseif ($entryLink = $configs['config']['survey_link_live'] ?? null) {
                $data['meta_data']['entry_link'] = $entryLink;

                /** @var OtherRespondent $respondent */
                $respondent = OtherRespondent::create($data);
            } else {
                // No applicable entry link set in config.
                return redirect()->route('home');
            }
        }

        if ( ! $respondent) {
            return redirect()->route('home');
        }

        $respondent->new_status = RespondentStatus::STARTED;
        $respondent->save();

        $link = null;
        if ($sampleCode === 'borderless_access_011_za') {
            $link = Str::replaceArray("{VALUE}", [$respondent->external_id, $respondent->uuid],
                "https://surveys.borderlessaccess.com/survey/selfserve/596/211287?list=4&S2S=2&Pid={VALUE}&id={VALUE}");
        } elseif ($entryLink = $respondent->meta_data['entry_link'] ?? null) {
            $link = Str::replaceFirst("{RID}", $respondent->uuid, $entryLink);
        } elseif ($isTest && $entryLink = $configs['config']['survey_link_test'] ?? null) {
            $link = Str::replaceFirst("{RID}", $respondent->uuid, $entryLink);
        } elseif ( ! $isTest && $entryLink = $configs['config']['survey_link_live'] ?? null) {
            $link = Str::replaceFirst("{RID}", $respondent->uuid, $entryLink);
        }

        if ( ! $link) {
            return redirect()->route('home');
        }

        return redirect()->away($link);
    }
}
