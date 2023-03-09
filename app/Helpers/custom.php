<?php

use App\Services\WhatsAppService\WhatsAppService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

if ( ! function_exists('alert')) {
    /**
     * @return string
     */
    function alert()
    {
        $html = '';
        $alerts = (array)session('alert');

        foreach ($alerts as $alert) {
            try {
                $html .= view('alerts.default', $alert)->render();
            } catch (\Throwable $exception) {
                Log::error('View "' . 'alerts.default' . '" not found.');
            }
        }

        return $html;
    }
}

if ( ! function_exists('cn')) {
    /**
     * Conditional return classnames.
     *
     * @param array $list
     *
     * @return string
     */
    function cn(array $list)
    {

        $renderList = [];
        foreach ($list as $key => $value) {
            if (is_int($key)) {
                $renderList[] = $value;
            } elseif ($value) {
                $renderList[] = $key;
            }
        }

        return implode(' ', $renderList);
    }
}

if ( ! function_exists('htmlAttributes')) {
    /**
     * Conditional return HTML attributes.
     *
     * @param array $list
     *
     * @return string
     */
    function htmlAttributes(array $list)
    {

        $renderList = [];
        foreach ($list as $key => $value) {
            if (is_string($value)) {
                $renderList[] = sprintf($key . '="%s"', $value);
            } elseif (is_bool($value) && $value) {
                $renderList[] = $key;
            }
        }

        return implode(' ', $renderList);
    }
}

if ( ! function_exists('authUser')) {
    /**
     * @return User|null
     */
    function authUser(): ?User
    {
        $user = Auth::user();

        if ($user) {
            /** @var User $user */
            return $user;
        }

        return null;
    }
}

if ( ! function_exists('generateWhatsAppLink')) {
    /**
     * @param string|null $message
     * @return string
     */
    function generateWhatsAppLink(string $message = null): string
    {
        return WhatsAppService::getUniversalLink($message);
    }
}

if ( ! function_exists('removeSpecialCharacters')) {
    /**
     * @param string $string
     * @return string|null
     */
    function removeSpecialCharacters(string $string): ?string
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        $string = (string)preg_replace('/[^A-Za-z0-9\_]/', '', $string); // Removes special chars.

        return preg_replace('/_+/', ' ', $string); // Replaces multiple hyphens with single one.
    }
}

