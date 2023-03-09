<?php

use App\Constants\RespondentInvitationStatus;
use App\Constants\RespondentStatus;
use App\Respondent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class TransferInviteUuidToRespondentUuid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Respondent::query()
            ->whereNull('uuid')
            ->with('invitations')
            ->chunk(50, function (Collection $respondents) {

                foreach ($respondents as $respondent) {

                    $invitations = $respondent->invitations;

                    // No invite set.
                    if ($invitations->count() === 0) {
                        $respondent->uuid = Str::uuid()->toString();
                        $respondent->save();
                        continue;
                    }

                    // Has only one invitation, so use the UUID from this one.
                    if ($invitations->count() === 1) {
                        $respondent->uuid = $respondent->invitations->first()->uuid;
                        $respondent->save();
                        continue;
                    }

                    $openedInvitatons = [];
                    $lastInvitation = null;
                    foreach ($invitations as $invitation) {
                        if ($invitation->status === RespondentInvitationStatus::OPENED) {
                            $openedInvitatons[] = $invitation;
                        }

                        $lastInvitation = $invitation;
                    }

                    if (count($openedInvitatons) === 0) {
                        $respondent->uuid = $lastInvitation->uuid;
                        $respondent->save();
                        continue;
                    }

                    if (count($openedInvitatons) === 1) {
                        $respondent->uuid = $openedInvitatons[0]->uuid;
                        $respondent->save();
                        continue;
                    }

                    if (isset($respondent->status_history[RespondentStatus::ENROLLING])) {
                        $inviteToUse = null;
                        $mostNearTime = null;
                        $enrollmentTime = $respondent->status_history[RespondentStatus::ENROLLING];

                        foreach ($openedInvitatons as $invitation) {
                            $currentInviteDifference = $invitation->updated_at->diffInSeconds($enrollmentTime, false);

                            if ($currentInviteDifference < 0) {
                                continue;
                            }

                            if ($mostNearTime === null || $currentInviteDifference < $mostNearTime) {
                                $mostNearTime = $currentInviteDifference;
                                $inviteToUse = $invitation;

                                continue;
                            }
                        }

                        if ($inviteToUse) {
                            $respondent->uuid = $inviteToUse->uuid;
                            $respondent->save();

                            continue;
                        }
                    }

                    if (isset($respondent->status_history[RespondentStatus::STARTED])) {
                        $inviteToUse = null;
                        $mostNearTime = null;
                        $enrollmentTime = $respondent->status_history[RespondentStatus::STARTED];

                        foreach ($openedInvitatons as $invitation) {
                            $currentInviteDifference = $invitation->updated_at->diffInSeconds($enrollmentTime, false);

                            if ($currentInviteDifference < 0) {
                                continue;
                            }

                            if ($mostNearTime === null || $currentInviteDifference < $mostNearTime) {
                                $mostNearTime = $currentInviteDifference;
                                $inviteToUse = $invitation;

                                continue;
                            }
                        }

                        if ($inviteToUse) {
                            $respondent->uuid = $inviteToUse->uuid;
                            $respondent->save();

                            continue;
                        }
                    }

                    $statuses = [
                        RespondentStatus::SELECTED,
                        RespondentStatus::INVITED,
                        RespondentStatus::TARGET_UNSUITABLE,
                        RespondentStatus::REMINDED,
                        RespondentStatus::RESELECTED,
                        RespondentStatus::ENROLLING,
                    ];
                    if (in_array($respondent->current_status, $statuses)) {
                        $respondent->uuid = $lastInvitation->uuid;
                        $respondent->save();
                        continue;
                    }

                    $projectsCode = [
                        'pdi_1',
                        'msi_001',
                        'af_001_za',
                        'af_001_ng',
                    ];
                    if ($respondent->current_status === RespondentStatus::CLOSED && in_array($respondent->project_code, $projectsCode)) {
                        $respondent->uuid = $lastInvitation->uuid;
                        $respondent->save();
                        continue;
                    }
                }

            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
