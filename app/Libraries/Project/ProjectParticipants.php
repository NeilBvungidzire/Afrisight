<?php

namespace App\Libraries\Project;

use App\Constants\RespondentStatus;
use App\Constants\TransactionType;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Respondent;
use App\Transaction;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ProjectParticipants {

    /**
     * @param string $projectCode
     * @param null|string|string[] $types
     *
     * @return Collection|null
     * @throws Exception
     */
    public static function getRequestedRewardTransactions(string $projectCode, $types = null): ?Collection
    {
        // Make sure only allowed transaction types are passed if any.
        $allowedTypes = TransactionType::getRewardingTypeConstants();;
        if (empty($types)) {
            $types = $allowedTypes;
        } elseif (is_string($types)) {
            $types = [$types];
        }
        $validTypes = array_intersect($allowedTypes, $types);
        if (count($validTypes) !== count($types)) {
            throw new Exception('Transaction type not allowed.');
        }

        $respondentsId = Respondent::query()
            ->where('current_status', RespondentStatus::COMPLETED)
            ->where('project_code', $projectCode)
            ->pluck('id')
            ->toArray();

        if (empty($respondentsId)) {
            return null;
        }

        $transactions = Transaction::query()
            ->whereIn('type', $types)
            ->where('status', TransactionStatus::REQUESTED)
            ->where(function (Builder $query) use ($respondentsId) {
                foreach ($respondentsId as $id) {
                    $query->orWhereJsonContains('meta_data->respondent_id', $id);
                }
            })
            ->get();

        if ($transactions->count() === 0) {
            return null;
        }

        return $transactions;
    }

    /**
     * @param string $projectCode
     * @param Closure|null $callable Optionally you can do further processing with the transaction after it is approved and updated.
     *
     * @return Transaction[]|null If array, it will contain all transaction which couldn't be approved.
     */
    public static function approveProjectParticipantsTransactions(string $projectCode, Closure $callable = null): ?array
    {
        try {
            $requestedTransactions = self::getRequestedRewardTransactions($projectCode);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return null;
        }

        $notUpdatedTransactions = [];
        foreach ($requestedTransactions as $transaction) {
            /** @var Transaction $transaction */
            $updated = $transaction->update([
                'new_status' => TransactionStatus::APPROVED,
            ]);

            if ($callable instanceof Closure) {
                $callable($transaction, $updated);
            }

            if ( ! $updated) {
                $notUpdatedTransactions[] = $transaction;
            }
        }

        return empty($notUpdatedTransactions) ? null : $notUpdatedTransactions;
    }
}
