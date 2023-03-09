<?php

namespace App\Cint;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

trait PanelApi {

    private $headers;

    /**
     * @param string $isoAlpha2Code
     *
     * @return $this
     */
    public function retrievePanel(string $isoAlpha2Code)
    {
        $panelConfigs = self::getPanelConfigs($isoAlpha2Code);

        // Panel is not configured.
        if (empty($panelConfigs)) {
            $this->setStates(0);

            return $this;
        }

        $this->headers = [
            'Authorization' => self::generatePanelToken($panelConfigs['key'], $panelConfigs['secret']),
        ];

        $panelUrlPath = self::generatePanelUrlPath($panelConfigs['key']);
        if (empty($panelUrlPath)) {
            $this->setStates(0);

            return $this;
        }

        try {
            return $this->from($panelUrlPath, 'GET', $this->headers);
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool|string
     */
    public static function generatePanelUrlPath(string $key)
    {
        $panelUrlPath = config('cint.panel_url_path');

        if (empty($panelUrlPath)) {
            Log::channel('cint')->error("Base URI for panel with key {$key} not set.");

            return false;
        }

        return "{$panelUrlPath}/{$key}";
    }

    /**
     * @param string $key
     * @param string $secret
     *
     * @return string
     */
    public static function generatePanelToken(string $key, string $secret)
    {
        return 'Basic' . ' ' . base64_encode($key . ':' . $secret);
    }

    /**
     * Retrieve panel configs by the panel's country ISO Alpha 2 Code. Checks also for presence of minimal
     * configuration elements.
     *
     * @param string $isoAlpha2Code
     *
     * @return bool|array
     */
    public static function getPanelConfigs(string $isoAlpha2Code)
    {
        $panelName = self::getPanelNameByIsoAlphaCode($isoAlpha2Code);
        $panelConfigs = config("cint.panels.${panelName}");

        if (empty($panelConfigs)) {
            Log::channel('cint')->error("Configuration for panel in country with ISO Alpha Code {$isoAlpha2Code} is not set.");

            return false;
        }

        $requiredVariables = [
            'key',
            'secret',
            'country.iso_alpha_2',
        ];
        foreach ($requiredVariables as $path) {
            if ( ! Arr::get($panelConfigs, $path)) {
                Log::channel('cint')->error("Configuration item {$path} for panel in country with ISO Alpha Code {$isoAlpha2Code} is not set.");

                return false;
            }
        }

        return $panelConfigs;
    }

    /**
     * Retrieve panel name by the panel's country ISO Alpha 2 Code.
     *
     * @param string $isoAlpha2Code
     *
     * @return bool|string
     */
    private static function getPanelNameByIsoAlphaCode(string $isoAlpha2Code)
    {
        $panels = config("cint.panels");

        if (empty($panels) || ! is_array($panels)) {
            return false;
        }

        foreach ($panels as $panelName => $panelConfigs) {
            $foundIsoAlpha2Code = Arr::get($panelConfigs, 'country.iso_alpha_2');
            if (strtoupper($foundIsoAlpha2Code) === strtoupper($isoAlpha2Code)) {
                return $panelName;
            }
        }

        return false;
    }

    /**
     * @param string|null $string
     *
     * @return bool|float
     */
    public static function tryGetAmountFromString(?string $string = null)
    {
        if (empty($string)) {
            return false;
        }

        $exploded = explode(' ', $string);
        $probablyAmount = floatval($exploded[0]);

        if ( ! is_float($probablyAmount)) {
            Log::channel('cint')->warning('Could not format balance from Cint into amount.', $exploded);

            return false;
        }

        return $probablyAmount;
    }
}
