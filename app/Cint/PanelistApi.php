<?php

namespace App\Cint;

use Exception;
use Illuminate\Support\Str;

trait PanelistApi {

    private $headers;

    /**
     * @param  int  $memberId
     * @return $this
     */
    public function retrievePanelistByMemberId(int $memberId) {
        try {
            $body = ['member_id' => $memberId];

            return $this->follow('panelists', 'GET', $this->headers, $body);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function retrievePanelistByEmail(string $email)
    {
        try {
            $body = ['email' => $email];

            return $this->follow('panelists', 'GET', $this->headers, $body);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function retrievePanelist()
    {
        try {
            return $this->follow('self', 'GET', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @param array $body
     *
     * @return $this
     */
    public function addPanelist(array $body)
    {
        try {
            return $this->follow('panelists', 'POST', $this->headers, $body);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @param array $body
     *
     * @return $this
     */
    public function updatePanelist(array $body)
    {
        try {
            return $this->follow('self', 'PATCH', $this->headers, $body);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function deletePanelist()
    {
        try {
            return $this->follow('self', 'DELETE', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function retrieveSurveyOpportunities()
    {
        try {
            return $this->follow('survey-opportunities', 'GET', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function retrieveSurveyInvitations()
    {
        try {
            return $this->follow('survey-invitations', 'GET', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @param int $paymentMethodId
     *
     * @return $this
     */
    public function payoutTransaction(int $paymentMethodId)
    {
        try {
            $body = [
                'transaction' => [
                    'type' => 'tt_pay',
                    'payment_method_id' => $paymentMethodId,
                    'identifier' => Str::random(52),
                ],
            ];
            return $this->follow('transactions', 'POST', $this->headers, $body);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    public function retrieveVariables()
    {
        try {
            return $this->follow('variables', 'GET', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }
}
