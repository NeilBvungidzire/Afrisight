<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\FlexTable;
use App\Libraries\Import\DataToArray;
use App\Mail\InstantLabsQuantOnlyReminder1h;
use App\Mail\InstantLabsQuantOnlyReminder20m;
use App\Mail\InstantLabsQuantOnlyReminder24h;
use App\Mail\InstantLabsQuantQualReminder1h;
use App\Mail\InstantLabsQuantQualReminder20m;
use App\Mail\InstantLabsQuantQualReminder24h;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InstantLabsController extends BaseController {

    /**
     * @var string
     */
    private $email = 'anca.nenciu@ipsos.com';
//    private $email = 'obayd.mir@afrisight.com';

    /**
     * @var string Reference for session and DB record code.
     */
    private $importDataKey = 'instant_labs_data';

    public function index()
    {
        $data = FlexTable::query()
            ->where('reference_code', $this->importDataKey)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.instant-labs.dashboard', compact('data'));
    }

    public function setData()
    {
        return view('admin.instant-labs.import-data');
    }

    public function readData()
    {
        $rawData = request()->get('data');

        // @todo check if data is not empty and is string.

        $preparedData = DataToArray::transform($rawData, "\t");

        request()->session()->put($this->importDataKey, $preparedData);

        return redirect()->route('admin.instant_labs.import_data.check');
    }

    public function checkData()
    {
        $importedData = session($this->importDataKey);
        if (empty($importedData)) {
            Alert::makeWarning('Could not read data');

            return redirect()->route('admin.instant_labs.import_data.set');
        }

        $columns = $importedData['columns'];
        $data = $importedData['data'];

        return view('admin.instant-labs.import-data-check', compact('columns', 'data'));
    }

    public function importData()
    {
        $importedData = request()->session()->pull($this->importDataKey);

        if ( ! isset($importedData['data'])) {
            Alert::makeWarning('Could not read data');

            return redirect()->route('admin.instant_labs.import_data.set');
        }

        $data = $importedData['data'];
        if (empty($data)) {
            Alert::makeWarning('Could not read data');

            return redirect()->route('admin.instant_labs.import_data.set');
        }

        DB::transaction(function () use ($data) {
            foreach ($data as $record) {
                FlexTable::create([
                    'reference_code' => $this->importDataKey,
                    'data'           => $record,
                ]);
            }
        });

        return redirect()->route('admin.instant_labs.dashboard');
    }

    public function findNotPlannedRespondents()
    {
        $data = $this->getNotPlannedRespondents();

        return view('admin.instant-labs.plan-find', compact('data'));
    }

    public function planRespondentsEngagements()
    {
        $data = $this->getNotPlannedRespondents();

        foreach ($data as $record) {
            if ( ! isset($record->data['INVITE'])) {
                continue;
            }

            $typeInvite = $record->data['INVITE'];
            $referenceDate = Date::createFromFormat(
                'Y-m-d H:i:s',
                $record->data['reference_datetime'],
                $record->data['reference_timezone']
            );
            $referenceDate = $referenceDate->setTimezone(config('timezone'))->format('Y-m-d H:i:s');

            $mailTypes = [
                '24h',
                '1h',
                '20m',
            ];

            if ($typeInvite === 'quantonly') {
                $this->handleQuantOnly($referenceDate, $record, $mailTypes);
            }
            if ($typeInvite === 'quantqual') {
                $this->handleQuantQual($referenceDate, $record, $mailTypes);
            }
        }

        return redirect()->route('admin.instant_labs.dashboard');
    }

    /**
     * @return Collection
     */
    private function getNotPlannedRespondents()
    {
        $data = FlexTable::query()
            ->where('reference_code', $this->importDataKey)
            ->whereJsonDoesntContain('data->planned', true)
            ->get();

        foreach ($data as $index => $record) {
            if ( ! isset($record->data['reference_datetime']) || ! isset($record->data['reference_timezone'])) {
                unset($data[$index]);
            }
        }

        return $data;
    }

    /**
     * @param string $referenceTime
     * @param FlexTable $record
     * @param array $mailTypes
     */
    private function handleQuantOnly(string $referenceTime, FlexTable $record, array $mailTypes)
    {
        $isPlanned = false;

        if (in_array('24h', $mailTypes)) {
            $reminder24H = Date::createFromFormat('Y-m-d H:i:s', $referenceTime)->subHours(24);
            Mail::to($this->email)->later($reminder24H, new InstantLabsQuantOnlyReminder24h($record->data));
            $isPlanned = true;
        }

        if (in_array('1h', $mailTypes)) {
            $reminder1H = Date::createFromFormat('Y-m-d H:i:s', $referenceTime)->subHours(1);
            Mail::to($this->email)->later($reminder1H, new InstantLabsQuantOnlyReminder1h($record->data));
            $isPlanned = true;
        }

        if (in_array('20m', $mailTypes)) {
            $reminder20M = Date::createFromFormat('Y-m-d H:i:s', $referenceTime)->subMinutes(20);
            Mail::to($this->email)->later($reminder20M, new InstantLabsQuantOnlyReminder20m($record->data));
            $isPlanned = true;
        }

        if ($isPlanned) {
            $record->update([
                'data' => array_merge($record->data, ['planned' => true]),
            ]);
        }
    }

    /**
     * @param string $referenceTime
     * @param FlexTable $record
     * @param array $mailTypes
     */
    private function handleQuantQual(string $referenceTime, FlexTable $record, array $mailTypes)
    {
        $isPlanned = false;

        if (in_array('24h', $mailTypes)) {
            $reminder24H = Date::createFromFormat('Y-m-d H:i:s', $referenceTime)->subHours(24);
            Mail::to($this->email)->later($reminder24H, new InstantLabsQuantQualReminder24h($record->data));
            $isPlanned = true;
        }

        if (in_array('1h', $mailTypes)) {
            $reminder1H = Date::createFromFormat('Y-m-d H:i:s', $referenceTime)->subHours(1);
            Mail::to($this->email)->later($reminder1H, new InstantLabsQuantQualReminder1h($record->data));
            $isPlanned = true;
        }

        if (in_array('20m', $mailTypes)) {
            $reminder20M = Date::createFromFormat('Y-m-d H:i:s', $referenceTime)->subMinutes(20);
            Mail::to($this->email)->later($reminder20M, new InstantLabsQuantQualReminder20m($record->data));
            $isPlanned = true;
        }

        if ($isPlanned) {
            $record->update([
                'data' => array_merge($record->data, ['planned' => true]),
            ]);
        }
    }
}
