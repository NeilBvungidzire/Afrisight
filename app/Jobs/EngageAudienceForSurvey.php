<?php

namespace App\Jobs;

use App\Constants\InvitationType;
use App\Constants\RespondentInvitationStatus;
use App\Constants\RespondentStatus;
use App\Mail\RespondentInvitationVariant1;
use App\Person;
use App\Respondent;
use App\RespondentInvitation;
use CMText\Channels;
use CMText\Message;
use CMText\TextClient;
use CMText\TextClientStatusCodes;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EngageAudienceForSurvey implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 300;

    /**
     * @var array
     */
    protected $personsId;

    /**
     * @var string
     */
    protected $projectCode;

    /**
     * @var array
     */
    protected $package;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $additionalMetaData;

    /**
     * @var string|null
     */
    protected $invitationHandlerClassname;

    /**
     * Create a new job instance.
     *
     * @param array       $personsId
     * @param string      $projectCode
     * @param array       $package
     * @param string      $type
     * @param array       $additionalMetaData
     * @param string|null $invitationHandlerClassname
     */
    public function __construct(
        array  $personsId,
        string $projectCode,
        array  $package,
        string $type,
        array  $additionalMetaData = [],
        string $invitationHandlerClassname = null
    )
    {
        $this->personsId = $personsId;
        $this->projectCode = $projectCode;
        $this->package = $package;
        $this->type = $type;
        $this->additionalMetaData = $additionalMetaData;
        $this->invitationHandlerClassname = $invitationHandlerClassname;
    }

    public function tags(): array
    {
        return [
            'AudienceEngagement',
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $persons = Person::query()
            ->whereIn('id', $this->personsId)
            ->get();

        foreach ($persons as $person) {
            $this->handleEngagement($person, $this->projectCode, $this->package, $this->type);
        }
    }

    private function handleEngagement(Person $person, string $projectCode, array $package, string $type)
    {
        // Check if already was respondent in project.
        $respondent = Respondent::query()
            ->where('person_id', $person->id)
            ->where('project_code', $projectCode)
            ->first();

//        if ($respondent && in_array($respondent->current_status, [
//                RespondentStatus::CLOSED,
//                RespondentStatus::COMPLETED,
//                RespondentStatus::DISQUALIFIED,
//            ], true)) {
//            return null;
//        }

        if (empty($respondent)) {
            $respondent = $person->respondent()->create([
                'person_id'        => $person->id,
                'project_code'     => $projectCode,
                'current_status'   => RespondentStatus::SELECTED,
                'status_history'   => [RespondentStatus::SELECTED => date('Y-m-d H:i:s')],
                'incentive_amount' => $package['usd_amount'] ?? 0,
            ]);
        }

        if (empty($respondent)) {
            return null;
        }

        switch ($type) {

            case InvitationType::EMAIL:
                $this->handleEmailInvite($person, $respondent, $package);
                break;

            case InvitationType::SMS:
                $this->handleSMSInvite($person, $respondent, $package);
                break;
        }
    }

    private function handleSMSInvite(Person $person, Respondent $respondent, array $package)
    {
        if (empty($person->mobile_number)) {
            return null;
        }
        $mobileNumber = $person->mobile_number;

        // Generate, but don't save yet, a respondent invitation.
        $invitation = new RespondentInvitation();
        $uuid = Str::uuid()->toString();
        $invitation->fill([
            'respondent_id' => $respondent->id,
            'uuid'          => $uuid,
            'type'          => InvitationType::SMS,
            'status'        => RespondentInvitationStatus::SEND,
        ]);

        try {
            $client = new TextClient('852D5D9D-E3C2-4F0D-997F-132878017760');
            $message = new Message(__('sms/survey_invite_variant_1.message', [
                'loi'            => $package['loi'],
                'local_currency' => $package['local_currency'],
                'local_amount'   => number_format($package['local_amount'], 2),
                'base_currency'  => 'USD',
                'base_amount'    => number_format($package['usd_amount'], 2),
                'link'           => route('invitation.land', ['uuid' => $uuid]),
            ], strtolower($person->language_code)), 'AfriSight', [$mobileNumber]);
            $message->WithChannels([Channels::SMS]);
            $result = $client->send([$message]);

            if ($result->statusCode !== TextClientStatusCodes::OK) {
                return null;
            }
        } catch (Exception $exception) {
            return null;
        }

        $newStatus = $respondent->current_status === RespondentStatus::SELECTED
            ? RespondentStatus::INVITED
            : RespondentStatus::RESELECTED;

        $metaData = $selectedRespondent->meta_data ?? [];

        $respondent->update([
            'current_status'   => $newStatus,
            'status_history'   => array_merge($respondent->status_history, [
                $newStatus => date('Y-m-d H:i:s'),
            ]),
            'meta_data'        => array_merge($this->additionalMetaData, $metaData, $package),
            'incentive_amount' => $package['usd_amount'] ?? 0,
        ]);

        $invitation->save();
    }

    private function handleEmailInvite(Person $person, Respondent $respondent, array $package)
    {
        $newStatus = $respondent->current_status === RespondentStatus::SELECTED
            ? RespondentStatus::INVITED
            : RespondentStatus::RESELECTED;

        $metaData = $selectedRespondent->meta_data ?? [];

        $respondent->update([
            'current_status'   => $newStatus,
            'status_history'   => array_merge($respondent->status_history, [
                $newStatus => date('Y-m-d H:i:s'),
            ]),
            'meta_data'        => array_merge($this->additionalMetaData, $metaData, $package),
            'incentive_amount' => $package['usd_amount'] ?? 0,
        ]);

        $invitation = $respondent->invitations()->create([
            'type'   => InvitationType::EMAIL,
            'status' => RespondentInvitationStatus::SEND,
        ]);

        $mailable = $this->invitationHandlerClassname ?? RespondentInvitationVariant1::class;
        $mailAddress = app()->environment('production')
            ? $person->email
            : config('app.test_mail_address');

        Mail::to($mailAddress)
            ->locale(strtolower($person->language_code))
            ->queue(new $mailable($invitation->uuid, $package));
    }
}
