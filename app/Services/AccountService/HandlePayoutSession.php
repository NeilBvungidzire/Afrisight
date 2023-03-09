<?php

namespace App\Services\AccountService;

trait HandlePayoutSession {

    static public function setPayoutSession(string $method, string $provider): void
    {
        session()->put('payout', [
            'method'   => $method,
            'provider' => $provider,
            'step'     => 'start',
        ]);
    }

    static public function setPayoutStep(string $step): void
    {
        session()->put('payout.step', $step);
    }

    static public function getPayoutSession(): ?array
    {
        return session()->get('payout');
    }
}
