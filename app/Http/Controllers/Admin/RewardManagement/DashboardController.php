<?php

namespace App\Http\Controllers\Admin\RewardManagement;

use App\Country;
use App\Http\Controllers\Admin\BaseController;

class DashboardController extends BaseController {

    public function __invoke()
    {
        $this->authorize('reward-management');

        $payoutMethodsByCountry = $this->getPayoutMethodsByCountry();

        return view('admin.reward-management.dashboard', compact('payoutMethodsByCountry'));
    }

    private function getPayoutMethodsByCountry(): array
    {
        $countryNameByCode = Country::query()
            ->pluck('name', 'iso_alpha_2');

        $list = [];
        foreach ($countryNameByCode as $code => $name) {
            $list[$code] = [
                'name'    => $name,
                'code'    => $code,
                'methods' => [],
            ];
        }
//        $this->addCintPayoutMethods($list);
//        $this->addOwnPayoutMethods($list);
        $this->addPayoutMethods($list);

        return $list;
    }

    private function addPayoutMethods(array &$list)
    {
        $payoutMethods = config('payout.options_configs');

        foreach ($payoutMethods as $countryCode => $methods) {
            if ( ! isset($list[$countryCode])) {
                continue;
            }

            foreach ($methods as $method => $methodConfig) {
                $list[$countryCode]['methods'][$method] = $this->addPayoutMethodParams(
                    $method,
                    $methodConfig['minimal_threshold'],
                    $methodConfig['provider'],
                    $methodConfig['active'],
                    $methodConfig['maximum_amount']
                );
            }
        }
    }

    private function addOwnPayoutMethods(array &$list)
    {
        $defaultConfigs = config('payout.defaults');
        $ownPayoutMethods = config('payout.methods');

        foreach ($ownPayoutMethods as $method => $methodConfigs) {
            foreach ($methodConfigs as $countryCode => $methodConfig) {
                if ( ! isset($list[$countryCode])) {
                    continue;
                }

                $list[$countryCode]['methods'][$method] = $this->addPayoutMethodParams(
                    $method,
                    $methodConfig['minimal_threshold'] ?? $defaultConfigs['minimal_threshold'] ?? 0,
                    $methodConfig['provider'],
                    $methodConfig['active'],
                    $methodConfig['maximum_amount'] ?? $defaultConfigs['maximum_amount'] ?? 0
                );
            }
        }
    }

    private function addCintPayoutMethods(array &$list)
    {
        $cintPayoutMethods = config('cint.panels');

        foreach ($cintPayoutMethods as $countryConfigs) {
            if ( ! isset($list[$countryConfigs['country']['iso_alpha_2']])) {
                continue;
            }

            if ( ! isset($countryConfigs['payment_methods'])) {
                continue;
            }

            foreach ($countryConfigs['payment_methods'] as $payoutMethodConfigs) {
                if ( ! $payoutMethodConfigs['active']) {
                    continue;
                }

                $method = 'DIGITAL_WALLET';

                $list[$countryConfigs['country']['iso_alpha_2']]['methods'][$method] = $this->addPayoutMethodParams(
                    'DIGITAL_WALLET',
                    $payoutMethodConfigs['threshold_money'],
                    $payoutMethodConfigs['name'],
                    $payoutMethodConfigs['active']
                );
            }
        }
    }

    /**
     * @param string     $method
     * @param float      $minimalThreshold
     * @param string     $provider
     * @param bool       $isActive
     * @param float|null $maximumAmount
     * @return array
     */
    private function addPayoutMethodParams(string $method, float $minimalThreshold, string $provider, bool $isActive, float $maximumAmount = null): array
    {
        return [
            'method'            => $method,
            'provider'          => $provider,
            'is_active'         => $isActive,
            'minimal_threshold' => $minimalThreshold,
            'maximum_amount'    => $maximumAmount,
        ];
    }
}
