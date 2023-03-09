<?php

namespace App\Cint;

use Exception;

trait RespondentApi
{

    public function retrieveRespondentByGuid(string $isoAlpha2Code, string $guid)
    {
        $panelConfigs = PanelApi::getPanelConfigs($isoAlpha2Code);

        // Panel is not configured.
        if (empty($panelConfigs)) {
            $this->setStates(0);

            return $this;
        }

        $this->headers = [
            'Authorization' => PanelApi::generatePanelToken($panelConfigs['key'], $panelConfigs['secret']),
        ];

        $panelUrlPath = PanelApi::generatePanelUrlPath($panelConfigs['key']);
        if (empty($panelUrlPath)) {
            $this->setStates(0);

            return $this;
        }

        $respondentUrl = "${panelUrlPath}/respondents/${guid}";

        try {
            return $this->from($respondentUrl, 'GET', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }
}
